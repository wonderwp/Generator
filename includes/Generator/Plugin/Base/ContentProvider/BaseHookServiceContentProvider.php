<?php

namespace WonderWp\Plugin\Generator\Generator\Plugin\Base\ContentProvider;

class BaseHookServiceContentProvider
{
    /**
     * @return string
     */
    public function getUsesContent()
    {
        return <<<'EOD'

use WonderWp\Component\PluginSkeleton\AbstractManager;
EOD;
    }

    /**
     * @return string
     */
    public function getHooksDeclarationsContent()
    {
        return <<<'EOD'


        //Admin Menu
        //add_action('admin_menu', [$this, 'customizeMenus']); //If you want to add a link to your plugin in the admin menu, uncomment this then modify the customizeMenus method
EOD;
    }

    public function getHooksCallablesContent()
    {
        return <<<'EOD'
/**
     * Add plugin menu entry in admin menu
     * @see https://developer.wordpress.org/reference/functions/add_menu_page/
     */
    public function customizeMenus()
    {
        //Get admin controller
        $adminController = $this->manager->getController(AbstractManager::ADMIN_CONTROLLER_TYPE);
        $callable        = [$adminController, 'route'];

        //Add entry in admin menu
        add_menu_page('__PLUGIN_NAME__', '__PLUGIN_NAME__', $this->manager->getConfig('plugin.capability'), WWP_PLUGIN___PLUGIN_CONST___NAME, $callable);
    }
EOD;
    }

    public function getHooksClassAttributes()
    {
        return '';
    }
}
