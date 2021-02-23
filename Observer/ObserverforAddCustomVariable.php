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

class ObserverforAddCustomVariable implements ObserverInterface
{

    protected $ruleFactory;
    protected $couponGenerator;

    public function __construct(
        \Magento\SalesRule\Model\RuleFactory $ruleFactory,
        \Lof\CouponCode\Helper\Generator $generator
    ) {
        $this->rulesFactory = $ruleFactory;
        $this->couponGenerator = $generator;
        $this->couponGenerator->initRequireModels();
    }

    /**
     * At the email template , you can get this custom variables free-coupon using  {{var coupongenerator.generateCoupon(ruleId|rule_key, default_code)}}
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var \Magento\Framework\App\Action\Action $controller */
        $transport = $observer->getTransport();
        $customer_email = isset($transport['customer_email'])?$transport['customer_email']:'';
        $customer_name = isset($transport['customer'])?$transport['customer']:'';

        $this->couponGenerator->setCustomerEmail($customer_email);
        $this->couponGenerator->setCustomerName($customer_name);

        $transport['coupongenerator'] = $this->couponGenerator;
    }

}