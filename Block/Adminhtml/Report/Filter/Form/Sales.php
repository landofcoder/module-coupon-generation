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
namespace Lof\CouponCode\Block\Adminhtml\Report\Filter\Form;

/**
 * Sales Adminhtml report filter form order
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 */
class Sales extends \Lof\CouponCode\Block\Adminhtml\Report\Filter\Form
{
    protected function _prepareForm()
    {
        $actionUrl = $this->getUrl($this->getFormActionUrl());
        $report_type = $this->getReportType();   
        $report_types = array("statistics","customergroup");
        $notin_report_types = array("customergroup", "producttype", "hour", "dayofweek", "country", "regionreport", "zipcodereport", "coupon");


        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            [
                'data' => [
                    'id' => 'filter_form',
                    'action' => $actionUrl,
                    'method' => 'get'
                ]
            ]
        );
        $htmlIdPrefix = 'sales_report_';
        $form->setHtmlIdPrefix($htmlIdPrefix); 
        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Filter')]);

        $statuses = $this->_objectManager->create('Magento\Sales\Model\Order\Config')->getStatuses(); 
        $dateFormat = $this->_localeDate->getDateFormat(\IntlDateFormatter::SHORT);
        $values = array();
        foreach ($statuses as $code => $label) {
            if (false === strpos($code, 'pending')) {
                $values[] = array(
                    'label' => __($label),
                    'value' => $code
                );
            }
        }

        $fieldset->addField('store_ids', 'hidden', array(
            'name'  => 'store_ids'
        ));
        $fieldset->addField(
            'filter_from',
            'date',
            [
                'name' => 'filter_from',
                'date_format' => $dateFormat,
                'label' => __('From'),
                'title' => __('From'),
                'required' => true,
                'class' => 'admin__control-text'
            ]
        );

        $fieldset->addField(
            'filter_to',
            'date',
            [
                'name' => 'filter_to',
                'date_format' => $dateFormat,
                'label' => __('To'),
                'title' => __('To'),
                'required' => true,
                'class' => 'admin__control-text'
            ]
        );

        $fieldset->addField('report_field', 'select', array(
            'name'      => 'report_field',
            'options'   => array(
                        'main_table.created_at' =>  __('Order Created'),
                        'main_table.updated_at' =>  __('Order Updated'), 
            ),
            'label'     => __('Match Period To'),
        ));
        
       
        if(!in_array($report_type, $notin_report_types)) {
            $fieldset->addField('group_by', 'select', array(
                'name'      => 'group_by',
                'label'     =>  __('Show By'),
                'options'   => array(
                        'day' =>  __('Day'),
                        'week' =>  __('Week'),
                        'month' =>  __('Month'),
                        'quarter' =>  __('Quarter'),
                        'year' =>  __('Year'),
                    ),
                'note'      =>  __('Show period time by option.'),
            ));
        }

        $fieldset->addField('show_order_statuses', 'select', [
                'name'      => 'show_order_statuses',
                'label'     => __('Order Status'),
                'options'   => array(
                        '0' => __('Any'),
                        '1' => __('Specified'),
                    ),
                'note'      => __('Applies to Any of the Specified Order Statuses'),
            ], 'to');

        $fieldset->addField('order_statuses', 'multiselect',[
                'name'      => 'order_statuses',
                'values'    => $values,
                'label'     => __('Status'),
                'display'   => 'none'
            ], 'show_order_statuses');

            // define field dependencies
        if ($this->getFieldVisibility('show_order_statuses') && $this->getFieldVisibility('order_statuses')) { 
            $this->setChild('form_after', $this->getLayout()->createBlock('Magento\Backend\Block\Widget\Form\Element\Dependence')
                    ->addFieldMap("{$htmlIdPrefix}show_order_statuses", 'show_order_statuses')
                    ->addFieldMap("{$htmlIdPrefix}order_statuses", 'order_statuses')
                    ->addFieldDependence('order_statuses', 'show_order_statuses', '1')

            );
            } 
        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
