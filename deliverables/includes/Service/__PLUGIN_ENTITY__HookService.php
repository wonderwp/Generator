<?php

namespace __PLUGIN_NS__\Service;

use WonderWp\Component\PluginSkeleton\AbstractManager;
use WonderWp\Component\DependencyInjection\Container;
use WonderWp\Component\Hook\AbstractHookService;

/**
 * Defines the different hooks that are going to be used by your plugin
 */
class __PLUGIN_ENTITY__HookService extends AbstractHookService
{
    /**
     * Run
     * @return $this
     */
    public function run()
    {
        // Get Manager
        $container     = Container::getInstance();
        $this->manager = $container->offsetGet('__PLUGIN_SLUG__.Manager');

        /**
         * Admin Hooks
         */
        //Menus
        add_action('admin_menu', [$this, 'customizeMenus']);

        //Translate
        add_action('plugins_loaded', [$this, 'loadTextdomain']);

        return $this;
    }

    /**
     * Add entry under top-level functionalities menu
     */
    public function customizeMenus()
    {
        //Get admin controller
        $adminController = $this->manager->getController(AbstractManager::ADMIN_CONTROLLER_TYPE);
        $callable        = [$adminController, 'route'];

        //Add entry under top-level functionalities menu
        add_submenu_page('wonderwp-modules', '__PLUGIN_NAME__', '__PLUGIN_NAME__', 'read', WWP_PLUGIN___PLUGIN_CONST___NAME, $callable);
    }

}
