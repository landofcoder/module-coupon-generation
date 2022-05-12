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
namespace Lof\CouponCode\Ui\Component\MassAction\Group;

use Magento\Framework\UrlInterface;
use Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory;

/**
 * Class Options
 */
class Options implements \JsonSerializable
{
    /**
     * @var array
     */
    protected $options;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * Additional options params
     *
     * @var array
     */
    protected $data;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * Base URL for subactions
     *
     * @var string
     */
    protected $urlPath;

    /**
     * Param name for subactions
     *
     * @var string
     */
    protected $paramName;

    /**
     * Additional params for subactions
     *
     * @var array
     */
    protected $additionalData = [];

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resource;

    public function __construct(
        CollectionFactory $collectionFactory,
        UrlInterface $urlBuilder,
        \Magento\Framework\App\ResourceConnection $resource,
        array $data = []
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->data = $data;
        $this->urlBuilder = $urlBuilder;
        $this->resource                     = $resource;
    }

    /**
     * Get action options
     *
     * @return mixed
     */
    public function jsonSerialize(): mixed
    {
        if ($this->options === null) {
            $lofRuleTable = $this->resource->getTableName('lof_couponcode_rule');
            $options = $this->collectionFactory->create()->addFieldToFilter('coupon_rule_id', ['gt' => 0]);
            $options->getSelect()->join(
                ['lof_couponcode_rule' => $lofRuleTable],
                'main_table.rule_id = lof_couponcode_rule.rule_id',
                ['coupon_rule_id'],
                null,
                'left'
                );
            $this->prepareData();
            if(count($options->getData())){
            foreach ($options->getData() as $optionCode) {
                $this->options[$optionCode['coupon_rule_id']] = [
                    'type' => 'rule_' . $optionCode['coupon_rule_id'],
                    'label' => $optionCode['name'],
                ];


                if ($this->urlPath && $this->paramName) {
                    $this->options[$optionCode['coupon_rule_id']]['url'] = $this->urlBuilder->getUrl(
                        $this->urlPath,
                        [$this->paramName => $optionCode['coupon_rule_id']]
                    );
                }

                $this->options[$optionCode['coupon_rule_id']] = array_merge_recursive(
                    $this->options[$optionCode['coupon_rule_id']],
                    $this->additionalData
                );
            }
            $this->options = array_values($this->options);
            }


        }

        return $this->options;
    }

    /**
     * Prepare addition data for subactions
     *
     * @return void
     */
    protected function prepareData()
    {
        foreach ($this->data as $key => $value) {
            switch ($key) {
                case 'urlPath':
                    $this->urlPath = $value;
                    break;
                case 'paramName':
                    $this->paramName = $value;
                    break;
                default:
                    $this->additionalData[$key] = $value;
                    break;
            }
        }
    }
}
