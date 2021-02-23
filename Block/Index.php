<?php
namespace Lof\CouponCode\Block;
class Index extends \Magento\Framework\View\Element\Template
{
     protected $customerSession;
     protected $_gridFactory;
     public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Lof\CouponCode\Model\CouponFactory $gridFactory,
        array $data = []
     ) {
        $this->_gridFactory = $gridFactory;
        parent::__construct($context, $data);

        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $customerSession = $om->create('Magento\Customer\Model\Session');
        $customer_id = $customerSession->getCustomer()->getId();
        $collection = $this->_gridFactory->create()->getCollection();
        // $collection->addFieldToFilter('customer_id', $customer_id);
        $collection->getSelect()
        ->joinLeft(
            array("coupon_usage"=>'salesrule_coupon_usage'), 
            'coupon_usage.coupon_id = main_table.coupon_id and coupon_usage.customer_id = main_table.customer_id')
        ->where('(main_table.customer_id= '. $customer_id .') AND ((coupon_usage.coupon_id IS NULL AND coupon_usage.customer_id IS NULL) Or 
                    ((coupon_usage.times_used < (SELECT salesrule_coupon.usage_per_customer FROM salesrule_coupon WHERE salesrule_coupon.coupon_id = coupon_usage.coupon_id)) OR ((SELECT salesrule_coupon.usage_per_customer FROM salesrule_coupon WHERE salesrule_coupon.coupon_id = coupon_usage.coupon_id) IS NULL)))');
        $this->setCollection($collection);
        $this->pageConfig->getTitle()->set(__('My Coupon Code'));
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getCollection()) {
            // create pager block for collection
            $pager = $this->getLayout()->createBlock(
                'Magento\Theme\Block\Html\Pager',
                'lof.couponcode.record.pager'
            )->setCollection(
                $this->getCollection() // assign collection to pager
            );
            $this->setChild('pager', $pager);// set pager block in layout
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