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
class Customer extends \Magento\Rule\Model\Condition\AbstractCondition
{

	const CUSTOMER_NAME               = 'name';
	const CUSTOMER_EMAIL              = 'email';
	const CUSTOMER_BILLING_TELEPHONE  = 'billing_telephone';
	const CUSTOMER_BILLING_POSTCODE   = 'billing_postcode';
	const CUSTOMER_BILLING_COUNTRY_ID = 'billing_country_id';
	const CUSTOMER_BILLING_REGION     = 'billing_region';
	const CUSTOMER_CREATED_AT         = 'created_at';
	const CUSTOMER_CONFIRMATION       = 'confirmation';
	const CUSTOMER_CREATED_IN         = 'created_in';
	const CUSTOMER_BILLING_FULL       = 'billing_full';
	const CUSTOMER_SHIPPING_FULL      = 'shipping_full';
	const CUSTOMER_DOB                = 'dob';
	const CUSTOMER_DOB_FROM           = 'dob_from';
	const CUSTOMER_DOB_TO             = 'dob_to';
	const CUSTOMER_TAXVAT             = 'taxvat';
	const CUSTOMER_GENDER             = 'gender';
	const CUSTOMER_BILLING_STREET     = 'billing_street';
	const CUSTOMER_BILLING_CITY       = 'billing_city';
	const CUSTOMER_FAX                = 'billing_fax';
	const CUSTOMER_VAT_ID             = 'billing_vat_id';
	const CUSTOMER_COMPANY            = 'billing_company';
	const CUSTOMER_FIRSTNAME          = 'billing_firstname';
	const CUSTOMER_LASTNAME           = 'billing_lastname';

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
    	$this->_country = $country;
    	$this->_gender = $gender;
    }

    /**
     * @return $this
     */
    public function loadAttributeOptions()
    {
    	$attributes = [
	    	self::CUSTOMER_NAME               => __('Name'),
	    	self::CUSTOMER_EMAIL              => __('Email'),
	    	self::CUSTOMER_BILLING_TELEPHONE  => __('Phone'),
	    	self::CUSTOMER_BILLING_POSTCODE   => __('ZIP'),
	    	self::CUSTOMER_BILLING_COUNTRY_ID => __('Country'),
	    	self::CUSTOMER_BILLING_REGION     => __('State/Province'),
	    	self::CUSTOMER_CREATED_AT         => __('Customer Since'),
	    	self::CUSTOMER_CONFIRMATION       => __('Confirmed Email'),
	    	self::CUSTOMER_CREATED_IN         => __('Account Created in'),
	    	self::CUSTOMER_BILLING_FULL       => __('Billing Address'),
	    	self::CUSTOMER_SHIPPING_FULL      => __('Shipping Address'),
	    	self::CUSTOMER_DOB                => __('Date of Birth'),
	    	self::CUSTOMER_DOB_FROM           => __('Date of Birth from'),
	    	self::CUSTOMER_DOB_TO             => __('Date of Birth to'),
	    	self::CUSTOMER_TAXVAT             => __('Tax VAT Number'),
	    	self::CUSTOMER_GENDER             => __('Gender'),
	    	self::CUSTOMER_BILLING_STREET     => __('Street Address'),
	    	self::CUSTOMER_BILLING_CITY       => __('City'),
	    	self::CUSTOMER_FAX                => __('Fax'),
	    	self::CUSTOMER_VAT_ID             => __('VAT Number'),
	    	self::CUSTOMER_COMPANY            => __('Company'),
	    	self::CUSTOMER_FIRSTNAME          => __('Billing Firstname'),
	    	self::CUSTOMER_LASTNAME           => __('Billing Lastname')
    	];
    	$this->setAttributeOption($attributes);
    	return $this;
    }

    public function getInputType()
    {
    	$type = '';
    	switch ($this->getAttribute()) {
    		case self::CUSTOMER_BILLING_COUNTRY_ID:
    		case self::CUSTOMER_GENDER:
    			$type = 'select';
    			break;

    		default:
    			$type = 'string';
    			break;
    	}

    	return $type;
    }

    /**
     * Retrieve value element type
     *
     * @return string
     */
    public function getValueElementType()
    {
    	$type = '';
    	switch ($this->getAttribute()) {
    		case self::CUSTOMER_BILLING_COUNTRY_ID:
    		case self::CUSTOMER_GENDER:
    			$type = 'select';
    			break;

    		default:
    			$type = 'text';
    			break;
    	}

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
        $selectOptions = [];
        if ($this->getAttribute() === self::CUSTOMER_BILLING_COUNTRY_ID) {
        	$selectOptions = $this->_country->toOptionArray(true);
        }
        if ($this->getAttribute() === self::CUSTOMER_GENDER) {
        	$genderOptions = $this->_gender->getGenderOptions();

        	foreach ($genderOptions as $k => $v) {
        		$selectOptions[] = [
        			'value' => $v->getValue(),
        			'label' => $v->getLabel()
        		];
        	}
        }

        $this->setData('value_select_options', $selectOptions);

        $hashedOptions = [];
        foreach ($selectOptions as $o) {
            $hashedOptions[$o['value']] = $o['label'];
        }
        $this->setData('value_option', $hashedOptions);

        return $this;
    }
}