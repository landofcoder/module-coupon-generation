<?php
namespace Lof\CouponCode\Controller\Customer;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\ResultFactory;

class Index extends \Magento\Framework\App\Action\Action
{

    /**
     * @var resultFactory
     */
    protected $resultFactory;

    /**
     * @var \Lof\CouponCode\Helper\Data
     */
    protected $_couponHelper;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * List of actions that are allowed for not authorized users.
     *
     * @var string[]
     */
    protected $openActions = [
        'external',
        'postexternal',
        'print',
    ];

    /**
     * @param Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Lof\CouponCode\Helper\Data $helper
     */
    public function __construct(
        Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Lof\CouponCode\Helper\Data $helper
    ) {
        $this->customerSession = $customerSession;
        $this->resultFactory = $context->getResultFactory();
        $this->_couponHelper = $helper;
        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    public function dispatch(\Magento\Framework\App\RequestInterface $request)
    {

        if (!$this->getRequest()->isDispatched()) {
            parent::dispatch($request);
        }

        $action = strtolower($this->getRequest()->getActionName());
        $pattern = '/^('.implode('|', $this->openActions).')$/i';

        if (!preg_match($pattern, $action)) {
            if (!$this->customerSession->authenticate()) {
                $this->_actionFlag->set('', 'no-dispatch', true);
            }
        } else {
            $this->customerSession->setNoReferer(true);
        }
        $result = parent::dispatch($request);
        $this->customerSession->unsNoReferer(false);

        return $result;
    }

	public function execute() {
        try {
            if(!$this->_couponHelper->getConfig('general_settings/show') || !$this->_couponHelper->getConfig('general_settings/show_on_customer')) {
                $resultForward = $this->resultFactory->create(ResultFactory::TYPE_FORWARD);
                return $resultForward->forward('noroute');
            }
    		/** @var \Magento\Framework\View\Result\Page $resultPage */
            $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
            $resultPage->getConfig()->getTitle()->set(__('My Coupons'));
            $this->initPage($resultPage);

            $block = $resultPage->getLayout()->getBlock('customer.account.link.back');
            if ($block) {
                $block->setRefererUrl($this->_redirect->getRefererUrl());
            }
            return $resultPage;
        } catch(\Magento\Framework\Exception\NoSuchEntityException $e) {
            /** @var \Magento\Framework\Controller\Result\Forward $resultForward */
            $resultForward = $this->resultFactory->create(ResultFactory::TYPE_FORWARD);
            return $resultForward->forward('noroute');
        }
	}
    /**
     * @param \Magento\Framework\Controller\ResultInterface $resultPage
     * @return void
     */
    protected function initPage(\Magento\Framework\Controller\ResultInterface $resultPage)
    {
        if ($navigationBlock = $resultPage->getLayout()->getBlock('customer_account_navigation')) {
            $navigationBlock->setActive('couponcode/customer/index');
        }
    }
}
