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
namespace Lof\CouponCode\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;
    /**
    * @var \Magento\Framework\View\Element\BlockFactory
    */
    protected $_blockFactory;
    /**
    *@var \Magento\Store\Model\StoreManagerInterface
    */
    protected $_storeManager;

    protected $_scopeConfig;

    /**
     * @var \Magento\Framework\Translate\Inline\StateInterface
     */
    protected $inlineTranslation;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $_localeDate;
    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $_transportBuilder;

    /**
     * @var \Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * Sales rule coupon
     *
     * @var \Magento\SalesRule\Helper\Coupon
     */
    protected $salesRuleCoupon;
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resource;

    protected $_coupon_rule_model = [];
    /**
     * @var \Magento\Cms\Model\Template\FilterProvider
     */
    protected $_filterProvider;
    protected $ruleFactory;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\View\Element\BlockFactory $blockFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Cms\Model\Template\FilterProvider $filterProvider,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory $collectionFactory,
        \Magento\SalesRule\Helper\Coupon $salesRuleCoupon,
        \Magento\Framework\App\ResourceConnection $resource,
        \Lof\CouponCode\Model\RuleFactory $ruleFactory
    ) {
        parent::__construct($context);
        $this->_localeDate     = $localeDate;
        $this->_scopeConfig    = $context->getScopeConfig();
        $this->_blockFactory   = $blockFactory;
        $this->_storeManager   = $storeManager;
        $this->_filterProvider = $filterProvider;
        $this->_objectManager  = $objectManager;
        $this->inlineTranslation    = $inlineTranslation;
        $this->_transportBuilder    = $transportBuilder;
        $this->collectionFactory = $collectionFactory;
        $this->salesRuleCoupon = $salesRuleCoupon;
        $this->ruleFactory = $ruleFactory;
        $this->resource                     = $resource;
    }

    /**
     * Send email
     *
     * @param string $emailFrom
     * @param string $emailTo
     * @param string emailidentifier
     * @param mixed $templateVar
     * @return void
     */
    public function sendMail($emailFrom, $emailTo, $emailidentifier, $templateVar)
    {
        $this->inlineTranslation->suspend();
        $transport = $this->_transportBuilder->setTemplateIdentifier($emailidentifier)
            ->setTemplateOptions(
                [
                    'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                    'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
                ]
            )
            ->setTemplateVars($templateVar)
            ->setFrom($emailFrom)
            ->addTo($emailTo)
            ->setReplyTo($emailTo)

            ->getTransport();
        $transport->sendMessage();
        $this->inlineTranslation->resume();
    }

    /**
     * get config
     *
     * @param string $key
     * @param mixed $store
     * @return mixed
     */
    public function getConfig($key, $store = null)
    {
        $store = $this->_storeManager->getStore($store);
        //$websiteId = $store->getWebsiteId();
        $result = $this->scopeConfig->getValue(
            'lofcouponcode/' . $key,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
        return $result;
    }

    /**
     * Get rule data
     *
     * @param int $ruleId
     * @return \Magento\SalesRule\Model\Rule
     */
    public function getRuleData($ruleId)
    {
        $modelRule = $this->_objectManager->create('Magento\SalesRule\Model\Rule');
        $collection = $modelRule->load($ruleId);
        return $collection;
    }

    /**
     * Filter string
     *
     * @param string $str
     * @return string
     */
    public function filter($str)
    {
        $html = $this->_filterProvider->getPageFilter()->filter($str);
        return $html;
    }

    public function getCouponRuleData($ruleId)
    {
        if (!isset($this->_coupon_rule_model[$ruleId])) {
//            $model = $this->_objectManager->create('Lof\CouponCode\Model\Rule');
            $model = $this->ruleFactory->create();
            if (is_numeric($ruleId)) {
                $collection = $model->load($ruleId);
            } else {
                $collection = $model->loadByAlias($ruleId);
            }
            $this->_coupon_rule_model[$ruleId] = $collection;
        }
        return $this->_coupon_rule_model[$ruleId];
    }

    /**
    * Generate coupon code
    *
    * @return string
    */
    public function generateCode($ruleId)
    {
        $format = $this->getCouponRuleData($ruleId)->getCouponsFormat();
        if (empty($format)) {
            $format = \Magento\SalesRule\Helper\Coupon::COUPON_FORMAT_ALPHANUMERIC;
        }
        $splitChar = '-';
        $charset = $this->salesRuleCoupon->getCharset($format);

        $charsetSize = count($charset);
        $code  = '';
        $split = $this->getCouponRuleData($ruleId)->getCouponsDash();
        $length = $this->getCouponRuleData($ruleId)->getCouponsLength();
        for ($i = 0; $i < $length; ++$i) {
            $char = $charset[\Magento\Framework\Math\Random::getRandomNumber(0, $charsetSize - 1)];
            if (($split > 0) && (($i % $split) === 0) && ($i !== 0)) {
                $char = $splitChar . $char;
            }
            $code .= $char;
        }
        $prefix = $this->getCouponRuleData($ruleId)->getCouponsPrefix();
        $suffix = $this->getCouponRuleData($ruleId)->getCouponsSuffix();
        return $prefix . $code . $suffix;
    }

    public function getAllRule()
    {
        $salesruleTable = $this->resource->getTableName('salesrule');
        $lofRuleTable = $this->resource->getTableName('lof_couponcode_rule');

        $collection = $this->collectionFactory->create();
        $collection->getSelect()->join(
            ['lof_couponcode_rule' => $lofRuleTable],
            'main_table.rule_id = lof_couponcode_rule.rule_id',
            ['coupon_rule_id']
        );
        $param = [];
        foreach ($collection as $rule) {
            $param[$rule['coupon_rule_id']] = $rule['name'];
        }
        return $param;
    }

    public function getBaseUrl()
    {
        return $this->_storeManager->getStore()->getBaseUrl();
    }

    public function getTrackLink()
    {
        $base_url = $this->getBaseUrl();
        $route = $this->getConfig("general_settings/track_route");
        if (!$route) {
            $route = "couponcode/track/trackcode";
        }
        return $base_url . $route;
    }

    /**
     * Is enabled module on frontend
     * @return int|bool
     */
    public function isEnabled()
    {
        return (int)$this->getConfig("general_settings/show");
    }
}
