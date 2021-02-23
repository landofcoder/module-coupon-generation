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

namespace Lof\CouponCode\Block\Adminhtml\Generate\Generate;

/**
 * Adminhtml report filter form
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * Report type options
     *
     * @var array
     */
    protected $_reportTypeOptions = [];

    /**
     * Report field visibility
     *
     * @var array
     */
    protected $_fieldVisibility = [];

    /**
     * Report field opions
     *
     * @var array
     */
    protected $_fieldOptions = [];

    protected $_helper;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Lof\CouponCode\Helper\Data $helper,
        array $data = []
        ) {
        
        parent::__construct($context, $registry, $formFactory, $data);
        $this->_helper = $helper;
    }

    /**
     * Add fieldset with general report fields
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        // die($this->_helper->getAllRule());
        $actionUrl = $this->getUrl('*/generate/generate');

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            [
                'data' => [
                    'id' => 'filter_form',
                    'action' => $actionUrl,
                    'method' => 'post'
                ]
            ]
        );

        $htmlIdPrefix = 'lof_couponcode_generate_';
        $form->setHtmlIdPrefix($htmlIdPrefix);
        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Generate Code for Visitor email')]);

        $dateFormat = $this->_localeDate->getDateFormat(\IntlDateFormatter::SHORT);

        $fieldset->addField('store_ids', 'hidden', ['name' => 'store_ids']);

        $fieldset->addField(
            'email_visitor',
            'text',
            [
                'name' => 'email_visitor',
                'required' => true,
                'label' => __('Visitor Email'),
                'title' =>__('Visitor Email')
            ]
        );
        $fieldset->addField(
            'coupon_rule_id',
            'select',
            [
                'label'    => __('Rule'),
                'title'    => __('Rule'),
                'name'     => 'coupon_rule_id',
                'options'  => $this->_helper->getAllRule()
            ]
        ); 
        $fieldset->addField(
            'generate_coupon',
            'button',
            [
                'label'    => '',
                'title'    => '',
                'class'    => 'action-secondary' ,
                'name'     => 'generate_coupon',
                'checked' => false,
                'onclick' => "filterFormSubmit()",
                'onchange' => "",
                'value' => __('Generate Coupon'),
            ]

        );

        $fieldset = $form->addFieldset('mass_generate_fieldset', ['legend' => __('Generate Coupon(s) for Customers '), 'class' => '.fieldset-wrapper-title, .admin__fieldset-wrapper-title']);
        $fieldset->addField(
            'title',
            'note',
            ['name' => 'title', 'label' => __('Use mass-action to generate coupons for existing customers'), 'title' => __('Use mass-action to generate coupons for existing customers'), 'required' => false]
        );

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

   
}
