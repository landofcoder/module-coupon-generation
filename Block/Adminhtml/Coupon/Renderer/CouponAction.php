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
 * @copyright  Copyright (c) 2019 Landofcoder (http://www.landofcoder.com/)
 * @license    http://www.landofcoder.com/LICENSE-1.0.html
 */
namespace Lof\CouponCode\Block\Adminhtml\Coupon\Renderer;
use Magento\Framework\UrlInterface;

class CouponAction extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Text
{

	/**
	 * @var Magento\Framework\UrlInterface
	 */
	protected $_urlBuilder;

	/**
	 * @param \Magento\Backend\Block\Context
	 * @param UrlInterface
	 */
	public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Framework\Url $urlBuilder
        ){
		$this->_urlBuilder = $urlBuilder;
        parent::__construct($context);
	}

	public function _getValue(\Magento\Framework\DataObject $row){
		$editUrl = $this->_urlBuilder->getUrl(
                                'couponcode/coupon/edit',
                                [
                                    'couponcode_id' => $row['couponcode_id']
                                ]
                            );
		return sprintf("<a target='_blank' href='%s'>Edit</a>", $editUrl);
	}
}