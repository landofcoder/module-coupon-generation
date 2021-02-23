<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Lof\CouponCode\Model\Rewrite\Newsletter;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\MailException;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\App\ObjectManager;
/**
 * Subscriber model
 *
 * @method \Magento\Newsletter\Model\ResourceModel\Subscriber _getResource()
 * @method \Magento\Newsletter\Model\ResourceModel\Subscriber getResource()
 * @method int getStoreId()
 * @method $this setStoreId(int $value)
 * @method string getChangeStatusAt()
 * @method $this setChangeStatusAt(string $value)
 * @method int getCustomerId()
 * @method $this setCustomerId(int $value)
 * @method string getSubscriberEmail()
 * @method $this setSubscriberEmail(string $value)
 * @method int getSubscriberStatus()
 * @method $this setSubscriberStatus(int $value)
 * @method string getSubscriberConfirmCode()
 * @method $this setSubscriberConfirmCode(string $value)
 * @method int getSubscriberId()
 * @method Subscriber setSubscriberId(int $value)
 * @method $this initCouponGenerator()
 * @method string getCouponCode(string $alias)
 * @method string getCouponExpirationDate(string $alias)
 * @method string getCouponDiscount(string $alias)
 * @method string getUsesPerCoupon(string $alias)
 * @method string generateCoupon(int ruleId, string $alias)
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 */
class Subscriber extends \Magento\Newsletter\Model\Subscriber
{
    protected $addedFlag = false;
    protected $_generatorCoupon;
    public function initCouponGenerator() {
        if(!$this->addedFlag) {
            $this->_generatorCoupon = $this->getData("generatorCoupon");
            if(!$this->_generatorCoupon && isset($this->_data['generatorCoupon'])) {
                $this->_generatorCoupon = $this->_data['generatorCoupon'];
            }
            $this->_generatorCoupon->initRequireModels();
            $customer_email = $this->getSubscriberEmail();
            $customer_name = $this->getSubscriberFullName();

            if($customer_email) {
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $customerFactory = $objectManager->get('\Magento\Customer\Model\CustomerFactory');
                $customer = $customerFactory->create();
                $customer->loadByEmail($customer_email);// load customer by email address
                if($customer_id = $customer->getId()) {
                    $this->_generatorCoupon->setCustomerId($customer_id);
                }
                $this->_generatorCoupon->setCustomerEmail($customer_email);
                $this->_generatorCoupon->setCustomerName($customer_name);
                $this->addedFlag = true;
            }
        }
        return $this;
    }
    public function getCouponCode($coupon_alias){
        $this->initCouponGenerator();
        if($this->addedFlag && $coupon_alias){
            return $this->_generatorCoupon->getCouponCode($coupon_alias);
        }
        return "";
    }
    public function getCouponExpirationDate($coupon_alias){
        $this->initCouponGenerator();
        if($this->addedFlag && $coupon_alias){
            return $this->_generatorCoupon->getCouponExpirationDate($coupon_alias);
        }
        return "";
    }
    public function getCouponDiscount($coupon_alias) {
        $this->initCouponGenerator();
        if($this->addedFlag && $coupon_alias){
            return $this->_generatorCoupon->getCouponDiscount($coupon_alias);
        }
        return "";
    }
    public function getUsesPerCoupon($coupon_alias) {
        $this->initCouponGenerator();
        if($this->addedFlag && $coupon_alias){
            return $this->_generatorCoupon->getUsesPerCoupon($coupon_alias);
        }
        return "";
    }
    public function generateCoupon($ruleId, $coupon_alias = "") {
        $this->initCouponGenerator();
        if($this->addedFlag && $ruleId){
            return $this->_generatorCoupon->generateCoupon($ruleId, $coupon_alias);
        }
        return "";
    }
}
