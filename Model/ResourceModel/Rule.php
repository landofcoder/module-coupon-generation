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
class Rule extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
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
     * @param string $connectionName
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
        $this->_init('lof_couponcode_rule', 'coupon_rule_id');
    } 
        /**
     * Retrieve default attribute set id
     *
     * @return int
     */

    public function getEntityType(){
         if (empty($this->_type)) {
            $this->setType(\Magento\Catalog\Model\Product::ENTITY);
        }
    }

    protected function _afterSave(\Magento\Framework\Model\AbstractModel $object)
    {
        if($attribute = $object->getData('customer_group_ids')){
            $table = $this->getTable('salesrule_customer_group');
            $where = ['row_id = ?' => (int)$object->getRuleId()];
            $this->getConnection()->delete($table, $where); 
            $data = []; 
            foreach ($attribute as $k => $_attribute) {
                $data[] = [
                'customer_group_id' => $_attribute,
                'row_id' => (int)$object->getRuleId()
                ];
            }
            $this->getConnection()->insertMultiple($table, $data);
        }
        if($stores = $object->getData('website_ids')){
            $table = $this->getTable('salesrule_website');
            $where = ['row_id = ?' => (int)$object->getRuleId()];
            $this->getConnection()->delete($table, $where);
            if ($stores) {
                $data = [];
                foreach ($stores as $storeId) {
                    $data[] = ['row_id' => (int)$object->getRuleId(), 'website_id' => (int)$storeId];
                }
                try{
                    $this->getConnection()->insertMultiple($table, $data);
                }catch(\Exception $e){
                    die($e->getMessage());
                }
            }
        }

        return parent::_afterSave($object);


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
            $salesrule = $this->lookupSalesRule($object->getId());
            $object->setData($salesrule);

            //Set Customer Group Ids Data
            $customer_groups = $this->lookupCustomerGroupIds($object->getRuleId());
            $object->setData('customer_group_ids', $customer_groups);
            $object->setData('customer_groups', $customer_groups);

            //Set Website Ids Data
            $stores = $this->lookupStoreIds($object->getRuleId());
            $object->setData('website_ids', $stores);
            $object->setData('website_id', $stores);

        }
        return parent::_afterLoad($object);

    }
       /**
     * Get store ids to which specified item is assigned
     *
     * @param int $id
     * @return mixed
     */
       public function lookupStoreIds($id)
       {
        $connection = $this->getConnection();

        $select = $connection->select()->from(
            $this->getTable('salesrule_website'),
            'website_id'
            )->where(
            'rule_id = :rule_id'
            );
            $binds = [':rule_id' => (int)$id];
            //die(print_r($connection->fetchCol($select, $binds)->getData());
            return $connection->fetchCol($select, $binds);
        }

        /**
     * Get customer group ids to which specified item is assigned
     *
     * @param int $id
     * @return mixed
     */
        public function lookupCustomerGroupIds($id)
        {
            $connection = $this->getConnection();
            $select = $connection->select()->from($this->getTable('salesrule_customer_group'),'customer_group_id')->where(
                'row_id = :rule_id'
                );
            $binds = [':rule_id' => (int)$id];
            return $connection->fetchCol($select, $binds);
        }
        public function lookupSalesRule($id)
        {
            $connection = $this->getConnection();
            $select = $connection->select()->from(['lofrule' => $this->getTable('lof_couponcode_rule')])->join(array('salesrule' => $this->getTable('salesrule')), 'lofrule.rule_id = salesrule.row_id')->where(
                'coupon_rule_id = :coupon_rule_id'
                )->limit(1);
            $binds = [':coupon_rule_id' => (int)$id];
            return $connection->fetchRow($select, $binds);
        }
// ------------------------------------
        public function lookupRuleByid($id)
        {
            $connection = $this->getConnection();
            $select = $connection->select()->from(['lofrule' =>$this->getTable('lof_couponcode_rule')])->join(array('salesrule' => $this->getTable('salesrule')), 'lofrule.rule_id = salesrule.row_id')->where(
                'lofrule.rule_id = :rule_id'
                )->limit(1);
            $binds = [':rule_id' => (int)$id];
            return $connection->fetchRow($select, $binds);
        }
// --------------------------. GET all rules
        public function getRuleData()
        {
            $connection = $this->getConnection();
            $select = $connection->select()->from(['lofrule' =>$this->getTable('lof_couponcode_rule')])->join(array('salesrule' => $this->getTable('salesrule')), 'lofrule.rule_id = salesrule.row_id');
            return $connection->fetchAll($select);
        }
    }
