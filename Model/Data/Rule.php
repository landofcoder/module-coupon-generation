<?php
/**
 * Data Model implementing the Address interface
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Lof\CouponCode\Model\Data;

// use Lof\CouponCode\Api\Data\ConditionInterface;
use Lof\CouponCode\Api\Data\RuleInterface;

/**
 * Class Rule
 *
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @codeCoverageIgnore
 */
class Rule extends \Magento\Framework\Api\AbstractExtensibleObject implements
    \Lof\CouponCode\Api\Data\RuleInterface
{
    const KEY_RULE_ID = 'rule_id';
    const KEY_COUPON_RULE_ID = 'coupon_rule_id';
    const KEY_SIMPLE_ACTION = 'simple_action';
    const KEY_DISCOUNT_AMOUNT = 'discount_amount';
    const KEY_CONDITIONS = 'conditions';
    const KEY_ACTIONS = 'actions';
    const KEY_COUPONS_GENERATED = 'coupons_generated';
    const KEY_RULE_NAME = 'rule_name';
    const KEY_CODE_LENGTH = 'code_length';

    /**
     * Return rule id
     *
     * @return int|null
     */
    public function getRuleId()
    {
        return $this->_get(self::KEY_RULE_ID);
    }

    public function setRuleId($ruleId)
    {
        return $this->setData(self::KEY_RULE_ID, $ruleId);
    }

    public function getCouponRuleId()
    {
        return $this->_get(self::KEY_COUPON_RULE_ID);
    }

    public function setCouponRuleId($coupon_rule_id)
    {
        return $this->setData(self::KEY_COUPON_RULE_ID, $coupon_rule_id);
    }

    public function getSimpleAction()
    {
        return $this->_get(self::KEY_SIMPLE_ACTION);
    }

    public function setSimpleAction($simple_action)
    {
        return $this->setData(self::KEY_SIMPLE_ACTION, $simple_action);
    }

    public function getDiscountAmount()
    {
        return $this->_get(self::KEY_DISCOUNT_AMOUNT);
    }

    public function setDiscountAmount($discount_amount)
    {
        return $this->setData(self::KEY_DISCOUNT_AMOUNT, $discount_amount);
    }

    public function getConditions()
    {
        return $this->_get(self::KEY_CONDITIONS);
    }

    public function setConditions($conditions)
    {
        return $this->setData(self::KEY_CONDITIONS, $conditions);
    }

    public function getActions()
    {
        return $this->_get(self::KEY_ACTIONS);
    }

    public function setActions($actions)
    {
        return $this->setData(self::KEY_ACTIONS, $actions);
    }

    public function getCouponsGenerated()
    {
        return $this->_get(self::KEY_COUPONS_GENERATED);
    }

    public function setCouponsGenerated($coupons_generated)
    {
        return $this->setData(self::KEY_COUPONS_GENERATED, $coupons_generated);
    }

    public function getRuleName()
    {
        return $this->_get(self::KEY_RULE_NAME);
    }

    public function setRuleName($rule_name)
    {
        return $this->setData(self::KEY_RULE_NAME, $rule_name);
    }

    public function getCodeLength()
    {
        return $this->_get(self::KEY_CODE_LENGTH);
    }

    public function setCodeLength($code_length)
    {
        return $this->setData(self::KEY_CODE_LENGTH, $code_length);
    }

}
