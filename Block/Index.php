<?php
namespace Lof\CouponCode\Block;
class Index extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Customer\Model\SessionFactory
     */
    protected $customerSessionFactory;

    /**
     * @var \Lof\CouponCode\Model\CouponFactory
    */
    protected $_gridFactory;

    /**
     * @var mixed|null
     */
    protected $_collection = null;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\SessionFactory $customerSessionFactory,
        \Lof\CouponCode\Model\CouponFactory $gridFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_gridFactory = $gridFactory;
        $this->customerSessionFactory = $customerSessionFactory;
    }

    /**
     * Get collection
     *
     * @return mixed
     */
    public function getCollection()
    {
        if (!$this->_collection) {
            $customer_id = $this->customerSessionFactory->create()
                                ->getCustomer()
                                ->getId();
            $collection = $this->_gridFactory->create()->getCollection();
            // $collection->addFieldToFilter('customer_id', $customer_id);
            $salesruleCouponTable = $collection->getTable("salesrule_coupon");
            $collection->getSelect()
                ->joinLeft(
                    [ "coupon_usage" => $collection->getTable('salesrule_coupon_usage') ],
                    'coupon_usage.coupon_id = main_table.coupon_id and coupon_usage.customer_id = main_table.customer_id',
                    ['times_used']
                )
                ->joinLeft(
                    [ "sc" => $salesruleCouponTable ],
                    'sc.coupon_id = main_table.coupon_id',
                    ['usage_limit', 'usage_per_customer', 'expiration_date', 'created_at']
                )
                ->where('(main_table.customer_id= '. $customer_id .') AND ((coupon_usage.coupon_id IS NULL AND coupon_usage.customer_id IS NULL) Or
                            ((coupon_usage.times_used < (SELECT `'.$salesruleCouponTable.'`.usage_per_customer FROM `'.$salesruleCouponTable.'` WHERE `'.$salesruleCouponTable.'`.coupon_id = coupon_usage.coupon_id)) OR ((SELECT `'.$salesruleCouponTable.'`.usage_per_customer FROM `'.$salesruleCouponTable.'` WHERE `'.$salesruleCouponTable.'`.coupon_id = coupon_usage.coupon_id) IS NULL)))');
            $this->_collection = $collection;
        }
        return $this->_collection;
    }

    /**
     * @inheritdoc
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        $this->pageConfig->getTitle()->set(__('My Coupon Code'));

        if ($this->getCollection()) {
            // create pager block for collection
            $childPager = $this->getChildBlock('lof.couponcode.record.pager');
            if (!$childPager) {
                $pager = $this->getLayout()->createBlock(
                    'Magento\Theme\Block\Html\Pager',
                    'lof.couponcode.record.pager'
                )->setCollection(
                    $this->getCollection() // assign collection to pager
                );
                $this->setChild('pager', $pager);// set pager block in layout
            }
        }
        return $this;
    }

    /**
     * @return string
     */
    // method for get pager html
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }
}
