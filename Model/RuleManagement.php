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

class RuleManagement
{
    protected $_objectManager;
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        $this->_objectManager = $objectManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getRule()
    {
        $coupon_model = $this->_objectManager->create('Lof\CouponCode\Model\ResourceModel\Rule')->getRuleData();
        return json_encode($coupon_model);
    }

    /**
     * {@inheritdoc}
     */
    public function postRule($param)
    {
        return 'hello api POST return the $param ' . $param;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteRule($param)
    {
        return 'hello api DELETE return the $param ' . $param;
    }

    /**
     * {@inheritdoc}
     */
    public function putRule($param)
    {
        return 'hello api PUT return the $param ' . $param;
    }
}
