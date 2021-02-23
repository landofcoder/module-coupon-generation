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
namespace Lof\CouponCode\Block\Adminhtml\Coupon\Edit\Tab;
use Lof\CouponCode\Model\Rule;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Convert\DataObject as ObjectConverter;
use Lof\CouponCode\Model\RuleFactory;

class Main extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_ruleFactory;

    protected $_eventtype;

    protected $_hours;

    protected $_minutes;

    protected $_systemStore;
    protected $_wysiwygConfig;
    /**
     * @var GroupRepositoryInterface
     */
    protected $groupRepository; 
    /**
     * @var SearchCriteriaBuilder
     */
    protected $_searchCriteriaBuilder;

    /**
     * @var \Magento\Framework\Convert\DataObject
     */
    protected $_objectConverter;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        GroupRepositoryInterface $groupRepository,
        ObjectConverter $objectConverter,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Store\Model\System\Store $systemStore,
        \Magento\Email\Model\ResourceModel\Template\CollectionFactory $templatesFactory,
        \Magento\Email\Model\Template\Config $emailConfig, 
        \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig,
        RuleFactory $ruleFactory,
        array $data = []
        ) {
        $this->_wysiwygConfig = $wysiwygConfig; 
        $this->_systemStore = $systemStore;
        $this->groupRepository = $groupRepository;
        $this->_objectConverter = $objectConverter;
        $this->_searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->_templatesFactory = $templatesFactory;
        $this->_emailConfig = $emailConfig;
        parent::__construct($context, $registry, $formFactory, $data);
        $this->_ruleFactory = $ruleFactory;
    }

    protected function _prepareForm()
    { 
        $model = $this->_coreRegistry->registry('lofcouponcode_coupon'); 
        if ($this->_isAllowedAction('Lof_CouponCode::coupon_edit')) {
            $isElementDisabled = false;
        } else {
            $isElementDisabled = true;
        }

        $this->_eventManager->dispatch(
        'lof_check_license',
        ['obj' => $this,'ex'=>'Lof_CouponCode']
        );

        if ($this->hasData('is_valid') && $this->hasData('local_valid') && !$this->getData('is_valid') && !$this->getData('local_valid')) {
            $isElementDisabled = true;
            //$wysiwygConfig['enabled'] = $wysiwygConfig['add_variables'] = $wysiwygConfig['add_widgets'] = $wysiwygConfig['add_images'] = 0;
            //$wysiwygConfig['plugins'] = [];

        }

        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('coupon_');
        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('General Information')]);
        if ($model->getId()) {
            $fieldset->addField('couponcode_id', 'hidden', ['name' => 'couponcode_id']);
        }
        $fieldset->addField(
            'alias',
            'text',
            ['name' => 'alias', 
            'label' => __('Coupon Alias'), 
            'title' => __('Coupon Alias'), 
            'required' => false,
            'disabled' => $isElementDisabled
            ]
        );

        $fieldset->addField(
            'code',
            'text',
            ['name' => 'code', 
            'label' => __('Coupon Code'), 
            'title' => __('Coupon Code'), 
            'required' => true,
            'disabled' => $isElementDisabled
            ]
        );

        $fieldset->addField(
            'email',
            'text',
            ['name' => 'email', 
            'label' => __('Email Address'), 
            'title' => __('Email Address'), 
            'required' => false,
            'disabled' => $isElementDisabled
            ]
        );

        $fieldset->addField(
            'usage_limit',
            'text',
            ['name' => 'usage_limit', 
            'label' => __('Usage Limit'), 
            'title' => __('Usage Limit'), 
            'required' => false,
            'disabled' => $isElementDisabled
            ]
        );

        $fieldset->addField(
            'usage_per_customer',
            'text',
            ['name' => 'usage_per_customer', 
            'label' => __('Usage Per Customer'), 
            'title' => __('Usage Per Customer'), 
            'required' => false,
            'disabled' => $isElementDisabled
            ]
        );

        $fieldset->addField(
            'time_used',
            'text',
            ['name' => 'time_used', 
            'label' => __('Time Used'), 
            'title' => __('Time Used'), 
            'required' => false,
            'disabled' => $isElementDisabled
            ]
        );

        $fieldset->addField(
            'expiration_date',
            'text',
            ['name' => 'expiration_date', 
            'label' => __('Expiration Date'), 
            'title' => __('Expiration Date'), 
            'required' => false,
            'disabled' => $isElementDisabled
            ]
        );

        $modelRule = $this->_ruleFactory->create()->getCollection();
        $couponRule = $modelRule->addFieldToFilter('rule_id',$model->getRuleId())->getFirstItem()->getData();
        $getCouponRuleId = $couponRule['coupon_rule_id'];
        $rule_text = '<a href="'.$this->getUrl("couponcode/rule/edit", ['coupon_rule_id'=>$getCouponRuleId]).'" target="_BLANK">'.$model->getRuleId().' ['.__("Edit Rule").']</a>';
        $fieldset->addField(
            'rule_id',
            'note',
            ['name' => 'rule_id', 'label' => __('Rule Id'), 'title' => __('Rule Id'), 'required' => false, 'text' => $rule_text]
        );
        
        if($model->getCustomerId()){
            $customer_text = '<a href="'.$this->getUrl("customer/index/edit", ['id'=>$model->getCustomerId()]).'" target="_BLANK">'.$model->getCustomerId().' ['.__("Edit Customer").']</a>';

            $fieldset->addField(
                'customer_id',
                'note',
                ['name' => 'customer_id', 'label' => __('Customer Id'), 'title' => __('Customer Id'), 'required' => false, 'text' => $customer_text]
            );
        }

        $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Prepare label for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Coupon Information');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Coupon Information');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Check permission for passed action
     *
     * @param string $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }
}
