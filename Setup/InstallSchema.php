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
 * @copyright  Copyright (c) 2019 Landofcoder (http://www.landofcoder.com/)
 * @license    http://www.landofcoder.com/LICENSE-1.0.html
 */
namespace Lof\CouponCode\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;

class InstallSchema implements InstallSchemaInterface
{
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();

        /**
         * Create table 'lof_couponcode_rule'
         */
        $setup->getConnection()->dropTable($setup->getTable('lof_couponcode_rule'));
        $table = $installer->getConnection()->newTable(
            $installer->getTable('lof_couponcode_rule')
        )->addColumn(
            'coupon_rule_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Coupon Rule Id'
        )->addColumn(
            'rule_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['nullable' => false],
            'Rule Id'
        )->addColumn(
            'rule_key',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            150,
            ['nullable' => false],
            'Rule Identifier Key'
        )->addColumn(
            'coupons_length',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            11,
            ['nullable' => false],
            'Coupons Length'
        )->addColumn(
            'coupons_format',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Coupon Format'
        )->addColumn(
            'coupons_prefix',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Coupons Prefix'
        )->addColumn(
            'coupons_suffix',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Coupons Suffix'
        )->addColumn(
            'coupons_dash',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Coupons Dash'
        )->addColumn(
            'coupons_generated',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['nullable' => false],
            'Coupons Generated'
        )->addColumn(
            'limit_generated',
            Table::TYPE_SMALLINT,
            4,
            ['nullable' => true],
            'Limit Number generated coupon codes per email. Empty for unlimited'
        )->addColumn(
            'is_check_email',
            Table::TYPE_SMALLINT,
            4,
            ['nullable' => false, 'default' => 0],
            'Need check exactly email address when apply discount code. Default 0 - disabled'
        )->addIndex(
                $setup->getIdxName('lof_couponcode_rule', ['coupon_rule_id']),
                ['coupon_rule_id']
                );
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'lof_couponcode_coupon'
         */

        $setup->getConnection()->dropTable($setup->getTable('lof_couponcode_coupon'));
        $table = $installer->getConnection()->newTable(
            $installer->getTable('lof_couponcode_coupon')
        )
        ->addColumn(
            'couponcode_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['identity' => true, 'nullable' => false, 'primary' => true],
            'Coupon Code ID'
        )->addColumn(
            'alias',
            Table::TYPE_TEXT,
            150,
            ['nullable' => true],
            'Alias'
        )->addColumn(
            'code',
            Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Code'
        )->addColumn(
            'coupon_id',
            Table::TYPE_SMALLINT,
            null,
            ['nullable' => false],
            'Coupon Id'
        )->addColumn(
            'rule_id',
            Table::TYPE_INTEGER,
            null,
            ['nullable' => false],
            'Rule Id'
        )->addColumn(
            'customer_id',
            Table::TYPE_INTEGER,
            null,
            ['nullable' => true],
            'Customer Id'
        )->addColumn(
            'email',
            Table::TYPE_TEXT,
            null,
            ['nullable' => false],
            'Email'
        )->addIndex(
                $setup->getIdxName('lof_couponcode_coupon', ['couponcode_id']),
                ['couponcode_id']
                );
        $installer->getConnection()->createTable($table);
        $installer->endSetup();

    }
}
