<?php
/**
 * Landofcoder
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Landofcoder.com license that is
 * available through the world-wide-web at this URL:
 * http://landofcoder.com/license
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category   Landofcoder
 * @package    Lof_CouponCode
 * @copyright  Copyright (c) 2017 Landofcoder (http://www.landofcoder.com/)
 * @license    http://www.landofcoder.com/LICENSE-1.0.html
 **/
namespace Lof\CouponCode\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\App\ObjectManager;

class ObserverforCheckCoupon implements ObserverInterface
{

    protected $request;
    protected $ruleFactory;
    protected $_couponHelper;
    protected $_customerSession;

    public function __construct(
        \Magento\SalesRule\Model\RuleFactory $ruleFactory,
        \Lof\CouponCode\Helper\Data $couponHelper,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\App\Request\Http $request
    ) {
        $this->request = $request;
        $this->rulesFactory = $ruleFactory;
        $this->_couponHelper = $couponHelper;
        $this->_customerSession = $customerSession;
    }

    /**
     * At the email template , you can get this custom variables free-coupon using  {{var coupongenerator.generateCoupon(ruleId|rule_key, default_code)}}
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        //How to check coupon
        // var_dump(($observer->getQuote()->getCouponCode())); die();

    }

}