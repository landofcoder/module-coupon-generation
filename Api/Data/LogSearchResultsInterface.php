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

namespace Lof\CouponCode\Api\Data;

interface LogSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{


    /**
     * Get Log list.
     * @return \Lof\CouponCode\Api\Data\LogInterface[]
     */
    public function getItems();

    /**
     * Set log_id list.
     * @param \Lof\CouponCode\Api\Data\LogInterface[] $items
     * @return $this
     */
    public function setItems( $items);
}
