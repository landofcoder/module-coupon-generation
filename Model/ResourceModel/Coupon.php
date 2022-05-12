<?php
/**
 * Landofcoder
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Landofcoder.com license that is
 * available through the world-wide-web at this URL:
 * http://landofcoder.com/license
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category   Landofcoder
 * @package    Lof_FollowUpEmail
 * @copyright  Copyright (c) 2016 Landofcoder (http://www.landofcoder.com/)
 * @license    http://www.landofcoder.com/LICENSE-1.0.html
 */
namespace Lof\CouponCode\Model\ResourceModel;

/**
 * CMS block model
 */
class Coupon extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Construct
     *
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param string|null $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->_storeManager = $storeManager;
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('lof_couponcode_coupon', 'couponcode_id');
    }
        /**
     * Retrieve default attribute set id
     *
     * @return int
     */

    public function getEntityType()
    {
        if (empty($this->_type)) {
            $this->setType(\Magento\Catalog\Model\Product::ENTITY);
        }
    }

    /**
     * Perform operations after object load
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _afterLoad(\Magento\Framework\Model\AbstractModel $object)
    {
        if ($object->getRuleId()) {
            //Set SalesRule Data
            $salesrule = $this->lookupSalesRule($object->getRuleId());
            $salecoupon = $this->lookupSalesCoupon($object->getCouponId());
            if($salesrule) {
                foreach($salesrule as $key => $val) {
                    $object->setData($key, $val);
                }
            }
            if($salecoupon) {
                foreach($salecoupon as $key => $val) {
                    if($key == "expiration_date") {
                        $to_date = $object->getData("to_date");
                        if($to_date){
                            $val = $to_date;
                        }
                    }
                    if($key == "usage_per_customer") {
                        $uses_per_customer = $object->getData("uses_per_customer");
                        if($uses_per_customer){
                            $val = $uses_per_customer;
                        }
                    }
                    if($key == "usage_limit") {
                        $uses_per_coupon = $object->getData("uses_per_coupon");
                        if($uses_per_coupon){
                            $val = $uses_per_coupon;
                        }
                    }
                    if($key == "usage_per_coupon") {
                        $uses_per_coupon = $object->getData("uses_per_coupon");
                        if($uses_per_coupon){
                            $val = $uses_per_coupon;
                        }
                    }
                    $object->setData($key, $val);
                }
            }
        }
        return parent::_afterLoad($object);
    }

    /**
     * lookup rule by sale rule id
     *
     * @param int $id
     * @return mixed
     */
    public function lookupSalesRule($id)
    {
        $connection = $this->getConnection();
        $select = $connection->select()->from($this->getTable('salesrule'))->where(
            'rule_id = :rule_id'
            )->limit(1);
        $binds = [':rule_id' => (int)$id];
        return $connection->fetchRow($select, $binds);
    }

    /**
     * lookup rule by sale rule id
     *
     * @param int $id
     * @return mixed
     */
    public function lookupSalesCoupon($coupon_id)
    {
        $connection = $this->getConnection();
        $select = $connection->select()->from($this->getTable('salesrule_coupon'))->where(
            'coupon_id = :coupon_id'
            )->limit(1);
        $binds = [':coupon_id' => (int)$coupon_id];
        return $connection->fetchRow($select, $binds);
    }

}
