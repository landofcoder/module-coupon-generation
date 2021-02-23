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
 * @copyright  Copyright (c) 2019 Landofcoder (http://www.landofcoder.com/)
 * @license    http://www.landofcoder.com/LICENSE-1.0.html
 */
namespace Lof\CouponCode\Block\Adminhtml\Customer\Edit\Tab;

class CouponCode extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Catalog\Model\Product\Attribute\Source\Status
     */
    protected $_status;

    protected $_couponFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context
     * @param \Magento\Backend\Helper\Data
     * @param \Magento\Catalog\Model\Product\Attribute\Source\Status
     * @param \Magento\Framework\Registry
     * @param \Lof\CouponCode\Model\Coupon
     * @param array
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Catalog\Model\Product\Attribute\Source\Status $status,
        \Magento\Framework\Registry $coreRegistry,
        \Lof\CouponCode\Model\Coupon $couponFactory,
        array $data = []
        ) {
        $this->_status = $status;
        $this->_coreRegistry = $coreRegistry;
        $this->_couponFactory = $couponFactory;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * Set grid params
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('coupon_code_grid');
        $this->setDefaultSort('couponcode_id');
        $this->setUseAjax(true);
    }

    /**
     * Retirve currently edited product model
     *
     * @return \Magento\Catalog\Model\Product
     */
    public function getCustomer()
    {
        return $this->_coreRegistry->registry('current_customer');
    }

    /**
     * Add filter
     *
     * @param object $column
     * @return $this
     */
    protected function _addColumnFilterToCollection($column)
    {
        parent::_addColumnFilterToCollection($column);
        return $this;
    }

    /**
     * Prepare collection
     *
     * @return \Magento\Backend\Block\Widget\Grid\Extended
     */
    protected function _prepareCollection()
    {
        $collection = $this->_couponFactory->getCollection();
        $customer = $this->getCustomer();
        $collection->addFieldToFilter('customer_id',$customer->getId());
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Add button to grid
    */
    public function getMainButtonsHtml()
    {
        $html = parent::getMainButtonsHtml();//get the parent class buttonssetLocation(".  . ")
        $addButton = $this->getLayout()->createBlock('Magento\Backend\Block\Widget\Button')
            ->setData(array(
            'label'     => 'Generate Coupon',
            'onclick'   => "window.open('". $this->getUrl('couponcode/generate/index') ."', '_blank');",
            'class'   => 'task'
        ))->toHtml();
        return $addButton.$html;
    }
    protected function _prepareColumns()
    {
        $this->addColumn(
            'gcouponcode_id',
            [
                'header' => __('ID'),
                'sortable' => true,
                'index' => 'couponcode_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
            );
        $this->addColumn(
            'gcode',
            [
                'header' => __('Code'),
                'index' => 'code',
                'header_css_class' => 'col-code',
                'column_css_class' => 'col-code'
            ]
            );

        $this->addColumn(
            'galias',
            [
                'header' => __('Alias'),
                'index' => 'alias',
                'style' => 'width:100px;',
                'header_css_class' => 'col-alias',
                'column_css_class' => 'col-alias'
            ]
            );
        $this->addColumn(
            'gemail',
            [
                'header' => __('Email'),
                'index' => 'email',
                'style' => 'width:100px;',
                'header_css_class' => 'col-email',
                'column_css_class' => 'col-email'
            ]
            );

        $this->addColumn(
            'gis_active',
            [
                'header' => __('Status'),
                'index' => 'is_active',
                'type' => 'options',
                'options' => $this->_status->getOptionArray(),
                'header_css_class' => 'col-status',
                'column_css_class' => 'col-status'
            ]
            );

        $this->addColumn(
            'gaction',
            [
                'header' => __('Action'),
                'type' => 'action',
                'renderer'  => 'Lof\CouponCode\Block\Adminhtml\Coupon\Renderer\CouponAction',
            ]
            );
        return parent::_prepareColumns();
    }


    /**
     * Apply filter to cross-sell grid.
     *
     * @param \Magento\Catalog\Model\ResourceModel\Product\Link\Product\Collection $collection
     * @param \Magento\Backend\Block\Widget\Grid\Column\Extended $column
     * @return $this
     */
    public function filterProductPosition($collection, $column)
    {
        $condition = $column->getFilter()->getCondition();
        $customer = $this->getCustomer();
        $condition['id'] = $customer->getCustomerId();
        $collection->addLinkCategoryToFilter($column->getIndex(), $condition);
        return $this;
    }
}

