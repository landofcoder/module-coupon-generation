<?php
/**
 * Venustheme
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Venustheme.com license that is
 * available through the world-wide-web at this URL:
 * http://www.venustheme.com/license-agreement.html
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category   Venustheme
 * @package    Ves_ImageSlider
 * @copyright  Copyright (c) 2014 Venustheme (http://www.venustheme.com/)
 * @license    http://www.venustheme.com/LICENSE-1.0.html
 */
namespace Lof\CouponCode\Block\Widget;
use Magento\Framework\App\Http\Context;
class Coupon extends \Magento\Framework\View\Element\Template implements \Magento\Widget\Block\BlockInterface
{
	protected $_blockModel;
	protected $_dataFilterHelper;
	protected $_dataGenerator;
	protected $_modelRule;
	protected $_customerSession;
	protected $_order_success_page;
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;
     /**
     * @var \Magento\Sales\Model\Order\Config
     */
    protected $_orderConfig;

    protected $httpContext;


	public function __construct(
		\Magento\Framework\View\Element\Template\Context $context,
		 \Magento\Framework\App\Http\Context $httpContext,
		\Lof\CouponCode\Helper\Data $dataHelper,
		\Lof\CouponCode\Helper\Generator $dataGenerator,
		\Lof\CouponCode\Model\Rule $modelRule,
		\Magento\Cms\Model\Block $blockModel,
		 \Magento\Sales\Model\Order\Config $orderConfig,
		\Magento\Customer\Model\Session $customerSession,
		\Magento\Checkout\Model\Session $checkoutSession,
		array $data = []
		) {
		parent::__construct($context, $data);
		$this->_layout = $context->getLayout();
		$this->httpContext = $httpContext;
		$this->_blockModel = $blockModel;
		$this->_dataFilterHelper = $dataHelper;
		$this->_modelRule = $modelRule;
		$this->_dataGenerator = $dataGenerator;
		$this->_customerSession = $customerSession;
		$this->_checkoutSession = $checkoutSession;
		$this->_order_success_page = false;
		$this->_orderConfig = $orderConfig;
		$this->setTemplate('widgets/coupon_form.phtml');
	}

	protected function _toHtml(){
		if(!$this->getDataFilterHelper()->getConfig('general_settings/show')) return;
		if(!$this->getDataFilterHelper()->getConfig('general_settings/allow_redeem')) return;

		$show_only_customer = $this->getConfig("show_only_customer");
		$show_only_customer = $show_only_customer?$show_only_customer:$this->getDataFilterHelper()->getConfig('general_settings/redeem_only_customer');

		if($show_only_customer && !$this->_customerSession->isLoggedIn()){
			return;
		}
		$block_id = $this->getConfig("cms_id");
		$title = $this->getConfig("title");
		$addition_cls = $this->getConfig("addition_cls");
		$heading_class = $this->getConfig("heading_class");
		$pretext_class = $this->getConfig("pretext_class");
		$rule_id = $this->getConfig("rule_id");
		$show_email = $this->getConfig("show_email");
		$show_name = $this->getConfig("show_name");
		$show_qrcode = $this->getConfig("show_qrcode");
		$show_barcode = $this->getConfig("show_barcode");
		$show_button = $this->getConfig("show_button");
		$get_current_order_info = true;
		if(!$this->hasData("order_id")){
			$get_current_order_info = false;
		}
		$width = $this->getConfig("width");
		$height = $this->getConfig("height");
		$width = $width?$width:200;
		$height = $height?$height:200;
		$cms_block_html = "";

		if($block_id) {
			$block = $this->_blockModel->load($block_id);
			// $block_id = 17;
			$cms_block_html = isset($block)? $this->_dataFilterHelper->filter($block->getContent()) : '';
		}
		// var_dump($cms_block_html);die;
		$this->assign('cms_block_html', $cms_block_html);
		$this->assign('title', $title);
		$this->assign('addition_cls', $addition_cls);
		$this->assign('heading_class', $heading_class);
		$this->assign('pretext_class', $pretext_class);
		$this->assign('rule_id', $rule_id);
		$this->assign('show_email', $show_email);
		$this->assign('show_name', $show_name);
		$this->assign('show_qrcode', $show_qrcode);
		$this->assign('show_barcode', $show_barcode);
		$this->assign('width', $width);
		$this->assign('height', $height);
		$this->assign('show_button', $show_button);
		$this->assign('get_current_order_info', $get_current_order_info);
		return parent::_toHtml();
	}
	public function getCustomerInfo() {
		$info = [];
		if($this->_customerSession->isLoggedIn()) {
			$customer = $this->_customerSession->getCustomer();
			$info['email'] = $customer->getEmail();
			$info['fullname'] = $customer->getName();
			$info['customer_id'] = $customer->getId();
		}
		return $info;
	}
	public function getConfig($key, $default = NULL){
		if($this->hasData($key)){
			return $this->getData($key);
		}
		return $default;
	}
	public function getDataFilterHelper() {
		return $this->_dataFilterHelper;
	}
	public function getLayout() {
		return $this->_layout;
	}
	public function getBaseUrl() {
		$_objectManager = \Magento\Framework\App\ObjectManager::getInstance(); //instance of\Magento\Framework\App\ObjectManager
		$storeManager = $_objectManager->get('Magento\Store\Model\StoreManagerInterface');
		$currentStore = $storeManager->getStore();

		return $currentStore->getBaseUrl();
	}
	public function getRedeemLink() {
		$base_url = $this->getBaseUrl();
		$route = $this->getDataFilterHelper()->getConfig("general_settings/route");
		if(!$route){
			$route = "couponcode/redeem/generate";
		}
		return $base_url.$route;
	}

	public function getTrackLink(){
		$base_url = $this->getBaseUrl();
		$route = $this->getDataFilterHelper()->getConfig("general_settings/track_route");
		if(!$route){
			$route = "couponcode/track/trackcode";
		}
		return $base_url.$route;
	}

	/**
     * Initialize data and prepare it for output
     *
     * @return string
     */
    protected function _beforeToHtml()
    {
        $this->prepareBlockData();
        return parent::_beforeToHtml();
    }

    /**
     * Prepares block data
     *
     * @return void
     */
    protected function prepareBlockData()
    {
        $order = $this->_checkoutSession->getLastRealOrder();

        if($order) {
        	$this->_order_success_page = true;
	        $this->addData(
	            [
	                'is_order_visible' => $this->isVisible($order),
	                'view_order_url' => $this->getUrl(
	                    'sales/order/view/',
	                    ['order_id' => $order->getEntityId()]
	                ),
	                'print_url' => $this->getUrl(
	                    'sales/order/print',
	                    ['order_id' => $order->getEntityId()]
	                ),
	                'can_print_order' => $this->isVisible($order),
	                'can_view_order'  => $this->canViewOrder($order),
	                'order_id'  => $order->getIncrementId()
	            ]
	        );
	    }
    }

    /**
     * Is order visible
     *
     * @param Order $order
     * @return bool
     */
    protected function isVisible($order)
    {
        return !in_array(
            $order->getStatus(),
            $this->_orderConfig->getInvisibleOnFrontStatuses()
        );
    }

    /**
     * Can view order
     *
     * @param Order $order
     * @return bool
     */
    protected function canViewOrder($order)
    {
        return $this->httpContext->getValue(\Magento\Customer\Model\Context::CONTEXT_AUTH)
            && $this->isVisible($order);
    }

}