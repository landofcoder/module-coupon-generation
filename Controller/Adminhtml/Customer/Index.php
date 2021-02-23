<?php

namespace Lof\CouponCode\Controller\Adminhtml\Customer;

class Index extends \Magento\Catalog\Controller\Adminhtml\Product
{
    /**
     * @var \Magento\Framework\View\Result\LayoutFactory
     */
    protected $resultLayoutFactory;

    /**
     * @param \Magento\Backend\App\Action\Context
     * @param \Magento\Catalog\Controller\Adminhtml\Product\Builder
     * @param \Magento\Framework\View\Result\LayoutFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Catalog\Controller\Adminhtml\Product\Builder $productBuilder,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory
    ) {
        parent::__construct($context, $productBuilder);
        $this->resultLayoutFactory = $resultLayoutFactory;
    }
    /**
     * Get post grid
     *
     * @return \Magento\Framework\View\Result\Layout
     */
    public function execute()
    {
        $id = $this->getRequest()->getparam('id');
        $customer = $this->_objectManager->create('Magento\Customer\Model\Customer');
        $customer->load($id);
        $registry = $this->_objectManager->get('Magento\Framework\Registry');
        $registry->register("current_customer", $customer);
        $this->productBuilder->build($this->getRequest());
        $resultLayout = $this->resultLayoutFactory->create();
        $resultLayout->getLayout()->getBlock('couponcode.customer.edit.tab.couponcode');
        return $resultLayout;
    }
}