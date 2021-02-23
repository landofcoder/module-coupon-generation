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
namespace Lof\CouponCode\Block\Adminhtml\Rule\Edit\Tab;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Convert\DataObject as ObjectConverter;

class Main extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var \Magento\Store\Model\System\Store
     */
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

    /**
     * [__construct description]
     * @param \Magento\Backend\Block\Template\Context                       $context               
     * @param \Magento\Framework\Registry                                   $registry              
     * @param \Magento\Framework\Data\FormFactory                           $formFactory           
     * @param GroupRepositoryInterface                                      $groupRepository       
     * @param ObjectConverter                                               $objectConverter       
     * @param SearchCriteriaBuilder                                         $searchCriteriaBuilder 
     * @param \Magento\Store\Model\System\Store                             $systemStore           
     * @param \Magento\Email\Model\ResourceModel\Template\CollectionFactory $templatesFactory      
     * @param \Magento\Email\Model\Template\Config                          $emailConfig           
     * @param array                                                         $data                  
     */
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
        // \Lof\FollowUpEmail\Model\Config\Source\EventType $eventtype,
        // \Lof\FollowUpEmail\Model\Config\Source\Hours $hours,
        // \Lof\FollowUpEmail\Model\Config\Source\Minutes $minutes,
        array $data = []
        ) {
        $this->_wysiwygConfig = $wysiwygConfig; 
        $this->_systemStore = $systemStore;
        $this->groupRepository = $groupRepository;
        $this->_objectConverter = $objectConverter;
        $this->_searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->_templatesFactory = $templatesFactory;
        $this->_emailConfig = $emailConfig;
        // $this->_hours = $hours;
        // $this->_minutes = $minutes;
        // $this->_eventtype = $eventtype;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    protected function _prepareForm()
    { 
        $model = $this->_coreRegistry->registry('lofcouponcode_rule'); 
        if ($this->_isAllowedAction('Lof_CouponCode::rule_edit')) {
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
        $form->setHtmlIdPrefix('rule_');
        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('General Information')]);
        if ($model->getId()) {
            $fieldset->addField('coupon_rule_id', 'hidden', ['name' => 'coupon_rule_id']);
        }
        $fieldset->addField('product_ids', 'hidden', ['name' => 'product_ids']); 
        
        $fieldset->addField(
            'name',
            'text',
            ['name' => 'name', 
            'label' => __('Rule Name'), 
            'title' => __('Rule Name'), 
            'required' => true,
            'disabled' => $isElementDisabled
            ]
        );

        $fieldset->addField(
            'rule_key',
            'text',
            ['name' => 'rule_key', 
            'label' => __('Rule Identifier Key'), 
            'title' => __('Rule Identifier Key'), 
            'required' => true,
            'disabled' => $isElementDisabled
            ]
        );

        $fieldset->addField(
            'description',
            'textarea',
            [
                'name' => 'description',
                'label' => __('Description'),
                'title' => __('Description'),
                'style' => 'height: 100px;',
                'disabled' => $isElementDisabled
            ]
        );
        
        /**
         * Check is single store mode
         */
        if ($this->_storeManager->isSingleStoreMode()) {
            $websiteId = $this->_storeManager->getStore(true)->getWebsiteId();
            $fieldset->addField('website_ids', 'hidden', ['name' => 'website_ids[]', 'value' => $websiteId]);
            $model->setWebsiteIds($websiteId);
        } else {
            $field = $fieldset->addField(
                'website_ids',
                'multiselect',
                [
                    'name' => 'website_ids[]',
                    'label' => __('Websites'),
                    'title' => __('Websites'),
                    'required' => true,
                    'values' => $this->_systemStore->getWebsiteValuesForForm()
                ]
            );
            $renderer = $this->getLayout()->createBlock(
                'Magento\Backend\Block\Store\Switcher\Form\Renderer\Fieldset\Element'
            );
            $field->setRenderer($renderer);
        }
        
        $groups = $this->groupRepository->getList($this->_searchCriteriaBuilder->create())
            ->getItems();
        $fieldset->addField(
            'customer_group_ids',
            'multiselect',
            [
                'name' => 'customer_group_ids[]',
                'label' => __('Customer Groups'),
                'title' => __('Customer Groups'),
                'required' => true,
                'values' =>  $this->_objectConverter->toOptionArray($groups, 'id', 'code'),
                'disabled' => $isElementDisabled
            ]
        );
        $dateFormat = $this->_localeDate->getDateFormat(\IntlDateFormatter::SHORT);
        $fieldset->addField(
            'from_date',
            'date',
            [
                'name' => 'from_date',
                'label' => __('From'),
                'title' => __('From'),
                'input_format' => \Magento\Framework\Stdlib\DateTime::DATE_INTERNAL_FORMAT,
                'date_format' => $dateFormat,
                'disabled' => $isElementDisabled
            ]
        );
        $fieldset->addField( 
            'to_date',
            'date',
            [
                'name' => 'to_date',
                'label' => __('To'),
                'title' => __('To'),
                'input_format' => \Magento\Framework\Stdlib\DateTime::DATE_INTERNAL_FORMAT,
                'date_format' => $dateFormat,
                'disabled' => $isElementDisabled
            ]
        );
        $fieldset->addField(
            'uses_per_customer',
            'text',
            ['name' => 'uses_per_customer', 
            'label' => __('Uses Per Customer'), 
            'title' => __('Uses Per Customer'), 
            'required' => false,
            'disabled' => $isElementDisabled
            ]
        );
        $fieldset->addField(
            'uses_per_coupon',
            'text',
            ['name' => 'uses_per_coupon', 
            'label' => __('Uses per Coupon'), 
            'title' => __('Uses per Coupon'), 
            'required' => false,
            'disabled' => $isElementDisabled
            ]
        );

        $fieldset->addField('sort_order', 'text', ['name' => 'sort_order', 'label' => __('Priority')]);

        $fieldset->addField(
            'is_rss',
            'select',
            [
                'label' => __('Public In RSS Feed'),
                'title' => __('Public In RSS Feed'),
                'name' => 'is_rss',
                'options' => ['1' => __('Yes'), '0' => __('No')],
                'disabled' => $isElementDisabled
            ]
        );


        $fieldset->addField(
            'is_active',
            'select',
            [
                'label'    => __('Status'),
                'title'    => __('Coupon Code Status'),
                'name'     => 'is_active',
                'options'  => $model->getAvailableStatuses(),
                'disabled' => $isElementDisabled
            ]
        );
        if (!$model->getId()) {
            $model->setData('is_active', $isElementDisabled ? '0' : '1');
            $model->setIsRss(1);
        }

        $fieldset->addField(
            'limit_generated',
            'text',
            ['name' => 'limit_generated', 
            'label' => __('Limit Number Coupons Was Generated Per Email'), 
            'title' => __('Limit Number Coupons Was Generated Per Email'), 
            'required' => false, 
            'note' => __('Limit number coupons can generated for a email address.'),
            'disabled' => $isElementDisabled
            ]
        );

        $fieldset->addField(
            'is_check_email',
            'select',
            ['name' => 'is_check_email', 
            'label' => __('Enable check email address when use coupon code'), 
            'title' => __('Enable check email address when use coupon code'), 
            'required' => false, 
            'note' => __('Enable/disable check exactly email address when apply coupon code on shopping cart and checkout page. If not equal, the coupon code can not apply.'),
            'options' => ['1' => __('Yes'), 
                        '0' => __('No')],
            'disabled' => $isElementDisabled
            ]
        );

        $fieldset->addField(
            'coupons_generated',
            'text',
            ['name' => 'coupons_generated', 
            'label' => __('Coupons Generated'), 
            'title' => __('Coupons Generated'), 
            'required' => false,
            'disabled' => $isElementDisabled
            ]
        );

        // if($event_type){
        //     $model->setData('event_type',$event_type);
        // } 
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
