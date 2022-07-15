<?php

namespace Racadtech\Udraw\Setup\Patch\Data;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Setup\CategorySetupFactory;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Zend_Validate_Exception;

class UdrawSettingsPatchV2 implements DataPatchInterface
{
    private ModuleDataSetupInterface $moduleDataSetup;
    private EavSetupFactory $eavSetupFactory;
    private CategorySetupFactory $categorySetupFactory;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param EavSetupFactory $eavSetupFactory
     * @param CategorySetupFactory $categorySetupFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory,
        CategorySetupFactory $categorySetupFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
        $this->categorySetupFactory = $categorySetupFactory;
    }

    public static function getDependencies(): array
    {
        return [
            UdrawSettingsPatch::class
        ];
    }

    public function getAliases(): array
    {
        return [];
    }

    public function apply()
    {
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $categorySetup = $this->categorySetupFactory->create(['setup' => $this->moduleDataSetup]);
        $moduleSetup = $this->moduleDataSetup->getConnection();
        $moduleSetup->startSetup();

        try {
            $eavSetup->addAttribute(Product::ENTITY, 'udraw_enable_gosendex_upload', [
                'group' => 'uDraw Options',
                'type' => 'varchar',
                'label' => 'GoSendEx Upload',
                'source' => 'Racadtech\Udraw\Model\Attribute\Source\GoSendExUpload',
                'backend' => '',
                'frontend' => '',
                'input' => 'select',
                'sort_order' => 3,
                'class' => '',
                'global' => ScopedAttributeInterface::SCOPE_STORE,
                'visible' => true,
                'required' => false,
                'user_defined' => false,
                'default' => '',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false
            ]);

            $entityTypeId = $categorySetup->getEntityTypeId(Product::ENTITY);
            $attributeSetId = $categorySetup->getDefaultAttributeSetId(Product::ENTITY);

            $categorySetup->addAttributeToGroup(
                $entityTypeId,
                $attributeSetId,
                'uDraw Options',
                $categorySetup->getAttribute($entityTypeId, 'udraw_enable_gosendex_upload')['attribute_id'],
                3
            );
        } catch (LocalizedException | Zend_Validate_Exception $e) {
        }

        $moduleSetup->insert(
            $this->moduleDataSetup->getTable('racadtech_udraw_settings'),
            ['name' => 'custom_designer_css', 'value' => '', 'label' => 'Custom Designer CSS', 'section' => 'Designer Settings', 'control_type' => 'textarea']
        );

        $moduleSetup->insert(
            $this->moduleDataSetup->getTable('racadtech_udraw_settings'),
            ['name' => 'gosendex_api_key', 'value' => '', 'label' => 'GoSendEx Api Key', 'section' => 'GoSendEx Settings', 'control_type' => 'text']
        );

        $moduleSetup->insert(
            $this->moduleDataSetup->getTable('racadtech_udraw_settings'),
            ['name' => 'gosendex_domain', 'value' => '', 'label' => 'GoSendEx Domain', 'section' => 'GoSendEx Settings', 'control_type' => 'text']
        );

        $moduleSetup->endSetup();
    }
}
