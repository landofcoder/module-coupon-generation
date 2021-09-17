<?php
/**
 * LandofCoder
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the venustheme.com license that is
 * available through the world-wide-web at this URL:
 * http://venustheme.com/license
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category   LandofCoder
 * @package    Lof_CouponCode
 * @copyright  Copyright (c) 2016 Landofcoder (http://www.landofcoder.com/)
 * @license    http://www.landofcoder.com/LICENSE-1.0.html
 */
namespace Lof\CouponCode\Block\Adminhtml\Rule\Edit\Tab;

class Actions extends \Magento\Backend\Block\Widget\Form\Generic implements
    \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * Core registry
     *
     * @var \Magento\Backend\Block\Widget\Form\Renderer\Fieldset
     */
    protected $_rendererFieldset;

    /**
     * @var \Magento\Rule\Block\Actions
     */
    protected $_ruleActions;

    /**
     * @var \Magento\Config\Model\Config\Source\Yesno
     */
    protected $_sourceYesno;

    protected $_actionModel;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Config\Model\Config\Source\Yesno $sourceYesno
     * @param \Magento\Rule\Block\Actions $ruleActions
     * @param \Magento\Backend\Block\Widget\Form\Renderer\Fieldset $rendererFieldset
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Config\Model\Config\Source\Yesno $sourceYesno,
        \Magento\Rule\Block\Actions $ruleActions,
        \Magento\Backend\Block\Widget\Form\Renderer\Fieldset $rendererFieldset,
        \Lof\CouponCode\Model\Action $actionModel,
        array $data = []
    ) {
        $this->_rendererFieldset = $rendererFieldset;
        $this->_ruleActions = $ruleActions;
        $this->_sourceYesno = $sourceYesno;
        $this->_actionModel = $actionModel;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('Actions');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('Actions');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Prepare form before rendering HTML
     *
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('lofcouponcode_rule');

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('rule_');
        
        $this->addOrginalField($form,$model);
        $this->addBehaviorConditions($form,$model);
        $this->_eventManager->dispatch('adminhtml_block_salesrule_actions_prepareform', ['form' => $form]);

        $form->setValues($model->getData());

        if ($model->isReadonly()) {
            foreach ($fieldset->getElements() as $element) {
                $element->setReadonly(true, true);
            }
        }

        $this->setForm($form);
        return parent::_prepareForm();
    }
    protected function addBehaviorConditions(\Magento\Framework\Data\Form $form, \Lof\CouponCode\Model\Rule $model) {  
       $renderer = $this->_rendererFieldset->setTemplate(
            'Magento_CatalogRule::promo/fieldset.phtml'
        )->setNewChildUrl(
            $this->getUrl('sales_rule/promo_quote/newActionHtml/form/rule_actions_fieldset/form_namespace/edit_form')
        );

        $fieldset = $form->addFieldset(
            'actions_fieldset',
            [
                'legend' => __(
                    'Apply the rule only to cart items matching the following conditions ' .
                    '(leave blank for all items).'
                )
            ]
        )->setRenderer(
            $renderer
        );

        $fieldset->addField(
            'actions',
            'text',
            ['name' => 'actions', 
            'label' => __('Apply To'), 
            'title' => __('Apply To'), 
            'required' => true
        ]
        )->setRule(
            $model
        )->setRenderer(
            $this->_ruleActions
        );
    }
    protected function addOrginalField(\Magento\Framework\Data\Form $form, \Lof\CouponCode\Model\Rule $model)
    {
        $fieldset = $form->addFieldset(
            'action_fieldset',
            ['legend' => __('Actions')]
        );

        $fieldset->addField(
            'simple_action',
            'select',
            [
                'label' => __('Apply'),
                'name' => 'simple_action',
                'options' => [
                    \Magento\SalesRule\Model\Rule::BY_PERCENT_ACTION => __('Percent of product price discount'),
                    \Magento\SalesRule\Model\Rule::BY_FIXED_ACTION => __('Fixed amount discount'),
                    \Magento\SalesRule\Model\Rule::CART_FIXED_ACTION => __('Fixed amount discount for whole cart'),
                    \Magento\SalesRule\Model\Rule::BUY_X_GET_Y_ACTION => __('Buy X get Y free (discount amount is Y)'),
                ]
            ]
        );
        $fieldset->addField(
            'discount_amount',
            'text',
            [
                'name' => 'discount_amount',
                'required' => true,
                'class' => 'validate-not-negative-number',
                'label' => __('Discount Amount')
            ]
        );
        $model->setDiscountAmount($model->getDiscountAmount() * 1);

        $fieldset->addField(
            'discount_qty',
            'text',
            ['name' => 'discount_qty', 
            'label' => __('Maximum Qty Discount is Applied To')
            ]
        );
        $model->setDiscountQty($model->getDiscountQty() * 1);

        $fieldset->addField(
            'discount_step',
            'text',
            ['name' => 'discount_step', 
            'label' => __('Discount Qty Step (Buy X)')
        ]
        );

        $fieldset->addField(
            'apply_to_shipping',
            'select',
            [
                'label' => __('Apply to Shipping Amount'),
                'title' => __('Apply to Shipping Amount'),
                'name' => 'apply_to_shipping',
                'values' => $this->_sourceYesno->toOptionArray()
            ]
        );
    }
}
