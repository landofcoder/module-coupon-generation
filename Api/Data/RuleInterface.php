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

namespace Lof\CouponCode\Api\Data;

interface RuleInterface  extends \Magento\Framework\Api\ExtensibleDataInterface
{
    const RULE_ID = 'rule_id';
    const COUPON_RULE_ID = 'coupon_rule_id';
    const SIMPLE_ACTION = 'simple_action';
    const DISCOUNT_AMOUNT = 'discount_amount';
    const CONDITIONS = 'conditions';
    const ACTIONS = 'actions';
    const COUPONS_GENERATED = 'coupons_generated';
    const RULE_NAME = 'rule_name';
    const CODE_LENGTH = 'code_length';

    /**
     * Get rule_id
     * @return string|null
     */
    public function getRuleId();

    /**
     * Set rule_id
     * @param string $rule_id
     * @return \Lof\CouponCode\Api\Data\RuleInterface
     */
    public function setRuleId($ruleId);

    /**
     * Get coupon_rule_id
     * @return string|null
     */
    public function getCouponRuleId();

    /**
     * Set coupon_rule_id
     * @param string $coupon_rule_id
     * @return \Lof\CouponCode\Api\Data\RuleInterface
     */
    public function setCouponRuleId($coupon_rule_id);

    /**
     * get simple_action
     * @param string $simple_action
     * @return string|null
     */
    public function getSimpleAction();

    /**
     * Set simple_action
     * @param string $simple_action
     * @return string|null
     */
    public function setSimpleAction($simple_action);

    /**
     * get discount_amount
     * @param string $discount_amount
     * @return string|null
     */
    public function getDiscountAmount();

    /**
     * Set discount_amount
     * @param string $discount_amount
     * @return string|null
     */
    public function setDiscountAmount($discount_amount);

    /**
     * get conditions
     * @param string $conditions
     * @return string|null
     */
    public function getConditions();

    /**
     * Set conditions
     * @param string $conditions
     * @return string|null
     */
    public function setConditions($conditions);

    /**
     * get actions
     * @param string $actions
     * @return string|null
     */
    public function getActions();

    /**
     * Set actions
     * @param string $actions
     * @return string|null
     */
    public function setActions($actions);

    /**
     * get coupons_generated
     * @param string $coupons_generated
     * @return string|null
     */
    public function getCouponsGenerated();

    /**
     * Set coupons_generated
     * @param string $coupons_generated
     * @return string|null
     */
    public function setCouponsGenerated($coupons_generated);

    /**
     * get rule_name
     * @param string $rule_name
     * @return string|null
     */
    public function getRuleName();

    /**
     * Set rule_name
     * @param string $rule_name
     * @return string|null
     */
    public function setRuleName($rule_name);

    /**
     * get rule_name
     * @param string $rule_name
     * @return string|null
     */
    public function getCodeLength();

    /**
     * Set code_length
     * @param string $code_length
     * @return string|null
     */
    public function setCodeLength($code_length);
}
