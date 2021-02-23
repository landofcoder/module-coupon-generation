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
 
namespace Lof\CouponCode\Block\Adminhtml\Report\Sales\Coupon;

class Grid extends \Lof\CouponCode\Block\Adminhtml\Grid\AbstractGrid
{

    protected $_columnDate = 'main_table.created_at';
    protected $_columnGroupBy = '';
    protected $_defaultSort = 'period';
    protected $_defaultDir = 'ASC';
    protected $_resource_grid_collection = null;
    protected $_scopeconfig;
    public function _construct()
    {   
        parent::_construct(); 
        $this->setCountTotals(true);
        $this->setFilterVisibility(true);
        $this->setPagerVisibility(true);
        $this->setId('salescounponGrid');
        $this->setUseAjax(false);  
    }
 /**
     * {@inheritdoc}
     */
    public function getResourceCollectionName()
    {
        return 'Lof\CouponCode\Model\ResourceModel\Sales\Collection';
    }
    protected function _prepareColumns()
    {      
        $filterData = $this->getFilterData();

        $this->addColumn('coupon_code', array(
            'header'        => __('Coupon Code'),
            'index'         => 'coupon_code',
            'width'         => '100px',
            'totals_label'  => __('Total'),
            'html_decorators' => array('nobr'),
            'filter'    => false,
        ));

        $this->addColumn('coupon_rule_name', array(
            'header'        => __('Rule'),
            'index'         => 'coupon_rule_name',
            'width'         => '100px',
            // 'totals_label'  => __('Total'),
            'html_decorators' => array('nobr'),
            'filter'    => false,
        ));

        $this->addColumn('orders_count', array(
            'header'    =>  __('Orders'),
            'index'     => 'orders_count',
            'type'      => 'number',
            'total'     => 'sum',
            'filter'    => false,
        ));

        $this->addColumn('total_qty_ordered', array(
            'header'    => __('Items'),
            'index'     => 'total_qty_ordered',
            'type'      => 'number',
            'total'     => 'sum',
            'filter'    => false,
        ));

        if ($this->getFilterData()->getStoreIds()) {
            $this->setStoreIds(explode(',', $this->getFilterData()->getStoreIds()));
        }
        $currencyCode = $this->getCurrentCurrencyCode();
        $rate = $this->getRate($currencyCode);

        $this->addColumn('total_subtotal_amount', array(
            'header'        =>  __('Subtotal'),
            'type'          => 'currency',
            'currency_code' => $currencyCode,
            'index'         => 'total_subtotal_amount',
            'total'         => 'sum',
            'rate'          => $rate,
            'filter'    => false,
        ));

        
        $this->addColumn('total_discount_amount', array(
            'header'            => __('Discount'),
            'type'              => 'currency',
            'currency_code'     => $currencyCode,
            'index'             => 'total_discount_amount',
            'total'             => 'sum',
            'rate'              => $rate,
            'filter'    => false,
        ));

        $this->addColumn('total_grandtotal_amount', array(
            'header'            => __('Total'),
            'type'              => 'currency',
            'currency_code'     => $currencyCode,
            'index'             => 'total_grandtotal_amount',
            'total'             => 'sum',
            'rate'              => $rate,
            'filter'    => false,
        ));

        $this->addColumn('total_invoiced_amount', array(
            'header'            => __('Invoiced'),
            'type'              => 'currency',
            'currency_code'     => $currencyCode,
            'index'             => 'total_invoiced_amount',
            'total'             => 'sum',
            'rate'              => $rate,
            'filter'    => false,
        ));

        $this->addColumn('total_refunded_amount', array(
            'header'            =>  __('Refunded'),
            'type'              => 'currency',
            'currency_code'     => $currencyCode,
            'index'             => 'total_refunded_amount',
            'total'             => 'sum',
            'rate'              => $rate,
            'filter'    => false,
        ));

        $this->addColumn('total_revenue_amount', array(
            'header'            => __('Revenue'),
            'type'              => 'currency',
            'currency_code'     => $currencyCode,
            'index'             => 'total_revenue_amount',
            'total'             => 'sum',
            'rate'              => $rate,
            'filter'    => false,
        ));
       
        $this->addExportType('*/*/exportSalesByCounponCsv', __('CSV'));
        $this->addExportType('*/*/exportSalesByCounponExcel', __('Excel XML')); 

        return parent::_prepareColumns();
    }

    protected function _prepareCollection()
    {

        $filterData = $this->getFilterData();
        $report_type = $this->getReportType(); 
        $limit = $filterData->getData("limit", null);
        if(!$limit) {
            $limit = $this->_defaultLimit;
        } 
        $report_field = $filterData->getData("report_field", null);
        $report_field = $report_field?$report_field: "main_table.created_at";
        $this->setCulumnDate($report_field);
        $this->setDefaultSort("orders_count");
        $this->setDefaultDir("DESC");
        
        $storeIds = $this->_getStoreIds();  
        $resourceCollection = $this->_objectManager->create($this->getResourceCollectionName())
            ->prepareByCouponCollection() 
            ->setPeriodType($this->getPeriodType())
            ->setDateColumnFilter($this->_columnDate)
            ->addDateFromFilter($filterData->getData('filter_from', null))
            ->addDateToFilter($filterData->getData('filter_to', null)) 
            ->addStoreFilter($storeIds)
            ->setAggregatedColumns($this->_getAggregatedColumns());
 
            
        $this->_addOrderStatusFilter($resourceCollection, $filterData);
        $this->_addCustomFilter($resourceCollection, $filterData);
        $resourceCollection->getSelect() 
                            ->order(new \Zend_Db_Expr($this->getColumnOrder()." ".$this->getColumnDir()));
        $resourceCollection->applyCustomFilter(); 
 
        $resourceCollection->setPageSize((int) $this->getParam($this->getVarNameLimit(), $limit));
        $resourceCollection->setCurPage((int) $this->getParam($this->getVarNamePage(), $this->_defaultPage));

        // die($resourceCollection->getSelect());
        if ($this->getCountSubTotals()) {
            $this->getSubTotals();
        } 
        $this->setCollection($resourceCollection); 
        if(!$this->_registry->registry('report_collection')) {
            $this->_registry->register('report_collection', $resourceCollection);
        } 

        $this->_prepareTotals('orders_count,total_qty_ordered,total_qty_invoiced,total_income_amount,total_revenue_amount,total_profit_amount,total_invoiced_amount,total_paid_amount,total_refunded_amount,total_tax_amount,total_tax_amount_actual,total_shipping_amount,total_shipping_amount_actual,total_discount_amount,total_discount_amount_actual,total_canceled_amount,avg_order_amount,avg_item_cost,total_subtotal_amount,total_grandtotal_amount');
        return parent::_prepareCollection();
    } 

}