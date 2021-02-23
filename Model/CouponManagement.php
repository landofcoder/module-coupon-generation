<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Lof\CouponCode\Model;

use Magento\Quote\Api\CouponManagementInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Coupon management object.
 */
class CouponManagement implements CouponManagementInterface
{
    /**
     * Quote repository.
     *
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteRepository;
    /**
     * Quote repository.
     *
     * @var \Magento\Framework\ObjectManagerInterface $objectManager
     */
    protected $_objectManager;

    /**
     * Constructs a coupon read service object.
     *
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository Quote repository.
     */
    public function __construct(
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->_objectManager = $objectManager;
    }

    /**
     * {@inheritdoc}
     */
    public function get($cartId)
    {
        /** @var  \Magento\Quote\Model\Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);
        return $quote->getCouponCode();
    }

    /**
     * {@inheritdoc}
     */
    public function set($cartId, $couponCode)
    {
        $flag = false;
        // check login to apply coupon code
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $currentCustomer = $objectManager->get('Magento\Customer\Model\Session');

        //get infor by couponcode
        $coupon_collection = $objectManager->create('Lof\CouponCode\Model\Coupon')->getCollection();
        $data = $coupon_collection->getByCouponCode($couponCode);
        if(count($data) > 0){
            $rule = $coupon_collection->getRule($data["rule_id"]);
            //get checkout info
            $customer_checkout = $objectManager->get('\Magento\Checkout\Model\Session')->getQuote()->getCustomer();
            $customer_email = $customer_checkout->getEmail();
            $customer_id = $customer_checkout->getId();
            if($rule["is_check_email"]){
                if((isset($data["email"]) && $data["email"] == $customer_email) || (isset($data["customer_id"]) && $data["customer_id"] == $customer_id))
                    $flag = true;
            } else{
                $flag = true;
            }
        }
        if($flag){
            /** @var  \Magento\Quote\Model\Quote $quote */
            $quote = $this->quoteRepository->getActive($cartId);
            if (!$quote->getItemsCount()) {
                throw new NoSuchEntityException(__('Cart %1 doesn\'t contain products', $cartId));
            }
            $quote->getShippingAddress()->setCollectShippingRates(true);

            try {
                $quote->setCouponCode($couponCode);
                $this->quoteRepository->save($quote->collectTotals());
            } catch (\Exception $e) {
                throw new CouldNotSaveException(__('Could not apply coupon code'));
            }
            if ($quote->getCouponCode() != $couponCode) {
                throw new NoSuchEntityException(__('Coupon code is not valid'));
            }
        }else {
            throw new NoSuchEntityException(__('Coupon code is not valid'));
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function remove($cartId)
    {
        /** @var  \Magento\Quote\Model\Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);
        if (!$quote->getItemsCount()) {
            throw new NoSuchEntityException(__('Cart %1 doesn\'t contain products', $cartId));
        }
        $quote->getShippingAddress()->setCollectShippingRates(true);
        try {
            $quote->setCouponCode('');
            $this->quoteRepository->save($quote->collectTotals());
        } catch (\Exception $e) {
            throw new CouldNotDeleteException(__('Could not delete coupon code'));
        }
        if ($quote->getCouponCode() != '') {
            throw new CouldNotDeleteException(__('Could not delete coupon code'));
        }
        return true;
    }
    public function getCouponAlias($alias){

        $coupon_model = $this->_objectManager->create('Lof\CouponCode\Model\Coupon')->getCouponByAlias($alias);
        $data = $coupon_model->getOrigData();
        $data["conditions_serialized"] = isset($data["conditions_serialized"])? json_decode($data["conditions_serialized"]) : (object)[];
        $data["actions_serialized"] = isset($data["actions_serialized"])? json_decode($data["actions_serialized"]) : (object)[];
        return json_encode($data,true);
    }

    public function getCouponByConditions(){
        $requestHttp = $this->_objectManager->create('\Magento\Framework\App\Request\Http');
        $conditions = $requestHttp->getParams();
        $coupon_model = $this->_objectManager->create('Lof\CouponCode\Model\Coupon');
        $data = $coupon_model->getResourceCollection()->getCouponCodeByConditions($conditions);
        return json_encode($data,true);
    }
}
