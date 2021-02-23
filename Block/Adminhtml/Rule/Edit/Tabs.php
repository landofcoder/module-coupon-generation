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

namespace Lof\CouponCode\Block\Adminhtml\Rule\Edit;

class Tabs extends \Magento\Backend\Block\Widget\Tabs
{

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @param \Magento\Framework\Registry              $registry
     * @param \Magento\Backend\Block\Widget\Context    $context
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Backend\Model\Auth\Session      $authSession
     * @param array                                    $data
     */
    public function __construct(
        \Magento\Framework\Registry $registry,
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Backend\Model\Auth\Session $authSession,
        array $data = []
    ) {
        $this->registry = $registry;
        parent::__construct($context, $jsonEncoder, $authSession, $data);
    }

    protected function _construct()
    {
        parent::_construct();
        $this->setId('form_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Rule Information'));
    }

    protected function _prepareLayout()
    {
    	$rule = $this->registry->registry('lofcouponcode_rule');

    	$this->addTab(
                'general',
                [
                    'label' => __('Rule Information'),
                    'content' => $this->getLayout()->createBlock('Lof\CouponCode\Block\Adminhtml\Rule\Edit\Tab\Main')->toHtml()
                ]
            );

        $this->addTab(
            'coupon_usage',
            [
                'label' => __('Coupon Usage'),
                'content' => $this->getLayout()->createBlock('Lof\CouponCode\Block\Adminhtml\Rule\Edit\Tab\CouponInfo')->toHtml()
            ]
        );
        $this->addTab(
            'conditions',
            [
                'label' => __('Conditions'),
                'content' => $this->getLayout()->createBlock('Lof\CouponCode\Block\Adminhtml\Rule\Edit\Tab\Conditions')->toHtml()
            ]
        );
        $this->addTab(
            'actions',
            [
                'label' => __('Actions'),
                'content' => $this->getLayout()->createBlock('Lof\CouponCode\Block\Adminhtml\Rule\Edit\Tab\Actions')->toHtml()
            ]
        );
    }
}
