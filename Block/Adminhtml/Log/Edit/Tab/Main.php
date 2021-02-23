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
namespace Lof\CouponCode\Block\Adminhtml\Log\Edit\Tab;
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
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    protected $_priceCurrency;

    protected $_couponHelper;


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
        \Lof\CouponCode\Helper\Data $couponHelper,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        array $data = []
        ) {
        $this->_wysiwygConfig = $wysiwygConfig; 
        $this->_systemStore = $systemStore;
        $this->groupRepository = $groupRepository;
        $this->_objectConverter = $objectConverter;
        $this->_searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->_templatesFactory = $templatesFactory;
        $this->_emailConfig = $emailConfig;
        $this->_couponHelper = $couponHelper;
        $this->_priceCurrency = $priceCurrency;

        parent::__construct($context, $registry, $formFactory, $data);
    }

    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('lofcouponcode_log');
        if ($this->_isAllowedAction('Lof_CouponCode::log_edit')) {
            $isElementDisabled = false;
        } else {
            $isElementDisabled = true;
        }

        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('log_');
        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('General Information')]);
        if ($model->getId()) {
            $fieldset->addField('log_id', 'hidden', ['name' => 'log_id']);
        }
        $fieldset->addField(
            'coupon_code',
            'note',
            ['name' => 'coupon_code', 'label' => __('Coupon Code'), 'title' => __('Coupon Code'), 'text' => '<strong>'.$model->getCouponCode().'</strong>']
        );

        $fieldset->addField(
            'email_address',
            'note',
            ['name' => 'email_address', 'label' => __('Email Address'), 'title' => __('Email Address'), 'required' => false, 'text' => '<a href="mailto:'.$model->getEmailAddress().'" target="_BLANK">'.$model->getEmailAddress().'</a>']
        );

        $fieldset->addField(
            'full_name',
            'note',
            ['name' => 'full_name', 'label' => __('Full Name'), 'title' => __('Full Name'), 'required' => false, 'text' => $model->getFullName()]
        );
        if($model->getCustomerId()) {
            $customer_text = '<a href="'.$this->getUrl("customer/index/edit", ['id'=>$model->getCustomerId()]).'" target="_BLANK">'.$model->getCustomerId().' ['.__("Edit Customer").']</a>';

            $fieldset->addField(
                'customer_id',
                'note',
                ['name' => 'customer_id', 'label' => __('Customer Id'), 'title' => __('Customer Id'), 'required' => false, 'text' => $customer_text]
            );
        }
        if($model->getOrderId()) {
            $order_text = '<span>#'.$model->getOrderId().'</span> <a href="'.$this->getUrl("sales/order/view", ['order_id'=>$model->getOrderId()]).'" target="_BLANK">['.__("Edit Order").']</a>';
            $fieldset->addField(
                'order_id',
                'note',
                ['name' => 'order_id', 'label' => __('Order Id'), 'title' => __('Order Id'), 'required' => false, 'text' => $order_text]
            );
        }
        $rule_text = '<a href="'.$this->getUrl("couponcode/rule/edit", ['coupon_rule_id'=>$model->getRuleId()]).'" target="_BLANK">'.$model->getRuleId().' ['.__("Edit Rule").']</a>';
        $fieldset->addField(
            'rule_id',
            'note',
            ['name' => 'rule_id', 'label' => __('Rule Id'), 'title' => __('Rule Id'), 'required' => false, 'text' => $rule_text]
        );

        if($this->_couponHelper->getConfig('general_settings/allow_track_log')) {
            $track_link = $model->getGeneratedLink();
            $email = $model->getEmailAddress();
            $coupon_code = $model->getCouponCode();
            $track_link = str_replace(array("+","%3A","%3D","%26","%3F"),array(" ",":","=","&","?"), $track_link);
            if(false === strpos($track_link, '?coupon_code')){
                $track_link .="?coupon_code=".$coupon_code."&email=".$email;
            }
            if($model->getOrderId()) {
                $track_link .= "&order_id=".$model->getOrderId();
            }
            $track_link_text = '<a href="'.$track_link.'" target="_BLANK">'.__("Track the status on frontend").'</a>';

            $qrcode_track_link = str_replace(array(" ",":","=","&","?"),array("+","%3A","%3D","%26","%3F"), $track_link);
            $qrcode_track_link = 'https://chart.googleapis.com/chart?chs=200x200&cht=qr&chl='.$qrcode_track_link.'&choe=UTF-8';

            $track_link_text .='<br/><img src="'.$qrcode_track_link.'" title="'.__("Scan QrCode").'"/>';
            $fieldset->addField(
                'track_link',
                'note',
                [
                    'name' => 'track_link',
                    'label' => __('Track Link'),
                    'title' => __('Track Link'),
                    'text' => $track_link_text
                ]
            );

            $barcode = $model->getBarcode();
            if($barcode) {
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
                $mediaUrl =  $storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);

                $barcode_image_url = $mediaUrl.$barcode;
                $barcode_link_text ='<img src="'.$barcode_image_url.'" title="'.__("Scan Barcode").'"/>';
                $fieldset->addField(
                    'barcode',
                    'note',
                    [
                        'name' => 'barcode',
                        'label' => __('Barcode'),
                        'title' => __('Barcode'),
                        'text' => $barcode_link_text
                    ]
                );
            }
        }

        $fieldset->addField(
            'ip_address',
            'note',
            [
                'name' => 'ip_address',
                'label' => __('IP Address'),
                'title' => __('IP Address'),
                'text' => $model->getIpAddress()
            ]
        );
        $fieldset->addField(
            'client_info',
            'note',
            [
                'name' => 'client_info',
                'label' => __('Browser Info'),
                'title' => __('Browser Info'),
                'text' => $model->getClientInfo()
            ]
        );

        $curent_item = $model->getOrigData();
        if(isset($curent_item['order_id']) && $curent_item['order_id']) {
            $_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $orderfieldset = $form->addFieldset('order_fieldset', ['legend' => __('Order Information')]);
            $order_info = $_objectManager->create('Magento\Sales\Model\Order')->loadByIncrementId($curent_item['order_id']);
            $currencySymbol = $this->_priceCurrency->getCurrency()->getCurrencySymbol();
            $orderfieldset->addField(
                'order_status',
                'note',
                [
                    'name' => 'order_status',
                    'label' => __('Order Status'),
                    'title' => __('Order status'),
                    'text' => '<strong style="color:#2196F3">'.$order_info->getStatus().'</strong>'
                ]
            );
            $orderfieldset->addField(
                'order_total',
                'note',
                [
                    'name' => 'order_total',
                    'label' => __('Order Total'),
                    'title' => __('Order Total'),
                    'text' => $currencySymbol . $order_info->getGrandTotal()
                ]
            );

            $orderfieldset->addField(
                'order_date',
                'note',
                [
                    'name' => 'order_date',
                    'label' => __('Order Date'),
                    'title' => __('Order Date'),
                    'text' => $this->formatDate(
                                $this->_localeDate->date(new \DateTime($order_info->getCreatedAt())),
                                \IntlDateFormatter::MEDIUM,
                                true
                            )
                ]
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
        return __('Coupon Logs Information');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Coupon Logs Information');
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
