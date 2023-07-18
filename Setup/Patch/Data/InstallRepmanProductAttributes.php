<?php
/**
 * @package     Dadolun_Repman
 * @copyright   Copyright (c) 2023 Dadolun (https://www.dadolun.com)
 * @license     This code is licensed under MIT LICENSE (see LICENSE for details)
 */
namespace Dadolun\Repman\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;

/**
 * Class InstallRepmanProductAttributes
 * @package Dadolun\Repman\Setup\Patch\Data
 */
class InstallRepmanProductAttributes implements DataPatchInterface, PatchRevertableInterface
{
    /** @var ModuleDataSetupInterface */
    private $moduleDataSetup;

    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * InstallRepmanAttributes constructor.
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $this->moduleDataSetup->startSetup();
        /**
         * @var EavSetup $eavSetup
         */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY, 'repman_repository_type');
        $eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY, 'repman_eof_expression');
        $eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY, 'repman_repository');
        $eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY, 'repman_installation_instructions');
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'repman_repository',
            [
                'type' => 'text',
                'backend' => '',
                'frontend' => '',
                'label' => 'Repman Repository name',
                'input' => 'text',
                'class' => '',
                'source' => '',
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'required' => false,
                'user_defined' => false,
                'default' => '',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => false,
                'unique' => false
            ]
        );
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'repman_eof_expression',
            [
                'type' => 'text',
                'backend' => '',
                'frontend' => '',
                'label' => 'Repman Package subscription end expression',
                'input' => 'text',
                'class' => '',
                'source' => '',
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'required' => false,
                'user_defined' => false,
                'default' => '',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => false,
                'unique' => false
            ]
        );
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'repman_repository_type',
            [
                'type' => 'varchar',
                'backend' => '',
                'frontend' => '',
                'label' => 'Repman Repository type',
                'input' => 'select',
                'class' => '',
                'source' => \Dadolun\Repman\Model\Product\Attribute\Source\RepositoryType::class,
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'required' => false,
                'user_defined' => false,
                'default' => '',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => false,
                'unique' => false
            ]
        );
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'repman_installation_instructions',
            [
                'type' => 'text',
                'backend' => '',
                'frontend' => '',
                'label' => 'Repman Package Installation Instructions',
                'input' => 'textarea',
                'class' => '',
                'source' => '',
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'visible' => true,
                'required' => false,
                'user_defined' => false,
                'default' => '',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => false,
                'unique' => false
            ]
        );
        $this->moduleDataSetup->endSetup();
    }

    public function revert()
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        /**
         * @var EavSetup $eavSetup
         */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY, 'repman_repository_type');
        $eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY, 'repman_eof_expression');
        $eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY, 'repman_repository');
        $eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY, 'repman_installation_instructions');
        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function getVersion()
    {
        return '1.0.0';
    }
}
