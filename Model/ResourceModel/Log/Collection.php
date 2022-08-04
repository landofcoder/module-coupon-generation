<?php
/**
 * Landofcoder
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Landofcoder.com license that is
 * available through the world-wide-web at this URL:
 * http://landofcoder.com/license
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category   Landofcoder
 * @package    Lof_RewardPoints
 * @copyright  Copyright (c) 2016 Landofcoder (http://www.landofcoder.com/)
 * @license    http://www.landofcoder.com/LICENSE-1.0.html
 */

namespace Lof\CouponCode\Model\ResourceModel\Log;

use Lof\CouponCode\Model\ResourceModel\AbstractCollection;

class Collection extends AbstractCollection
{

	protected function _afterLoad()
    {
        return parent::_afterLoad();
    }
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Lof\CouponCode\Model\Log', 'Lof\CouponCode\Model\ResourceModel\Log');
    }
}
