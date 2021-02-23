<?php
namespace Lof\CouponCode\Block;
class Trackinfo extends \Magento\Framework\View\Element\Template
{
     protected $_couponData;
     protected $orderRepository;
     protected $_coreRegistry;
     protected $_priceCurrency;

     public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Lof\CouponCode\Helper\Data $_couponData,
        \Magento\Sales\Api\Data\OrderInterface $orderRepository,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Store\Model\StoreManagerInterface $priceCurrency,
        array $data = []
     ) {
        $this->_couponData = $_couponData;
        $this->orderRepository = $orderRepository;
        $this->_coreRegistry = $coreRegistry;
        $this->_priceCurrency = $priceCurrency;
        parent::__construct($context, $data);
    }
    protected function _toHtml(){
        if(!$this->_couponData->getConfig('general_settings/show')) return;
        $coupon_log = $this->_coreRegistry->registry('lofcouponcode_log');
        $sales_order_info = null;
        $currencyCode = $this->_priceCurrency->getStore()->getCurrentCurrencyCode();
        if($coupon_log && $coupon_log->getOrderId()) {
            $sales_order_info = $this->orderRepository->loadByIncrementId($coupon_log->getOrderId());
        }
        $this->assign('coupon_log', $coupon_log);
        $this->assign('order_info', $sales_order_info);
        $this->assign('currencyCode', $currencyCode);
        return parent::_toHtml();
    }
    public function DiscountAmountFormat($couponRuleId, $discount_amount){
        $couponRuleData = $this->_couponData->getCouponRuleData($couponRuleId);
        $simple_action = $couponRuleData->getSimpleAction();
        if($simple_action == 'by_percent') {
            $discount_amount .='%';
        }elseif($simple_action == 'fixed'){
            $discount_amount ='$'.$discount_amount;
        }
        return $discount_amount;
    }
}
?>