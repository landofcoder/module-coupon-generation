<?php
/**
 * Lof CouponCode is a powerful tool for managing the processing return and exchange requests within your workflow. This, in turn, allows your customers to request and manage returns and exchanges directly from your webstore. The Extension compatible with magento 2.x
 * Copyright (C) 2017  Landofcoder.com
 * 
 * This file is part of Lof/CouponCode.
 * 
 * Lof/CouponCode is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

namespace Lof\CouponCode\Model;

use Lof\CouponCode\Api\RuleRepositoryInterface;
use Lof\CouponCode\Api\Data\RuleSearchResultsInterfaceFactory;
use Lof\CouponCode\Api\Data\RuleInterfaceFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Lof\CouponCode\Model\ResourceModel\Rule as ResourceRule;
use Lof\CouponCode\Model\ResourceModel\Rule\CollectionFactory as RuleCollectionFactory;
use Magento\Store\Model\StoreManagerInterface;

class RuleRepository implements RuleRepositoryInterface
{

    protected $resource;

    protected $ruleFactory;

    protected $ruleCollectionFactory;

    protected $searchResultsFactory;

    protected $dataObjectHelper;

    protected $dataObjectProcessor;

    protected $dataRuleFactory;

    private $storeManager;

    protected $_objectManager;


    /**
     * @param ResourceRule $resource
     * @param RuleFactory $ruleFactory
     * @param RuleInterfaceFactory $dataRuleFactory
     * @param RuleCollectionFactory $ruleCollectionFactory
     * @param RuleSearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ResourceRule $resource,
        RuleFactory $ruleFactory,
        Rule $rule,
        RuleInterfaceFactory $dataRuleFactory,
        RuleCollectionFactory $ruleCollectionFactory,
        RuleSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager,
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        $this->resource = $resource;
        $this->ruleFactory = $ruleFactory;
        $this->rule = $rule;
        $this->ruleCollectionFactory = $ruleCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataRuleFactory = $dataRuleFactory;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->storeManager = $storeManager;
        $this->_objectManager = $objectManager;
    }

    /**
     * {@inheritdoc}
     */
    public function save(
        \Lof\CouponCode\Api\Data\RuleInterface $rule
    ) {

        if ($rule) {
            try {
                /** @var $model \Magento\SalesRule\Model\Rule */
                $model = $this->_objectManager->create('Lof\CouponCode\Model\Rule');
                $model_sale_rule = $this->_objectManager->create('Magento\SalesRule\Model\Rule');

                if ($rule->getSimpleAction() == 'by_percent' && $rule->getDiscountAmount() != null) {
                    $data['simple_action'] = $rule->getSimpleAction();
                    $data['discount_amount'] = min(100, $rule->getDiscountAmount());
                }

                if ($rule->getConditions()) {
                    $data['conditions'] = $rule->getConditions();
                } 
                if ($rule->getActions()) {
                    $data['actions'] = $rule->getActions();
                }
                if($rule->getCouponsGenerated()){
                    $data['coupons_generated'] = $rule->getCouponsGenerated();
                }else{
                    $data['coupons_generated'] = 0;
                }
                $data['coupons_length'] = $rule->getCodeLength();
                $data['coupon_type'] = '2';
                $data['use_auto_generation'] = '1';
                $data['name'] = $rule->getRuleName();
                $model_sale_rule->loadPost($data);
                $model->setData($data);
                $model_sale_rule->save();
                $model->setData('rule_id',$model_sale_rule->getId());
                $model->setData('name',$model_sale_rule->getName());
                $model->save();

                return true;
            } catch (\Exception $exception) {
                throw new CouldNotSaveException(__(
                    'Could not save the rule: %1',
                    $exception->getMessage()
                ));
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getById($ruleId)
    {

        $rule = $this->rule->load($ruleId, 'rule_id');
        $ruleData = $rule->getData();
        if (!$ruleData["rule_id"]) {
            throw new NoSuchEntityException(__('Rule with id "%1" does not exist.', $ruleId));
        }else {
           return true;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ) {
        $collection = $this->ruleCollectionFactory->create();
        foreach ($criteria->getFilterGroups() as $filterGroup) {
            foreach ($filterGroup->getFilters() as $filter) {
                if ($filter->getField() === 'store_id') {
                    $collection->addStoreFilter($filter->getValue(), false);
                    continue;
                }
                $condition = $filter->getConditionType() ?: 'eq';
                $collection->addFieldToFilter($filter->getField(), [$condition => $filter->getValue()]);
            }
        }
        
        $sortOrders = $criteria->getSortOrders();
        if ($sortOrders) {
            /** @var SortOrder $sortOrder */
            foreach ($sortOrders as $sortOrder) {
                $collection->addOrder(
                    $sortOrder->getField(),
                    ($sortOrder->getDirection() == SortOrder::SORT_ASC) ? 'ASC' : 'DESC'
                );
            }
        }
        $collection->setCurPage($criteria->getCurrentPage());
        $collection->setPageSize($criteria->getPageSize());
        
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);
        $searchResults->setTotalCount($collection->getSize());
        $searchResults->setItems($collection->getItems());
        return $searchResults;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(
        \Lof\CouponCode\Api\Data\RuleInterface $rule
    ) {
        try {
            $rule->getResource()->delete($rule);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the Rule: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($ruleId)
    {
        if($this->getById($ruleId)){
            $this->rule->load($ruleId, 'rule_id')->delete();
            return true;
        }else{
            throw new CouldNotDeleteException(__('Could not delete the Rule: %1'));
        }
    }
}
