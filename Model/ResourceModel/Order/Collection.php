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


/**
 * Reports orders collection
 *
 * @category    Mage
 * @package     Mage_Reports
 * @author      Magento Core Team <core@magentocommerce.com>
 */

namespace Lof\CouponCode\Model\ResourceModel\Order;
class  Collection extends \Lof\CouponCode\Model\ResourceModel\AbstractReport\Ordercollection
{
  /**
     * Is live
     *
     * @var boolean
     */
    protected $_isLive   = false;
    

    /**
     * Sales amount expression
     *
     * @var string
     */
    protected $_salesAmountExpression;

    /**
     * Check range for live mode
     *
     * @param unknown_type $range
     * @return Mage_Reports_Model_Resource_Order_Collection
     */


    public function setDateColumnFilter($column_name = '') {
        if($column_name) {
            $this->_date_column_filter = $column_name;
        }
        return $this;
    }
 public function getDateColumnFilter() {
        return $this->_date_column_filter;
    }
    /**
     * Set status filter
     *
     * @param string $orderStatus
     * @return Mage_Sales_Model_Resource_Report_Collection_Abstract
     */
    public function addDateFromFilter($from = null)
    {
        $this->_from_date_filter = $from;
        return $this;
    }

    /**
     * Set status filter
     *
     * @param string $orderStatus
     * @return Mage_Sales_Model_Resource_Report_Collection_Abstract
     */
    public function addDateToFilter($to = null)
    {
        $this->_to_date_filter = $to;
        return $this;
    }

    public function setPeriodType($period_type = "") {
        $this->_period_type = $period_type;
        return $this;
    }

    /**
     * Set status filter
     *
     * @param string $orderStatus
     * @return Mage_Sales_Model_Resource_Report_Collection_Abstract
     */
    public function addProductIdFilter($product_id = 0)
    {
        $this->_product_id_filter = $product_id;
        return $this;
    }

    /**
     * Set status filter
     *
     * @param string $orderStatus
     * @return Mage_Sales_Model_Resource_Report_Collection_Abstract
     */
    public function addProductSkuFilter($product_sku = "")
    {
        $this->_product_sku_filter = $product_sku;
        return $this;
    }


    protected function _applyDateFilter()
    {
        $select_datefield = array();
        if($this->_period_type) {
            switch( $this->_period_type) {
                case "year":
                    $select_datefield = array(
                        'period'  => 'YEAR('.$this->getDateColumnFilter().')'
                    );
                break;
                case "quarter":
                    $select_datefield = array(
                        'period'  => 'CONCAT(QUARTER('.$this->getDateColumnFilter().'),"/",YEAR('.$this->getDateColumnFilter().'))'
                    );
                break;
                case "week":
                    $select_datefield = array(
                        'period'  => 'CONCAT(YEAR('.$this->getDateColumnFilter().'),"", WEEK('.$this->getDateColumnFilter().'))'
                    );
                break;
                case "day":
                    $select_datefield = array(
                        'period'  => 'DATE('.$this->getDateColumnFilter().')'
                    );
                break;
                case "hour":
                    $select_datefield = array(
                        'period'  => "DATE_FORMAT(".$this->getDateColumnFilter().", '%H:00')"
                    );
                break;
                case "weekday":
                    $select_datefield = array(
                        'period'  => 'WEEKDAY('.$this->getDateColumnFilter().')'
                    );
                break;
                case "month":
                default:
                    $select_datefield = array(
                        'period'  => 'CONCAT(MONTH('.$this->getDateColumnFilter().'),"/",YEAR('.$this->getDateColumnFilter().'))',
                        'period_sort'  => 'CONCAT(MONTH('.$this->getDateColumnFilter().'),"",YEAR('.$this->getDateColumnFilter().'))'
                    );
                break;
            }
        }
        if($select_datefield) {
            $this->getSelect()->columns($select_datefield);
        }


        // sql theo filter date 
        if($this->_to_date_filter && $this->_from_date_filter) { 
            // $locale = new Zend_Locale(Mage::getStoreConfig(Mage_Core_Model_Locale::XML_PATH_DEFAULT_LOCALE)); 
            // $dateStart = new Zend_Date(null, null, $locale);
            // $dateStart->setDate($this->_from_date_filter, $locale->getTranslation(null, 'date', $locale));
            // $dateStart->setHour(0);
            // $dateStart->setMinute(0);
            // $dateStart->setSecond(0);

            // $dateEnd = new Zend_Date(null, null, $locale);
            // $dateEnd->setDate($this->_to_date_filter, $locale->getTranslation(null, 'date', $locale));
            // $dateEnd->setHour(23);
            // $dateEnd->setMinute(59);
            // $dateEnd->setSecond(59);

            // $dateStart->setTimezone('Etc/UTC');
            // $dateEnd->setTimezone('Etc/UTC'); 

            // kiem tra lai doan convert ngay thang nay ! 
            
            $dateStart = $this->_localeDate->convertConfigTimeToUtc($this->_from_date_filter,'Y-m-d 00:00:00');
            $endStart = $this->_localeDate->convertConfigTimeToUtc($this->_to_date_filter, 'Y-m-d 23:59:59'); 
            $dateRange = array('from' => $dateStart, 'to' => $endStart , 'datetime' => true);

            $this->addFieldToFilter($this->getDateColumnFilter(), $dateRange);
        }


        return $this;
    }

    public function applyCustomFilter() {
        $this->_applyDateFilter();
        $this->_applyStoresFilter();
        $this->_applyOrderStatusFilter();
        return $this;
    }

    public function prepareOrderDetailedCollection() {
        $hide_fields = array("avg_item_cost", "avg_order_amount");
        $this->setMainTableId('main_table.entity_id');
        $this->_aggregateByField('main_table.entity_id', $hide_fields);
        return $this;
    }

    public function prepareOrderItemDetailedCollection() {
        $hide_fields = array("avg_item_cost", "avg_order_amount");
        $this->getSelect()->reset(\Magento\Framework\DB\Select::COLUMNS);
        $this->_aggregateOrderItemsByField('', $hide_fields);
        return $this;
    }


      /**
     * Aggregate Orders data by custom field
     *
     * @throws Exception
     * @param string $aggregationField
     * @param mixed $from
     * @param mixed $to
     * @return Mage_Sales_Model_Resource_Report_Order_Createdat
     */
    protected function _aggregateByField($aggregationField = "", $hide_fields = array(), $show_fields = array())
    {
        $adapter = $this->getResource()->getConnection();
        // $adapter->beginTransaction();
        try {

            $subSelect = null;
            // Columns list
            $columns = array(
                // convert dates from UTC to current admin timezone
                'store_id'                       => 'main_table.store_id',
                'order_status'                   => 'main_table.status',
                'product_type'                   => 'oi.product_type',
                'total_cost_amount'              => new \Zend_Db_Expr('IFNULL(SUM(oi.total_cost_amount),0)'),
                'orders_count'                   => new \Zend_Db_Expr('COUNT(main_table.entity_id)'),
                'total_qty_ordered'              => new \Zend_Db_Expr('SUM(oi.total_qty_ordered)'),
                'total_qty_shipping'             => new \Zend_Db_Expr('SUM(oi.total_qty_shipping)'),
                'total_qty_refunded'             => new \Zend_Db_Expr('SUM(oi.total_qty_refunded)'),
                'total_subtotal_amount'          => new \Zend_Db_Expr('SUM(main_table.subtotal)'),
                'total_qty_invoiced'             => new \Zend_Db_Expr('SUM(oi.total_qty_invoiced)'),
                'total_grandtotal_amount'        => new \Zend_Db_Expr('SUM(main_table.grand_total)'),
                'avg_item_cost'                  => new \Zend_Db_Expr('AVG(oi.total_item_cost)'),
                'avg_order_amount'               => new \Zend_Db_Expr(
                sprintf('AVG((%s - %s - %s - (%s - %s - %s)) * %s)',
                    $adapter->getIfNullSql('main_table.base_total_invoiced', 0),
                    $adapter->getIfNullSql('main_table.base_tax_invoiced', 0),
                    $adapter->getIfNullSql('main_table.base_shipping_invoiced', 0),
                    $adapter->getIfNullSql('main_table.base_total_refunded', 0),
                    $adapter->getIfNullSql('main_table.base_tax_refunded', 0),
                    $adapter->getIfNullSql('main_table.base_shipping_refunded', 0),
                    $adapter->getIfNullSql('main_table.base_to_global_rate', 0)
                    )
                ),
                'total_income_amount'            => new \Zend_Db_Expr(
                    sprintf('SUM((%s - %s) * %s)',
                        $adapter->getIfNullSql('main_table.base_grand_total', 0),
                        $adapter->getIfNullSql('main_table.base_total_canceled',0),
                        $adapter->getIfNullSql('main_table.base_to_global_rate',0)
                    )
                ),
                'total_revenue_amount'           => new \Zend_Db_Expr(
                    sprintf('SUM((%s - %s - %s - (%s - %s - %s)) * %s)',
                        $adapter->getIfNullSql('main_table.base_total_invoiced', 0),
                        $adapter->getIfNullSql('main_table.base_tax_invoiced', 0),
                        $adapter->getIfNullSql('main_table.base_shipping_invoiced', 0),
                        $adapter->getIfNullSql('main_table.base_total_refunded', 0),
                        $adapter->getIfNullSql('main_table.base_tax_refunded', 0),
                        $adapter->getIfNullSql('main_table.base_shipping_refunded', 0),
                        $adapter->getIfNullSql('main_table.base_to_global_rate', 0)
                    )
                ),
                'total_profit_amount'            => new \Zend_Db_Expr(
                    sprintf('SUM(((%s - %s) - (%s - %s) - (%s - %s) - %s) * %s)',
                        $adapter->getIfNullSql('main_table.base_total_paid', 0),
                        $adapter->getIfNullSql('main_table.base_total_refunded', 0),
                        $adapter->getIfNullSql('main_table.base_tax_invoiced', 0),
                        $adapter->getIfNullSql('main_table.base_tax_refunded', 0),
                        $adapter->getIfNullSql('main_table.base_shipping_invoiced', 0),
                        $adapter->getIfNullSql('main_table.base_shipping_refunded', 0),
                        $adapter->getIfNullSql('main_table.base_total_invoiced_cost', 0),
                        $adapter->getIfNullSql('main_table.base_to_global_rate', 0)
                    )
                ),
                'total_invoiced_amount'          => new \Zend_Db_Expr(
                    sprintf('SUM(%s * %s)',
                        $adapter->getIfNullSql('main_table.base_total_invoiced', 0),
                        $adapter->getIfNullSql('main_table.base_to_global_rate', 0)
                    )
                ),
                'total_canceled_amount'          => new \Zend_Db_Expr(
                    sprintf('SUM(%s * %s)',
                        $adapter->getIfNullSql('main_table.base_total_canceled', 0),
                        $adapter->getIfNullSql('main_table.base_to_global_rate', 0)
                    )
                ),
                'total_paid_amount'              => new \Zend_Db_Expr(
                    sprintf('SUM(%s * %s)',
                        $adapter->getIfNullSql('main_table.base_total_paid', 0),
                        $adapter->getIfNullSql('main_table.base_to_global_rate', 0)
                    )
                ),
                'total_refunded_amount'          => new \Zend_Db_Expr(
                    sprintf('SUM(%s * %s)',
                        $adapter->getIfNullSql('main_table.base_total_refunded', 0),
                        $adapter->getIfNullSql('main_table.base_to_global_rate', 0)
                    )
                ),
                'total_tax_amount'               => new \Zend_Db_Expr(
                    sprintf('SUM((%s - %s) * %s)',
                        $adapter->getIfNullSql('main_table.base_tax_amount', 0),
                        $adapter->getIfNullSql('main_table.base_tax_canceled', 0),
                        $adapter->getIfNullSql('main_table.base_to_global_rate', 0)
                    )
                ),
                'total_tax_amount_actual'        => new \Zend_Db_Expr(
                    sprintf('SUM((%s -%s) * %s)',
                        $adapter->getIfNullSql('main_table.base_tax_invoiced', 0),
                        $adapter->getIfNullSql('main_table.base_tax_refunded', 0),
                        $adapter->getIfNullSql('main_table.base_to_global_rate', 0)
                    )
                ),
                'total_shipping_amount'          => new \Zend_Db_Expr(
                    sprintf('SUM((%s - %s) * %s)',
                        $adapter->getIfNullSql('main_table.base_shipping_amount', 0),
                        $adapter->getIfNullSql('main_table.base_shipping_canceled', 0),
                        $adapter->getIfNullSql('main_table.base_to_global_rate', 0)
                    )
                ),
                'total_shipping_amount_actual'   => new \Zend_Db_Expr(
                    sprintf('SUM((%s - %s) * %s)',
                        $adapter->getIfNullSql('main_table.base_shipping_invoiced', 0),
                        $adapter->getIfNullSql('main_table.base_shipping_refunded', 0),
                        $adapter->getIfNullSql('main_table.base_to_global_rate', 0)
                    )
                ),
                'total_discount_amount'          => new \Zend_Db_Expr(
                    sprintf('SUM((ABS(%s) - %s) * %s)',
                        $adapter->getIfNullSql('main_table.base_discount_amount', 0),
                        $adapter->getIfNullSql('main_table.base_discount_canceled', 0),
                        $adapter->getIfNullSql('main_table.base_to_global_rate', 0)
                    )
                ),
                'total_discount_amount_actual'   => new \Zend_Db_Expr(
                    sprintf('SUM((%s - %s) * %s)',
                        $adapter->getIfNullSql('main_table.base_discount_invoiced', 0),
                        $adapter->getIfNullSql('main_table.base_discount_refunded', 0),
                        $adapter->getIfNullSql('main_table.base_to_global_rate', 0)
                    )
                )
            );
            
            if($hide_fields) {
                foreach($hide_fields as $field){
                    if(isset($columns[$field])){
                        unset($columns[$field]);
                    }
                }
            }

            $selectOrderItem = $adapter->select();

            $qtyCanceledExpr = $adapter->getIfNullSql('qty_canceled', 0);
            $cols            = array(
                'order_id'           => 'order_id',
                'product_id'         => 'product_id',
                'product_type'       => 'product_type',
                'created_at'         => 'created_at',
                'sku'                => 'sku',
                'total_cost_amount'  => new \Zend_Db_Expr('SUM(base_cost)'),
                'total_qty_ordered'  => new \Zend_Db_Expr("SUM(qty_ordered - {$qtyCanceledExpr})"),
                'total_qty_invoiced' => new \Zend_Db_Expr('SUM(qty_invoiced)'),
                'total_qty_shipping' => new \Zend_Db_Expr('SUM(qty_shipped)'),
                'total_qty_refunded' => new \Zend_Db_Expr('SUM(qty_refunded)'),
                'total_item_cost'    => new \Zend_Db_Expr('SUM(row_total)'),
            );
            $selectOrderItem->from($this->getTable('sales_order_item'), $cols)
                ->where('parent_item_id IS NULL')
                ->group('order_id');

            
            // $columns['store_id']       = new \Zend_Db_Expr($this->_storeManager->getStore(\Magento\Store\Model\Store::ADMIN_CODE)->getId()); 
           
            $this->getSelect()->columns($columns)
                ->join(array('oi' => $selectOrderItem), 'oi.order_id = main_table.entity_id', array());

            if($aggregationField) {
                $this->getSelect()->group($aggregationField);
            }
            
            // $adapter->commit();
        } catch (Exception $e) {
            $adapter->rollBack();
            throw $e;
        }

        return $this;
    }


    /**
     * Aggregate Orders data by custom field
     *
     * @throws Exception
     * @param string $aggregationField
     * @param mixed $from
     * @param mixed $to
     * @return Mage_Sales_Model_Resource_Report_Order_Createdat
     */
    protected function _aggregateOrderItemsByField($aggregationField = "", $hide_fields = array(), $show_fields = array())
    {
        $adapter = $this->getResource()->getConnection(); 
        try {

            $subSelect = null;
            $qtyCanceledExpr = $adapter->getIfNullSql('oi.qty_canceled', 0);
            // Columns list
            $columns = array(
                // convert dates from UTC to current admin timezone
                'oi.*'                           => 'oi.*',
                'increment_id'                   => 'main_table.increment_id',
                'status'                         => 'main_table.status',
                'created_at'                     => 'main_table.created_at',
                'store_id'                       => 'main_table.store_id',
                'order_status'                   => 'main_table.status',
                'real_tax_refunded'              => new \Zend_Db_Expr("IFNULL(oi.tax_refunded,0)"),
                'real_qty_shipped'               => new \Zend_Db_Expr("IFNULL(oi.qty_shipped,0)"),
                'real_qty_refunded'              => new \Zend_Db_Expr("IFNULL(oi.qty_refunded,0)"),
                'real_qty_ordered'               => new \Zend_Db_Expr("(oi.qty_ordered - {$qtyCanceledExpr})"),
                'subtotal'                       => new \Zend_Db_Expr("(oi.qty_ordered * oi.base_price)"),
                'total_revenue_amount'           => new \Zend_Db_Expr(
                    sprintf('(CASE WHEN oi.base_row_invoiced > 0 THEN IFNULL((%s - %s - %s - %s - %s),0) ELSE 0 END)',
                        $adapter->getIfNullSql('oi.base_row_invoiced', 0),
                        $adapter->getIfNullSql('oi.base_tax_invoiced', 0),
                        $adapter->getIfNullSql('oi.discount_amount', 0),
                        $adapter->getIfNullSql('oi.base_amount_refunded', 0),
                        $adapter->getIfNullSql('oi.base_tax_refunded', 0)
                    )
                ),
                'row_refunded_incl_tax'           => new \Zend_Db_Expr(
                    sprintf('(%s + %s)',
                        $adapter->getIfNullSql('oi.amount_refunded', 0),
                        $adapter->getIfNullSql('oi.tax_refunded', 0)
                    )
                ),
                'row_invoiced_incl_tax'           => new \Zend_Db_Expr(
                    sprintf('(%s + %s)',
                        $adapter->getIfNullSql('oi.row_invoiced', 0),
                        $adapter->getIfNullSql('oi.tax_invoiced', 0)
                    )
                ),
                'total_cost_amount'           => new \Zend_Db_Expr(
                    sprintf('(%s * %s)',
                        $adapter->getIfNullSql('oi.qty_ordered', 0),
                        $adapter->getIfNullSql('oi.base_cost', 0)
                    )
                ),
                'total_revenue_amount_excl_tax'           => new \Zend_Db_Expr(
                    sprintf('(CASE WHEN oi.base_row_invoiced > 0 THEN IFNULL((%s - %s - %s),0) ELSE 0 END)',
                        $adapter->getIfNullSql('oi.base_row_invoiced', 0),
                        $adapter->getIfNullSql('oi.discount_amount', 0),
                        $adapter->getIfNullSql('oi.base_amount_refunded', 0)
                    )
                ),
                'total_profit_amount'           => new \Zend_Db_Expr(
                    sprintf('(CASE WHEN oi.base_row_invoiced > 0 THEN IFNULL(((%s - %s - %s - %s - %s) - (%s * %s)),0) ELSE 0 END)',
                        $adapter->getIfNullSql('oi.base_row_invoiced', 0),
                        $adapter->getIfNullSql('oi.base_tax_invoiced', 0),
                        $adapter->getIfNullSql('oi.discount_amount', 0),
                        $adapter->getIfNullSql('oi.base_amount_refunded', 0),
                        $adapter->getIfNullSql('oi.base_tax_refunded', 0),
                        $adapter->getIfNullSql('oi.qty_ordered', 0),
                        $adapter->getIfNullSql('oi.base_cost', 0)
                    )
                ),
                'total_margin'           => new \Zend_Db_Expr(
                    sprintf('(CASE WHEN oi.base_row_invoiced > 0 THEN IFNULL(ROUND((((%s - %s - %s -  %s - %s) - (%s * %s))/(%s - %s - %s -  %s - %s))*100), 100) ELSE 0 END)',
                        $adapter->getIfNullSql('oi.base_row_invoiced', 0),
                        $adapter->getIfNullSql('oi.base_tax_invoiced', 0),
                        $adapter->getIfNullSql('oi.discount_amount', 0),
                        $adapter->getIfNullSql('oi.base_amount_refunded', 0),
                        $adapter->getIfNullSql('oi.base_tax_refunded', 0),
                        $adapter->getIfNullSql('oi.qty_ordered', 0),
                        $adapter->getIfNullSql('oi.base_cost', 0),
                        $adapter->getIfNullSql('oi.base_row_invoiced', 0),
                        $adapter->getIfNullSql('oi.base_tax_invoiced', 0),
                        $adapter->getIfNullSql('oi.discount_amount', 0),
                        $adapter->getIfNullSql('oi.base_amount_refunded', 0),
                        $adapter->getIfNullSql('oi.base_tax_refunded', 0)
                    )
                ),
            );
            
            if($hide_fields) {
                foreach($hide_fields as $field){
                    if(isset($columns[$field])){
                        unset($columns[$field]);
                    }
                }
            }

            $selectOrderItem = $adapter->select();

            $selectOrderItem->from($this->getTable('sales_order_item'), array("*"))
                ->where('parent_item_id IS NULL');

            
            // $columns['store_id']       = new \Zend_Db_Expr($this->_storeManager->getStore(\Magento\Store\Model\Store::ADMIN_CODE)->getId()); 
            
            $this->getSelect()->columns($columns)
                ->join(array('oi' => $selectOrderItem), 'oi.order_id = main_table.entity_id', array());

            if($aggregationField) {
                $this->getSelect()->group($aggregationField);
            }
            
            // $adapter->commit();
        } catch (Exception $e) {
            $adapter->rollBack();
            throw $e;
        }

        return $this;
    }

}
