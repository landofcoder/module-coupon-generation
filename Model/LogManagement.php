<?php
/**
 * Lof CouponCode is a powerful tool for managing the processing return and exchange requests within your workflow. This, in turn, allows your customers to request and manage returns and exchanges directly from your webstore. The Extension compatible with magento 2.x
 * Copyright (C) 2017  Landofcoder.com
 * 
 * This file is part of Lof/CouponCode.
 * 
 * Lof/CouponCode is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

namespace Lof\CouponCode\Model;

class LogManagement
{
	 protected $_registry;
	 protected $_logModel;
	 protected $_couponHelper;
	 protected $orderRepository;
	 protected $_priceCurrency;

	 public function __construct(
        \Magento\Framework\Registry $registry,
        \Lof\CouponCode\Model\Log $logModel,
        \Lof\CouponCode\Helper\Data $helperData,
        \Magento\Sales\Api\Data\OrderInterface $orderRepository,
        \Magento\Store\Model\StoreManagerInterface $priceCurrency
        ) {
        $this->_registry = $registry;
        $this->_logModel = $logModel;
        $this->_couponHelper = $helperData;
        $this->orderRepository = $orderRepository;
        $this->_priceCurrency = $priceCurrency;
    }
    /**
     * {@inheritdoc}
     */
    public function getLog($coupon_code,$email)
    {
    	if(!$this->_couponHelper->getConfig('general_settings/show') || !$this->_couponHelper->getConfig('general_settings/allow_track_log')) {
            return [];
        }

        $return = [];
        $coupon_code = trim($coupon_code);
		$customer_email = trim($email);
		if($coupon_code && $customer_email) {
			$collection = $this->_logModel->getCollection();
			$collection = $collection->addFieldToFilter("coupon_code", $coupon_code)
									->addFieldToFilter("email_address", $customer_email);

			if(0 < $collection->getSize()){
				$log = $collection->getFirstItem();
				$return = [
					"order_id" => $log->getOrderId(),
					"coupon_code" => $log->getCouponCode(),
					"full_name" => $log->getFullName(),
					"email_address" => $log->getEmailAddress(),
					"discount_amount" => $log->getDiscountAmount(),
				];

				if($log && $log->getOrderId()) {
		            $sales_order_info = $this->orderRepository->loadByIncrementId($log->getOrderId());
		            $currencyCode = $this->_priceCurrency->getStore()->getCurrentCurrencyCode();
		            $return["order_status"] = $sales_order_info->getStatusLabel();
		            $return["order_total"] = $sales_order_info->getSubtotal().$currencyCode;
		        }

			}
		}
        return json_encode($return);
    }
}
