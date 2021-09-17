<?php
/**
 * Landofcoder
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Landofcoder.com license that is
 * available through the world-wide-web at this URL:
 * https://landofcoder.com/license
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category   Landofcoder
 * @package    Lof_CouponCode
 * @copyright  Copyright (c) 2021 Landofcoder (https://landofcoder.com/)
 * @license    https://landofcoder.com/LICENSE-1.0.html
 */
namespace Lof\CouponCode\Plugin;

use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class CouponManagementPlugin
 * @package Lof\CouponCode\Plugin
 */
class CouponManagementPlugin
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
     * @var \Magento\Checkout\Model\SessionFactory
     */
    protected $sessionFactory;

    /**
     * @var \Lof\CouponCode\Model\CouponFactory
     */
    protected $couponFactory;

    /**
     * @var \Lof\CouponCode\Helper\Data $helperData
     */
    protected $helperData;

    /**
     * @var bool
     */
    protected $flag = false;

    /**
     * Constructs a coupon read service object.
     *
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param \Lof\CouponCode\Model\CouponFactory $couponFactory
     * @param \Magento\Checkout\Model\SessionFactory $sessionFactory
     * @param \Lof\CouponCode\Helper\Data $helperData
     */
    public function __construct(
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Lof\CouponCode\Model\CouponFactory $couponFactory,
        \Magento\Checkout\Model\SessionFactory $sessionFactory,
        \Lof\CouponCode\Helper\Data $helperData
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->couponFactory = $couponFactory;
        $this->sessionFactory = $sessionFactory;
        $this->helperData = $helperData;
    }

    /**
     * @param \Magento\Quote\Api\CouponManagementInterface $subject
     * @param \Closure $proceed
     * @param string|int $cartId
     * @param string $couponCode
     */
    public function aroundSet(
        \Magento\Quote\Api\CouponManagementInterface $subject,
        \Closure $proceed,
        $cartId,
        $couponCode
    ) {
        if ($this->helperData->isEnabled()) {
            //get info by couponcode
            $couponCollection = $this->couponFactory->create()->getCollection();
            $data = $couponCollection->getByCouponCode($couponCode);
            if ($data && count($data) > 0 && isset($data["rule_id"])) {
                $rule = $couponCollection->getRule((int)$data["rule_id"]);
                //get checkout info
                $customer_checkout = $this->sessionFactory->create()->getQuote()->getCustomer();
                $customer_email = $customer_checkout->getEmail();
                $customer_id = $customer_checkout->getId();
                if (isset($rule['is_check_email']) && $rule["is_check_email"]) {
                    if ((isset($data["email"]) && $data["email"] == $customer_email) || (isset($data["customer_id"]) && $data["customer_id"] == $customer_id))
                        $this->flag = true;
                } else {
                    $this->flag = true;
                }
            }
            if ($this->flag) {
                return $proceed($cartId, $couponCode);
            } else {
                throw new NoSuchEntityException(__('Coupon code is not valid!'));
            }
        } else {
            return $proceed($cartId, $couponCode);
        }
    }
}
