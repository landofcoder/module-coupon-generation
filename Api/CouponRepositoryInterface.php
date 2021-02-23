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

namespace Lof\CouponCode\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

interface CouponRepositoryInterface
{


    /**
     * Save Coupon
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return string
     */
    public function save();

    /**
     * Retrieve Coupon
     * @param string $couponId
     * @return \Lof\CouponCode\Api\Data\CouponInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($couponId);

    /**
     * Retrieve Coupon matching the specified criteria.
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Lof\CouponCode\Api\Data\CouponSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete Coupon
     * @param \Lof\CouponCode\Api\Data\CouponInterface $coupon
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(
        \Lof\CouponCode\Api\Data\CouponInterface $coupon
    );

    /**
     * Delete Coupon by ID
     * @param string $couponId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($couponId);
}
