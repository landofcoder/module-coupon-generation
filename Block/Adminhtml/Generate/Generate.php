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
namespace Lof\CouponCode\Block\Adminhtml\Generate;

/**
 * Adminhtml sales report page content block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Generate extends \Magento\Backend\Block\Widget\Container
{
    /**
     * Template
     *
     * @var string
     */
    protected $_template = 'Lof_CouponCode::coupon/grid/container.phtml';

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_blockGroup = 'Lof_CouponCode';
        $this->_controller = 'adminhtml_generate';
        $this->_headerText = __('Coupon Code Generate');
        parent::_construct();
    }
    /**
     * Get filter URL
     *
     * @return string
     */
    public function getFilterUrl()
    {
        $this->getRequest()->setParam('filter', null);
        return $this->getUrl('*/generate/generate', ['_current' => true]);
    }
}
