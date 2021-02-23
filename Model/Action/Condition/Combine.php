<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Catalog Rule Combine Condition data model
 */
namespace Lof\CouponCode\Model\Action\Condition;

// use Lof\RewardPoints\Model\Earning\Rule;

class Combine extends \Magento\Rule\Model\Condition\Combine
{
    /**
     * Core event manager proxy
     *
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_eventManager = null;

    /**
     * @var \Magento\SalesRule\Model\Rule\Condition\Address
     */
    protected $_conditionAddress;

    /**
     * @var \Magento\CatalogRule\Model\Rule\Condition\ProductFactory
     */
    protected $_productFactory;

    /**
     * @param \Magento\Rule\Model\Condition\Context $context
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\SalesRule\Model\Rule\Condition\Address $conditionAddress
     * @param array $data
     */
    public function __construct(
        \Magento\Rule\Model\Condition\Context $context,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\CatalogRule\Model\Rule\Condition\ProductFactory $conditionFactory,
        \Magento\SalesRule\Model\Rule\Condition\Address $conditionAddress,
        \Magento\SalesRule\Model\Rule\Condition\Product $ruleConditionProduct,
        \Lof\CouponCode\Model\Action\Condition\CustomerFactory $customerFactory,
        \Lof\CouponCode\Model\Action\Condition\BehaviorFactory $behaviorFactory,
        array $data = []
    ) {
        $this->_eventManager      = $eventManager;
        $this->_conditionAddress  = $conditionAddress;
        $this->_productFactory    = $conditionFactory;
        $this->_ruleConditionProd = $ruleConditionProduct;
        $this->_customerFactory   = $customerFactory;
        $this->_behaviorFactory   = $behaviorFactory;
        parent::__construct($context, $data);
        $this->setType('Lof\CouponCode\Model\Action\Condition\Combine');
    }

    /**
     * @return mixed
     */
    public function getNewChildSelectOptions()
    {
    
        $conditions = $this->_getCustomerBehaviorConditions();
        return $conditions;
    }

    protected function _getCustomerBehaviorConditions()
    {
        $attributes = [];
        $customerAttributes = $this->_customerFactory->create()->loadAttributeOptions()->getAttributeOption();
        foreach ($customerAttributes as $code => $label) {
            $attributes[] = [
                'value' => 'Lof\CouponCode\Model\Action\Condition\Customer|' . $code,
                'label' => $label,
            ];
        }

        $attributes2 = [];
        $customerRe = $this->_behaviorFactory->create()->loadAttributeOptions()->getAttributeOption();
        foreach ($customerRe as $code => $label) {
            $attributes2[] = [
                'value' => 'Lof\CouponCode\Model\Action\Condition\Behavior|' . $code,
                'label' => $label,
            ];
        }

        $conditions = parent::getNewChildSelectOptions();
        $conditions = array_merge_recursive(
            $conditions,
            [
                [
                    'label' => __('Conditions Combination'),
                    'value' => 'Lof\CouponCode\Model\Action\Condition\Combine|' . Rule::BEHAVIOR
                ],
                [
                    'label' => __('Customer Behavior'),
                    'value' => $attributes2
                ],
                [
                    'label' => __('Customer Attribute'),
                    'value' => $attributes
                ]
            ]
        );
        return $conditions;
    }

    /**
     * @param array $productCollection
     * @return $this
     */
    public function collectValidatedAttributes($productCollection)
    {
        foreach ($this->getConditions() as $condition) {
            /** @var Product|Combine $condition */
            $condition->collectValidatedAttributes($productCollection);
        }
        return $this;
    }

}