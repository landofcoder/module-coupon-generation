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
namespace Lof\CouponCode\Controller\Adminhtml\Coupon;

class Save extends \Lof\CouponCode\Controller\Adminhtml\Coupon
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context, $coreRegistry);
    }

    /**
     * Index action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {
            try {
                /** @var $model \Magento\SalesRule\Model\Rule */
                $model = $this->_objectManager->create('Lof\CouponCode\Model\Coupon');
                $model_sale_coupon = $this->_objectManager->create('Magento\SalesRule\Model\Coupon');
                $id = $this->getRequest()->getParam('couponcoupon_id');
                if(!$id) {
                    $id = isset($data['couponcode_id'])?(int)$data['couponcode_id']:0;
                }
                if ($id) {
                    $model->load($id);
                    $sale_coupon_id = $model->getCouponId();
                    $model_sale_coupon->load($sale_coupon_id);
                    if ($id != $model->getId()) {
                        throw new \Magento\Framework\Exception\LocalizedException(__('The wrong coupon is specified.'));
                    }

                    $session = $this->_objectManager->get('Magento\Backend\Model\Session');

                    $usage_limit = isset($data['usage_limit'])?$data['usage_limit']:'';
                    $usage_per_customer = isset($data['usage_per_customer'])?$data['usage_per_customer']:'';
                    $time_used = isset($data['time_used'])?$data['time_used']:'';
                    $expiration_date = isset($data['expiration_date'])?$data['expiration_date']:'';
                    $coupon_code = isset($data['code'])?$data['code']:'';
                    if($coupon_code) {
                        $model_sale_coupon->setCode($coupon_code);
                    }

                    $model_sale_coupon->setUsageLimit($usage_limit);
                    $model_sale_coupon->setUsagePerCustomer($usage_per_customer);
                    $model_sale_coupon->setTimeUsed($time_used);
                    $model_sale_coupon->setExpirationDate($expiration_date);

                    $session->setPageData($model->getData());
                    $model_sale_coupon->save();

                    $model->setData($data);
                    $model->save();

                    $this->messageManager->addSuccess(__('You saved the Coupon.'));
                    $session->setPageData(false);
                    if ($this->getRequest()->getParam('back')) {
                        $this->_redirect('couponcode/*/edit', ['couponcode_id' => $model->getId()]);
                        return;
                    }
                }
                $this->_redirect('couponcode/*/');
                return;

            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
                $id = (int)$this->getRequest()->getParam('couponcode_id');
                if (!empty($id)) {
                 $this->_redirect('couponcode/*/edit', ['couponcode_id' => $id]);
                } else {
                    $this->_redirect('couponcode/*/*/');
                }
                return;
            } catch (\Exception $e) {
                die($e->getMessage());
                $this->messageManager->addError(
                 __('Something went wrong while saving the coupon data. Please review the error log.')
                 );
                $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
                $this->_objectManager->get('Magento\Backend\Model\Session')->setPageData($data);
                $this->_redirect('couponcode/*/edit', ['couponcode_id' => $this->getRequest()->getParam('couponcode_id')]);
            }
        }
        return $resultRedirect->setPath('*/*/');
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Lof_CouponCode::coupon_save');
    }
}
