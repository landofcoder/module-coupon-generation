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

use Lof\CouponCode\Helper\Data;
use Magento\Backend\App\Action\Context;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory;
use Magento\Eav\Model\Entity\Collection\AbstractCollection;
use Magento\Framework\Controller\ResultFactory;
use Magento\Ui\Component\MassAction\Filter;

/**
 * Class MassAssignGroup
 */
class MassGenerate extends AbstractMassAction
{
    const EMAILIDENTIFIER = 'sent_mail_with_customer';
    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    protected $_helper;

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

    /**
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param CustomerRepositoryInterface $customerRepository
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        CustomerRepositoryInterface $customerRepository,
        Data $helper,
        \Magento\SalesRule\Model\CouponFactory $couponFactory,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        \Magento\Framework\Stdlib\DateTime\DateTime $date
    ) {
        parent::__construct($context, $filter, $collectionFactory);
        $this->customerRepository = $customerRepository;
        $this->_helper = $helper;
        $this->couponFactory = $couponFactory;
        $this->dateTime = $dateTime;
        $this->date = $date;
    }

    /**
     * Customer mass assign group action
     *
     * @param AbstractCollection $collection
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    protected function massAction(AbstractCollection $collection)
    {
        $couponGenerate = 0;

        $couponRuleId = $this->getRequest()->getParam('rule');
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        if ($couponRuleId) {
            try {
                $coupon = $this->couponFactory->create();
                $couponRuleData = $this->_helper->getCouponRuleData($couponRuleId);
                $ruleId = (int)$couponRuleData->getRuleId();
                if ($ruleId) {
                    $limit_time_generated_coupon = (int)$couponRuleData->getLimitGenerated();

                    $couponsGeneratedOld =$couponRuleData->getCouponsGenerated();
                    $nowTimestamp = $this->dateTime->formatDate($this->date->gmtTimestamp());
                    $expirationDate = $couponRuleData->getToDate();
                    if ($expirationDate && !($expirationDate instanceof \DateTime)) {
                        $expirationDate = \DateTime::createFromFormat('Y-m-d', $expirationDate);
                    }
                    if ($expirationDate instanceof \DateTime) {
                        $expirationDate = $expirationDate->format('Y-m-d H:i:s');
                    }
                    $_lofCoupon = $this->_objectManager->create('Lof\CouponCode\Model\Coupon');
                    $emailFrom = $this->_helper->getConfig('general_settings/sender_email_identity');
                    $emailidentifier = self::EMAILIDENTIFIER;
                    $queue_mail_array = [];
                    foreach ($collection->getAllIds() as $customerId) {
                        $coupon_code = $this->_helper->generateCode($couponRuleId);
                        // echo $coupon_code;

                        $customer = $this->customerRepository->getById($customerId);
                        $customerEmail = $customer->getEmail();

                        //number coupons was generated for same email address
                        $coupon_collection = $this->_objectManager->create('Lof\CouponCode\Model\Coupon')->getCollection();
                        $number_generated_coupon = (int)$coupon_collection->getTotalByEmail($customerEmail, $ruleId);
                        if ($limit_time_generated_coupon > 0 && $number_generated_coupon >= $limit_time_generated_coupon) {
                            continue;
                        }
                        //-------------------
                        $customerName =$customer->getFirstname() . " " . $customer->getLastname();
                        //-------------------

                        $coupon->setId(null)
                                ->setRuleId($ruleId)
                                ->setExpriationDate($expirationDate)
                                ->setCreatedAt($nowTimestamp)
                                ->setType(1)
                                ->setCustomerId((int)$customerId)
                                ->setCode($coupon_code)
                                ->save();

                        if ($coupon->getId()) {
                            $_lofCoupon->setId(null)
                                    ->setRuleId($ruleId)
                                    ->setCouponId($coupon->getId())
                                    ->setCode($coupon_code)
                                    ->setEmail($customerEmail)
                                    ->setCustomerId((int)$customerId)
                                    ->save();
                            $simple_action = $couponRuleData->getSimpleAction();
                            $discount_amount_formatted = $couponRuleData->getDiscountAmount();
                            if ($simple_action == 'by_percent') {
                                $discount_amount_formatted .='%';
                            } elseif ($simple_action == 'fixed') {
                                $discount_amount_formatted ='$' . $discount_amount_formatted;
                            }
                            $templateVar = [
                                    'customer_name' => $customerName,
                                    'coupon_code' => $coupon_code,
                                    'rule_title' => $couponRuleData->getName(),
                                    'from_date' => $couponRuleData->getFromDate(),
                                    'to_date' => $couponRuleData->getToDate(),
                                    'simple_action' => $couponRuleData->getSimpleAction(),
                                    'discount_amount' => $couponRuleData->getDiscountAmount(),
                                    'discount_amount_formatted' => $discount_amount_formatted,
                                    'link_website' => $this->_helper->getBaseUrl()
                                ];

                            $queue_mail_array[] = ['email_from' => $emailFrom, 'email_to' =>$customerEmail, 'identifier' => $emailidentifier, 'template_vars' =>$templateVar];
                            $couponGenerate++;
                        }
                    }
                    if ($couponGenerate) {
                        $couponGenerateNew = $couponGenerate + $couponsGeneratedOld;
                        $couponRuleData->setData('coupons_generated', $couponGenerateNew)->save();
                        $this->messageManager->addSuccess(__('A total of %1 record(s) were updated.', $couponGenerate));
                    }
                    $allow_send_email = $this->_helper->getConfig('general_settings/send_email_coupon');
                    if ($allow_send_email && $queue_mail_array) {
                        foreach ($queue_mail_array as $email_item) {
                            $this->_helper->sendMail($email_item['email_from'], $email_item['email_to'], $email_item['identifier'], $email_item['template_vars']);
                        }
                    }
                } else {
                    $this->messageManager->addError(
                        __('Not found coupon rule to generate code. Please review the error log.')
                    );
                }
            } catch (\Exception $e) {
                $this->messageManager->addError(
                    __('Something went wrong while generate coupon code. Please review the error log.')
                );
                $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
                $resultRedirect->setPath('*/*/index');
            }
        }
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect->setPath('*/*/index');

        return $resultRedirect;
    }
    /**
    * Check the permission to run it
    *
    * @return boolean
    */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Lof_CouponCode::generate');
    }
}
