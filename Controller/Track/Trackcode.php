<?php
/**
 * Landofcoder
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the landofcoder.com license that is
 * available through the world-wide-web at this URL:
 * https://landofcoder.com/license
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category   Landofcoder
 * @package    Lof_CouponCode
 * @copyright  Copyright (c) 2017 Landofcoder (https://www.landofcoder.com/)
 * @license    https://www.landofcoder.com/LICENSE-1.0.html
 */
namespace Lof\CouponCode\Controller\Track;

use Magento\Framework\App\Action\Action;
use Magento\CatalogSearch\Helper\Data;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Search\Model\QueryFactory;
use Magento\Catalog\Model\Layer\Resolver;
use \Magento\Catalog\Model\Layer\Resolver as LayerResolver;
use Magento\Catalog\Model\Layer\Filter\DataProvider\Category as CategoryDataProvider;
use Magento\Framework\Controller\ResultFactory;
use \Magento\Search\Helper\Data as SearchHelper;

class Trackcode extends Action
{
    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;
	protected $_resultPageFactory;
	protected $_customerSession;
    protected $_coreRegistry;
    protected $_couponHelper;

    /**
     * @var \Magento\Framework\Controller\Result\ForwardFactory
     */
    protected $resultForwardFactory;

	public function __construct(
		\Magento\Framework\App\Action\Context $context,
		\Magento\Framework\View\Result\PageFactory $resultPageFactory,
		StoreManagerInterface $storeManager,
		\Lof\CouponCode\Helper\Data $helper,
		\Magento\Customer\Model\Session $customerSession,
		\Magento\Framework\Controller\Result\ForwardFactory $resultForwardFactory,
		\Magento\Framework\Registry $coreRegistry
		){
		$this->_resultPageFactory  = $resultPageFactory;
		$this->_storeManager       = $storeManager;
		$this->_couponHelper       = $helper;
		$this->_customerSession = $customerSession;
		$this->_coreRegistry = $coreRegistry;
		$this->resultForwardFactory = $resultForwardFactory;
		parent::__construct($context);
	}

	/*
	* Example Url:
	*/
	public function execute()
	{
		$resultPage = $this->_resultPageFactory->create();
		if(!$this->_couponHelper->getConfig('general_settings/show') || !$this->_couponHelper->getConfig('general_settings/allow_track_log')) {
            $resultForward = $this->_resultPageFactory->create();
            return $resultForward->forward('defaultnoroute');
        }

		$resultPage->getConfig()->getTitle()->set(__('Sales Info'));
		$coupon_code = $this->getRequest()->getParam('coupon_code');
		$customer_email = $this->getRequest()->getParam('email');
		$order_id = $this->getRequest()->getParam('order_id');

		$coupon_code = trim($coupon_code);
		$customer_email = trim($customer_email);
		if($coupon_code && $customer_email) {
			$collection = $this->_objectManager->create('Lof\CouponCode\Model\Log')->getCollection();
			$collection = $collection->addFieldToFilter("coupon_code", $coupon_code)
									->addFieldToFilter("email_address", $customer_email);

			if($order_id) {
				$collection->addFieldToFilter("order_id", $order_id);
			}

			if(0 < $collection->getSize()){
				$this->_coreRegistry->register('lofcouponcode_log', $collection->getFirstItem());
			}
		}
        return $resultPage;
	}
}
