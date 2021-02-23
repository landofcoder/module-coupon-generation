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
namespace Lof\CouponCode\Model;

use Lof\CouponCode\Api\Data\CouponInterface;

class Coupon extends \Magento\Framework\Model\AbstractModel implements CouponInterface
{
    /**
     * Blog's Statuses
     */
    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;



    /** @var \Magento\Store\Model\StoreManagerInterface */
    protected $_storeManager;

    /**
     * URL Model instance
     *
     * @var \Magento\Framework\UrlInterface
     */
    protected $_url;

    protected $_resource;

    protected $_couponCollection;
    /**
     * Page cache tag
     */
    /**
     * @param \Magento\Framework\Model\Context                          $context                  
     * @param \Magento\Framework\Registry                               $registry                 
     * @param \Lof\CouponCode\Model\ResourceModel\Coupon|null                $resource
     * @param \Lof\CouponCode\Model\ResourceModel\Coupon\Collection|null $resourceCollection                 
     * @param \Lof\CouponCode\Model\ResourceModel\Coupon\CollectionFactory|null $couponCollection      
     * @param \Magento\Store\Model\StoreManagerInterface                $storeManager             
     * @param \Magento\Framework\UrlInterface                           $url                      
     * @param array                                                     $data                     
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Lof\CouponCode\Model\ResourceModel\Coupon $resource = null,
        \Lof\CouponCode\Model\ResourceModel\Coupon\Collection $resourceCollection = null,
        \Lof\CouponCode\Model\ResourceModel\Coupon\CollectionFactory $couponCollection,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\UrlInterface $url,
        array $data = []
        ) {
        $this->_storeManager = $storeManager;
        $this->_url = $url;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->_resource = $resource;
        $this->_couponCollection = $couponCollection;
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Lof\CouponCode\Model\ResourceModel\Coupon');
    }

    /**
     * Load object data
     * @param string $alias
     * @return $this
     */
    public function getCouponByAlias($alias){
        $this->_beforeLoad($alias, 'alias');
        $this->_getResource()->load($this, $alias, 'alias');
        $this->_afterLoad();
        $this->setOrigData();
        $this->_hasDataChanges = false;
        return $this;
    }

    /**
     * Get coupon_id
     * @return string
     */
    public function getCouponId()
    {
        return $this->getData(self::COUPON_ID);
    }

    /**
     * Set coupon_id
     * @param string $couponId
     * @return \Lof\CouponCode\Api\Data\CouponInterface
     */
    public function setCouponId($couponId)
    {
        return $this->setData(self::COUPON_ID, $couponId);
    }

    /**
     * Get couponcode_id
     * @return string
     */
    public function getCouponcodeId()
    {
        return $this->getData(self::COUPONCODE_ID);
    }

    /**
     * Set couponcode_id
     * @param string $couponcode_id
     * @return \Lof\CouponCode\Api\Data\CouponInterface
     */
    public function setCouponcodeId($couponcode_id)
    {
        return $this->setData(self::COUPONCODE_ID, $couponcode_id);
    }
}
