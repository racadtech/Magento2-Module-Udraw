<?php

namespace Racadtech\Udraw\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;

class UdrawSettingsPatch implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private ModuleDataSetupInterface $moduleDataSetup;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
    }

    public static function getDependencies(): array
    {
        return [];
    }

    public function getAliases(): array
    {
        return [];
    }

    public function apply()
    {
        $moduleSetup = $this->moduleDataSetup->getConnection();
        $moduleSetup->startSetup();

        $moduleSetup->insert(
            $this->moduleDataSetup->getTable('racadtech_udraw_settings'),
            ['name' => 'udraw_api_key', 'value' => '', 'label' => 'uDraw Api Key', 'section' => 'Api Settings', 'control_type' => 'text']
        );

        $moduleSetup->insert(
            $this->moduleDataSetup->getTable('racadtech_udraw_settings'),
            ['name' => 'udraw_secret_key', 'value' => '', 'label' => 'uDraw Secret Key', 'section' => 'Api Settings', 'control_type' => 'text']
        );

        $moduleSetup->insert(
            $this->moduleDataSetup->getTable('racadtech_udraw_settings'),
            ['name' => 'udraw_designer_ui', 'value' => 'default', 'label' => 'uDraw Designer UI', 'section' => 'Designer Settings', 'control_type' => 'hidden']
        );

        $moduleSetup->insert(
            $this->moduleDataSetup->getTable('racadtech_udraw_settings'),
            ['name' => 'custom_designer_js', 'value' => '', 'label' => 'Custom Designer JS', 'section' => 'Designer Settings', 'control_type' => 'textarea']
        );

        $moduleSetup->insert(
            $this->moduleDataSetup->getTable('racadtech_udraw_settings'),
            ['name' => 'custom_pricematrix_js', 'value' => '', 'label' => 'Custom Pricematrix JS', 'section' => 'Price Matrix Settings', 'control_type' => 'textarea']
        );

        $moduleSetup->insert(
            $this->moduleDataSetup->getTable('racadtech_udraw_settings'),
            ['name' => 'custom_pricematrix_css', 'value' => '', 'label' => 'Custom Pricematrix CSS', 'section' => 'Price Matrix Settings', 'control_type' => 'textarea']
        );

        $moduleSetup->endSetup();
    }
}
