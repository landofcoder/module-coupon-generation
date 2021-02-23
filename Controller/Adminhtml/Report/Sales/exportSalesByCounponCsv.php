<?php
namespace Lof\CouponCode\Controller\Adminhtml\Report\Sales;

use Magento\Framework\App\Action\Action;
use Lof\CouponCode\Model\ResourceModel\Sales\Collection;

class ExportSalesByCounponCsv extends Action{
    protected $fileFactory;
    protected $csvProcessor;
    protected $directoryList;
    protected $saleCollection;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Framework\File\Csv $csvProcessor,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        Collection $saleCollection
    )
    {
        $this->fileFactory = $fileFactory;
        $this->csvProcessor = $csvProcessor;
        $this->directoryList = $directoryList;
        $this->saleCollection = $saleCollection;
        parent::__construct($context);
    }

    public function execute()
    {
        $fileName = 'report_sale_by_coupon.csv';
        $filePath = $this->directoryList->getPath(\Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR)
            . "/" . $fileName;

        $dataSaleByCoupon = $this->getDataSalesByCoupon();

        $this->csvProcessor
            ->setDelimiter(';')
            ->setEnclosure('"')
            ->saveData(
                $filePath,
                $dataSaleByCoupon
            );

        return $this->fileFactory->create(
            $fileName,
            [
                'type' => "filename",
                'value' => $fileName,
                'rm' => true,
            ],
            \Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR,
            'application/octet-stream'
        );
    }

    protected function getDataSalesByCoupon()
    {
        $params = $this->getRequest()->getParams();
        $saleCollection = $this->saleCollection->prepareByCouponCollection()
            ->addDateFromFilter(urldecode($params['filter_from']))
            ->addDateToFilter(urldecode($params['filter_to'])) ;
        $saleCollection->applyCustomFilter();
        $total = [
            'orders_count' => 0,
            'total_qty_ordered' => 0,
            'total_subtotal_amount' => 0,
            'total_discount_amount' => 0,
            'total_income_amount' => 0,
            'total_invoiced_amount' => 0,
            'total_revenue_amount' => 0,
            'total_refunded_amount' => 0
        ];
        $result = [];
        $result[] = [
            'Coupon Code',
            'Rule',
            'Orders',
            'Items',
            'Subtotal',
            'Discount',
            'Total',
            'Invoiced',
            'Refunded',
            'Revenue',
        ];
        foreach ($saleCollection as $item) {
            $total['orders_count'] += $item['orders_count'];
            $total['total_qty_ordered'] += $item['total_qty_ordered'];
            $total['total_subtotal_amount'] += $item['total_subtotal_amount'];
            $total['total_discount_amount'] += $item['total_discount_amount'];
            $total['total_income_amount'] += $item['total_income_amount'];
            $total['total_invoiced_amount'] += $item['total_invoiced_amount'];
            $total['total_refunded_amount'] += $item['total_refunded_amount'];
            $total['total_revenue_amount'] += $item['total_revenue_amount'];
            $result[] = [
                ($item['coupon_code']),
                ($item['coupon_rule_name']),
                number_format($item['orders_count']),
                number_format($item['total_qty_ordered']),
                number_format($item['total_subtotal_amount'],2),
                number_format($item['total_discount_amount'],2),
                number_format($item['total_income_amount'],2),
                number_format($item['total_invoiced_amount'],2),
                number_format($item['total_refunded_amount'],2),
                number_format($item['total_revenue_amount'],2)
            ];
        }
        $result[] = [
            'Total',
            null,
            $total['orders_count'],
            $total['total_qty_ordered'],
            number_format($total['total_subtotal_amount'],2),
            number_format($total['total_discount_amount'],2),
            number_format($total['total_income_amount'],2),
            number_format($total['total_invoiced_amount'],2),
            number_format($total['total_refunded_amount'],2),
            number_format($total['total_revenue_amount'],2)
        ];
        return $result;
    }
}