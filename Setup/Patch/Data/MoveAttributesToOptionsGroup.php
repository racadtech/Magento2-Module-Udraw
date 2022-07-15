<?php

namespace Racadtech\Udraw\Setup\Patch\Data;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Setup\CategorySetupFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;
use Magento\GiftMessage\Setup\Patch\Data\AddGiftMessageAttributes;

class MoveAttributesToOptionsGroup implements DataPatchInterface, PatchVersionInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private ModuleDataSetupInterface $moduleDataSetup;

    /**
     * @var CategorySetupFactory
     */
    private CategorySetupFactory $categorySetupFactory;

    /**
     * MoveGiftMessageToGiftOptionsGroup constructor.
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param CategorySetupFactory $categorySetupFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        CategorySetupFactory $categorySetupFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->categorySetupFactory = $categorySetupFactory;
    }

    /**
     * {@inheritdoc}
     * @throws LocalizedException
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        $categorySetup = $this->categorySetupFactory->create(['setup' => $this->moduleDataSetup]);
        $entityTypeId = $categorySetup->getEntityTypeId(Product::ENTITY);
        $attributeSetId = $categorySetup->getDefaultAttributeSetId(Product::ENTITY);

        $groupName = 'uDraw Options';

        if (!$categorySetup->getAttributeGroup(Product::ENTITY, $attributeSetId, $groupName)) {
            $categorySetup->addAttributeGroup(Product::ENTITY, $attributeSetId, $groupName, 1);
        }
        $categorySetup->addAttributeToGroup(
            $entityTypeId,
            $attributeSetId,
            $groupName,
            $categorySetup->getAttribute($entityTypeId, 'udraw_linked_template')['attribute_id'],
            1
        );
        $categorySetup->addAttributeToGroup(
            $entityTypeId,
            $attributeSetId,
            $groupName,
            $categorySetup->getAttribute($entityTypeId, 'udraw_linked_pricematrix')['attribute_id'],
            2
        );
        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies(): array
    {
        return [
            AddUdrawAttributes::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function getVersion(): string
    {
        return '1.4.0';
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases(): array
    {
        return [];
    }
}
