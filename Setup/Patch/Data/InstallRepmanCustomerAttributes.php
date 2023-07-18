<?php
/**
 * @package     Dadolun_Repman
 * @copyright   Copyright (c) 2023 Dadolun (https://www.dadolun.com)
 * @license     This code is licensed under MIT LICENSE (see LICENSE for details)
 */
namespace Dadolun\Repman\Setup\Patch\Data;

use Magento\Customer\Setup\CustomerSetup;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Customer\Api\CustomerMetadataInterface;

/**
 * Class InstallRepmanCustomerAttributes
 * @package Dadolun\Repman\Setup\Patch\Data
 */
class InstallRepmanCustomerAttributes implements DataPatchInterface, PatchRevertableInterface
{
    /** @var ModuleDataSetupInterface */
    private $moduleDataSetup;

    /**
     * @var CustomerSetupFactory
     */
    private $customerSetupFactory;

    /**
     * InstallRepmanCustomerAttributes constructor.
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param CustomerSetupFactory $customerSetupFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        CustomerSetupFactory $customerSetupFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->customerSetupFactory = $customerSetupFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $this->moduleDataSetup->startSetup();
        /**
         * @var CustomerSetup $customerSetup
         */
        $customerSetup = $this->customerSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $customerSetup->addAttribute(
            CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER,
            'repman_id',
            [
                'label' => 'Repman Uid',
                'required' => 0,
                'position' => 200,
                'system' => 0,
                'user_defined' => 1,
                'is_used_in_grid' => 1,
                'is_visible_in_grid' => 1,
                'is_filterable_in_grid' => 1,
                'is_searchable_in_grid' => 1
            ]
        );
        $customerSetup->addAttributeToSet(
            CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER,
            CustomerMetadataInterface::ATTRIBUTE_SET_ID_CUSTOMER,
            null,
            'repman_id'
        );
        $customerSetup->addAttribute(
            CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER,
            'repman_token',
            [
                'label' => 'Repman Unique Organization Token',
                'required' => 0,
                'position' => 200,
                'system' => 0,
                'user_defined' => 1,
                'is_used_in_grid' => 1,
                'is_visible_in_grid' => 1,
                'is_filterable_in_grid' => 1,
                'is_searchable_in_grid' => 1
            ]
        );
        $customerSetup->addAttributeToSet(
            CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER,
            CustomerMetadataInterface::ATTRIBUTE_SET_ID_CUSTOMER,
            null,
            'repman_token'
        );

        $this->moduleDataSetup->endSetup();
    }

    public function revert()
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        /**
         * @var CustomerSetup $customerSetup
         */
        $customerSetup = $this->customerSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $customerSetup->removeAttribute(\Magento\Customer\Model\Customer::ENTITY, 'repman_id');
        $customerSetup->removeAttribute(\Magento\Customer\Model\Customer::ENTITY, 'repman_token');
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
