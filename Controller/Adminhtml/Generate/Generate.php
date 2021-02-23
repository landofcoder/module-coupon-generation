<?php
/**
 * LandofCoder
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the venustheme.com license that is
 * available through the world-wide-web at this URL:
 * http://venustheme.com/license
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category   LandofCoder
 * @package    Lof_CouponCode
 * @copyright  Copyright (c) 2016 Landofcoder (http://www.landofcoder.com/)
 * @license    http://www.landofcoder.com/LICENSE-1.0.html
 */

namespace Lof\CouponCode\Controller\Adminhtml\Generate;

class Generate extends \Lof\CouponCode\Controller\Adminhtml\Generate
{
    CONST EMAILIDENTIFIER = 'sent_mail_with_customer';
 
    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     */

    protected $_couponHelper;

    /**
     * @var \Magento\SalesRule\Model\CouponFactory
     */
    protected $couponFactory;
    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    protected $dateTime;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;

    protected $customerHelper;

    protected $lofCoupon;

    protected $_urlInterface;
    protected $_storeManager;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Lof\CouponCode\Helper\Data $helper,
        \Magento\SalesRule\Model\CouponFactory $couponFactory,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Customer\Helper\View $customerHelper,
        \Lof\CouponCode\Model\CouponFactory $lofCouponFactory,
        \Magento\Framework\UrlInterface $urlInterface,
        \Magento\Store\Model\StoreManagerInterface $storeManager
        ) {
        $this->_couponHelper = $helper;
        $this->couponFactory = $couponFactory;
        $this->dateTime = $dateTime;
        $this->date = $date;
        $this->customerHelper = $customerHelper;
        $this->lofCoupon = $lofCouponFactory;
        $this->_urlInterface = $urlInterface;
        $this->_storeManager   = $storeManager;
        parent::__construct($context, $coreRegistry); 
    }

    public function execute()
    {
        // $data = $this->getRequest()->getPostValue();  
        $requestData = $this->_objectManager->get(
            'Magento\Backend\Helper\Data'
        )->prepareFilterString(
            $this->getRequest()->getParam('filter')
        );
        
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($requestData && isset($requestData['coupon_rule_id'])) {
            try {
                /** @var $model \Magento\SalesRule\Model\Rule */
                // $ruleModel = $this->_objectManager->create('Lof\CouponCode\Model\Rule');
                // $couponModel = $this->_objectManager->create('Lof\CouponCode\Model\Coupon');
                $couponRuleId = $requestData['coupon_rule_id'];
                $couponRuleData = $this->_couponHelper->getCouponRuleData($couponRuleId);
                $ruleId = (int)$couponRuleData->getRuleId();
                if($ruleId) { 
                    $limit_time_generated_coupon = (int)$couponRuleData->getLimitGenerated();

                    $coupon_collection = $this->_objectManager->create('Lof\CouponCode\Model\Coupon')->getCollection();
                    $number_generated_coupon = (int)$coupon_collection->getTotalByEmail($requestData['email_visitor'], $ruleId);
                    if($limit_time_generated_coupon <= 0 || ($number_generated_coupon < $limit_time_generated_coupon)) {//check number coupons was generated for same email address


                        $coupon = $this->couponFactory->create();

                        $emailFrom = $this->_couponHelper->getConfig('general_settings/sender_email_identity');
                        $emailidentifier = self::EMAILIDENTIFIER;

                        $nowTimestamp = $this->dateTime->formatDate($this->date->gmtTimestamp());
                        $expirationDate = $couponRuleData->getToDate();
                        if ($expirationDate && !($expirationDate instanceof \DateTime)) {
                            $expirationDate = \DateTime::createFromFormat('Y-m-d', $expirationDate);
                        }
                        if($expirationDate instanceof \DateTime) {
                            $expirationDate = $expirationDate->format('Y-m-d H:i:s');
                        }
                        $coupon_code = $this->_couponHelper->generateCode($couponRuleId);

                        $coupon->setId(null)
                            ->setRuleId($ruleId)
                            ->setExpriationDate($expirationDate)
                            ->setCreatedAt($nowTimestamp)
                            ->setType(1)
                            ->setCode($coupon_code)
                            ->save();
                        if($coupon->getId()) {
                            //$_lofCoupon = $this->_objectManager->create('Lof\CouponCode\Model\Coupon');
                            $_lofCoupon = $this->lofCoupon->create();
                            $_lofCoupon->setRuleId($ruleId)
                                ->setCouponId($coupon->getId())
                                ->setCode($coupon_code)
                                ->setEmail($requestData['email_visitor'])
                                ->save();

                            $simple_action = $couponRuleData->getSimpleAction();
                            $discount_amount_formatted = $couponRuleData->getDiscountAmount();
                            if($simple_action == 'by_percent') {
                                $discount_amount_formatted .='%'; 
                            }elseif($simple_action == 'fixed'){
                                $discount_amount_formatted ='$'.$discount_amount_formatted; 
                            }

                            $templateVar = array(
                                'coupon_code' => $coupon_code,
                                'rule_title' => $couponRuleData->getName(),
                                'from_date' => $couponRuleData->getFromDate(),
                                'to_date' => $couponRuleData->getToDate(),
                                'simple_action' => $couponRuleData->getSimpleAction(),
                                'discount_amount' => $couponRuleData->getDiscountAmount(),
                                'discount_amount_formatted' => $discount_amount_formatted,
                                'link_website' => $this->_storeManager->getStore()->getBaseUrl()
                            );
                            
                            $couponsGeneratedOld =$couponRuleData->getCouponsGenerated();
                            $couponGenerateNew = $couponsGeneratedOld + 1;
                            $couponRuleData->setData('coupons_generated', $couponGenerateNew)->save();
                            $emailTo = $requestData['email_visitor'];

                            $allow_send_email = $this->_couponHelper->getConfig('general_settings/send_email_coupon');
                            if($allow_send_email) {
                                $this->_couponHelper->sendMail($emailFrom,$emailTo,$emailidentifier,$templateVar); 
                                $this->messageManager->addSuccess(__('A coupon code has been sent to %1.', $emailTo));
                            } else {
                                $this->messageManager->addSuccess(__('A coupon code has been generated.'));
                            }
                        } else {
                            $this->messageManager->addError(
                             __('Something went wrong while send coupon code. Please review the error log.')
                             );
                        }
                    } else {
                        $this->messageManager->addError(
                             __('The rule limit number coupons were generated for email %1.', $requestData['email_visitor'])
                             );
                    }
                } else {
                    $this->messageManager->addError(
                         __('Did not found the coupon rule.')
                         );
                }
                $this->_redirect('*/*/');
                return;

            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
                $this->_redirect('*/*/');
                return;
            } catch (\Exception $e) {
                $this->messageManager->addError(
                 __('Something went wrong while send coupon code. Please review the error log.')
                 );
                $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
                $this->_objectManager->get('Magento\Backend\Model\Session')->setPageData($requestData);
                $this->_redirect('*/*/');
            }
        }else {
            $this->messageManager->addError(
                __('Please choose a coupon rule to generate.')
                );
        }
        return $resultRedirect->setPath('*/*/');
    }
}