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
 * @package    Lof_RewardPoints
 * @copyright  Copyright (c) 2016 Landofcoder (http://www.landofcoder.com/)
 * @license    http://www.landofcoder.com/LICENSE-1.0.html
 */

namespace Lof\CouponCode\Model\ResourceModel\Coupon;

use Lof\CouponCode\Model\ResourceModel\AbstractCollection;

class Collection extends AbstractCollection
{
    protected $_idFieldName = 'couponcode_id';

	protected function _afterLoad()
    {
        $this->getCustomerAfterLoad();
        $this->getRuleAfterLoad();
        return parent::_afterLoad();
    }

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Lof\CouponCode\Model\Coupon', 'Lof\CouponCode\Model\ResourceModel\Coupon');
    }

    protected function getCustomerAfterLoad()
    {
    	$items = $this->getColumnValues("couponcode_id");
         if (count($items)) {
            $connection = $this->getConnection();
             foreach ($this as $item) {
                 $customerId = $item->getData('customer_id');
                 if(empty($customerId)){
                     $item->setData('customer_name',$item->getEmail());
                     $item->setData('customer_id','');
                 }else{
                     $select = $connection->select()->from(['customer' => $this->getTable('customer_grid_flat')])->where('customer.entity_id = (?)', $customerId);
                     $result = $connection->fetchRow($select);
                     $item->setData('customer_name',$result['name']);
                 }
             }
          }
    }

    protected function getRuleAfterLoad()
    {
    	$items = $this->getColumnValues("couponcode_id");
    	if(count($items)){
    		$connection = $this->getConnection();
    		foreach ($this as $item) {
    			$ruleId = $item->getData('rule_id');
          $couponId = $item->getData('coupon_id');
    			$select = $connection->select()
                                    ->from(
                                        ['salesrule' => $this->getTable('salesrule')]
                                        )
                                    ->join(
                                        array('lofrule' => $this->getTable('lof_couponcode_rule')),
                                            'salesrule.rule_id = lofrule.rule_id',
                                                "coupon_rule_id"
                                        )
                                    ->where(
                                        'salesrule.rule_id = (?)',$ruleId
                                        );
    			$result = $connection->fetchRow($select);

          $select_coupon = $connection->select()
                              ->from(
                                  ['salesrule_coupon' => $this->getTable('salesrule_coupon')]
                                  )
                              ->where(
                                  'salesrule_coupon.coupon_id = (?)',$couponId
                                  );
          $result_coupon = $connection->fetchRow($select_coupon);

    			$item->setData('name', $result['name']);
          $item->setData('uses_per_coupon', $result['uses_per_coupon']);
          $item->setData('uses_per_customer', $result['uses_per_customer']);
          $item->setData('discount_amount',$result['discount_amount']);
          $item->setData('simple_action',$result['simple_action']);
          $item->setData('coupon_rule_id',$result['coupon_rule_id']);
    			$item->setData('is_active',$result['is_active']);
          $item->setData('from_date',$result['from_date']);
          $item->setData('to_date',$result['to_date']);
          $item->setData('created_at',$result_coupon['created_at']);
          $item->setData('times_used',$result_coupon['times_used']);
    		}
    	}
    }

    public function addFilterByEmailRule($email, $ruleId)
    {
        if($email && $ruleId) {
            $email = trim($email);
            $this->addFieldToFilter("email", $email);
            $this->addFieldToFilter("rule_id", (int)$ruleId);
        }
        return $this;
    }

    public function getTotalByEmail($email, $ruleId)
    {
        $this->addFilterByEmailRule($email, $ruleId);
        return $this->count();
    }

    public function getByCouponCode($coupon_code)
    {
      $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $SellerPost = $objectManager->get('Lof\CouponCode\Model\Coupon')->load($coupon_code, 'code');
        if(!$SellerPost->getData()){
          $SellerPost = $objectManager->get('Magento\SalesRule\Model\Coupon')->load($coupon_code, 'code');
        }
        return  $SellerPost->getData();
    }

    public function getRule($rule_id)
    {
      $connection = $this->getConnection();
      $select = $connection->select()
                                    ->from(
                                        ['salesrule' => $this->getTable('salesrule')]
                                        )
                                    ->join(
                                        array('lofrule' => $this->getTable('lof_couponcode_rule')),
                                            'salesrule.rule_id = lofrule.rule_id',
                                                "is_check_email"
                                        )
                                    ->where(
                                        'salesrule.rule_id = (?)',$rule_id
                                        );
      $result = $connection->fetchRow($select);
      return $result;
    }

    public function getCouponCodeByConditions($param)
    {
      $connection = $this->_resource->getConnection();
      $select = 'SELECT *  FROM ' . $this->getTable('salesrule_coupon') . ' INNER JOIN '. $this->getTable('lof_couponcode_coupon').' ON salesrule_coupon.coupon_id = lof_couponcode_coupon.couponcode_id';
      $where = '';
      $numItems = count($param);
        $i = 0;
        foreach ($param as $key => $value) {
          if(++$i === $numItems){
            $where .=' lof_couponcode_coupon.'. $key . ' = ' . '"' . $value . '"';
          } else{
            $where .=' lof_couponcode_coupon.'. $key . ' = ' . '"' . $value . '" and ';
          }
        }
      if($where != ''){
        $where = ' WHERE '. $where;
        $select = $select . $where;
      }
      try {
        $data = $connection->fetchAll($select);
        return $data;
      } catch (\Exception $e) {
        return __('Could not found coupon code');
      }
    }

}
