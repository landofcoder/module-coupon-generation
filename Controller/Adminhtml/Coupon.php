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
namespace Lof\CouponCode\Controller\Adminhtml;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;

/**
 * Cms manage blocks controller
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
abstract class Coupon extends \Magento\Backend\App\Action
{ 
 

    const ADMIN_RESOURCE = 'Lof_CouponCode::coupon';


      /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
      protected $_coreRegistry;
    /**
     * @param \Magento\Backend\App\Action\Context              $context             
     * @param \Magento\Framework\Registry                      $coreRegistry        

     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry
        ) {
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context);


    } 
    /**
     * Init page
     *
     * @param \Magento\Backend\Model\View\Result\Page $resultPage
     * @return \Magento\Backend\Model\View\Result\Page
     */
    protected function initPage($resultPage)
    {
        $resultPage->setActiveMenu('Lof_CouponCode::coupon')
        ->addBreadcrumb(__('Coupon Code'), __('Coupon Code'))
        ->addBreadcrumb(__('Coupon Code'), __('Coupon Code'));
        return $resultPage;
    }
    /**
     * Check the permission to run it
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Lof_CouponCode::coupon');
    }

}
