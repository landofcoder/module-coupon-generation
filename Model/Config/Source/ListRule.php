<?php
/**
 * Venustheme
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
 * @category   Venustheme
 * @package    Ves_Testimonial
 * @copyright  Copyright (c) 2016 Landofcoder (http://www.venustheme.com/)
 * @license    http://www.venustheme.com/LICENSE-1.0.html
 */
namespace Lof\CouponCode\Model\Config\Source;
 
class ListRule implements \Magento\Framework\Option\ArrayInterface
{
	protected  $_couponHelper;

    /**
     * @param \Lof\CouponCode\Helper\Data $couponHelper
     */
    public function __construct(
    	\Lof\CouponCode\Helper\Data $couponHelper
    	) {
    	$this->_couponHelper = $couponHelper;
    }
    public function toOptionArray()
    {
        $collection = $this->_couponHelper->getAllRule();
    	$rules = array();
    	foreach ($collection as $key=>$val) {
    		$rules[] = [
    		'value' => $key,
    		'label' => addslashes($val)
    		];
    	}
        array_unshift($rules, array(
                'value' => '',
                'label' => __('-- Please Select A Rule --'),
                ));
        return $rules;
    }
}