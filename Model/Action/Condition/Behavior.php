<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Catalog Rule Product Condition data model
 */
namespace Lof\CouponCode\Model\Action\Condition;

/**
 * @method string getAttribute() Returns attribute code
 */
class Behavior extends \Magento\Rule\Model\Condition\AbstractCondition
{
	const OPTION_TOTAL_ORDER          = 'total_orders';
	const OPTION_TOTAL_SALES          = 'total_sales';
	const OPTION_ORDERS_NUMBER        = 'orders_num';
	const OPTION_REVIEWS_NUMBER       = 'reviews_num';

    /**
     * @param Context $context
     * @param array $data
     */
    public function __construct(
       \Magento\Rule\Model\Condition\Context $context,
       \Magento\Directory\Model\Config\Source\Country $country,
       \Magento\Customer\Block\Widget\Gender $gender,
    	array $data = []
    ) {
    	parent::__construct($context, $data);
        $this->_country                 = $country;
        $this->_gender                  = $gender;
    }

    public function loadCustomerReOptions(){
    	$attributes = [
			self::OPTION_TOTAL_SALES    => __('Total Sales'),
			self::OPTION_ORDERS_NUMBER  => __('Total Orders'),
			self::OPTION_REVIEWS_NUMBER => __('Total Reviews')
    	];
    	$this->setAttributeOption($attributes);
    	return $this;
    }

    /**
     * @return $this
     */
    public function loadAttributeOptions()
    {
    	$attributes = [
            self::OPTION_TOTAL_SALES    => __('Total Sales'),
            self::OPTION_ORDERS_NUMBER  => __('Total Orders'),
            self::OPTION_REVIEWS_NUMBER => __('Total Reviews')
        ];
        $this->setAttributeOption($attributes);
    	return $this;
    }

    public function getInputType()
    {
    	$type = 'string';
    	// switch ($this->getAttribute()) {
    	// 	case self::CUSTOMER_BILLING_COUNTRY_ID:
    	// 	case self::CUSTOMER_GENDER:
    	// 		$type = 'select';
    	// 		break;

    	// 	default:
    	// 		$type = 'string';
    	// 		break;
    	// }

    	return $type;
    }

    /**
     * Retrieve value element type
     *
     * @return string
     */
    public function getValueElementType()
    {
    	$type = 'text';
    	// switch ($this->getAttribute()) {
    	// 	case self::CUSTOMER_BILLING_COUNTRY_ID:
    	// 	case self::CUSTOMER_GENDER:
    	// 		$type = 'select';
    	// 		break;

    	// 	default:
    	// 		$type = 'text';
    	// 		break;
    	// }

    	return $type;
    }

    /**
     * Retrieve value by option.
     *
     * @param string $option
     *
     * @return string
     */
    public function getValueOption($option = null)
    {
        $this->_prepareValueOptions();

        return $this->getData('value_option'.($option !== null ? '/'.$option : ''));
    }

    protected function _prepareValueOptions()
    {
        // $selectOptions = [];
        // if ($this->getAttribute() === self::CUSTOMER_BILLING_COUNTRY_ID) {
        // 	$selectOptions = $this->_country->toOptionArray(true);
        // }
        // if ($this->getAttribute() === self::CUSTOMER_GENDER) {
        // 	$genderOptions = $this->_gender->getGenderOptions();

        // 	foreach ($genderOptions as $k => $v) {
        // 		$selectOptions[] = [
        // 			'value' => $v->getValue(),
        // 			'label' => $v->getLabel()
        // 		];
        // 	}
        // }

        // $this->setData('value_select_options', $selectOptions);

        // $hashedOptions = [];
        // foreach ($selectOptions as $o) {
        //     $hashedOptions[$o['value']] = $o['label'];
        // }
        // $this->setData('value_option', $hashedOptions);

        return $this;
    }
}