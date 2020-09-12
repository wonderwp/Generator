<?php

namespace __PLUGIN_NS__;

use __PLUGIN_NS__\Service\__PLUGIN_ENTITY__Activator;
use WonderWp\Component\PluginSkeleton\AbstractManager;
use __PLUGIN_PARENT_MANAGER_NAMESPACE__;
use WonderWp\Component\DependencyInjection\Container;
use WonderWp\Component\Service\ServiceInterface;
use __PLUGIN_NS__\Controller\__PLUGIN_ENTITY__AdminController;
use __PLUGIN_NS__\Service\__PLUGIN_ENTITY__HookService;
//__PLUGIN_EXTRA_USES//

/**
 * The manager is the file that registers everything your plugin is going to use / need.
 * It's the most important file for your plugin, the one that bootstraps everything.
 * The manager registers itself with the DI container, so you can retrieve it somewhere else and use its config / controllers / services
 */
class __PLUGIN_ENTITY__Manager extends __PLUGIN_PARENT_MANAGER__
{
    /**
     * Registers config, controllers, services etc usable by the plugin components
     *
     * @param Container $container
     *
     * @return $this
     */
    public function register(Container $container)
    {
        parent::register($container);

        // Register Config
        $this->setConfig('path.root', plugin_dir_path(dirname(__FILE__)));
        $this->setConfig('path.base', dirname(dirname(plugin_basename(__FILE__))));
        $this->setConfig('path.url', plugin_dir_url(dirname(__FILE__)));//__PLUGIN_ENTITY_CONFIG__//
        $this->setConfig('textDomain', WWP___PLUGIN_CONST___TEXTDOMAIN);

        // Register Controllers
        $this->addController(AbstractManager::ADMIN_CONTROLLER_TYPE, function () {
            return new __PLUGIN_ENTITY__AdminController($this);
        });
        /* Uncomment this if your plugin has a public controller
        $this->addController(AbstractManager::PUBLIC_CONTROLLER_TYPE, function () {
            return $plugin_public = new __PLUGIN_ENTITY__PublicController($this);
        });
        */

        // Register Services
        $this->addService(ServiceInterface::HOOK_SERVICE_NAME, function () {
            // Hook service
            return new __PLUGIN_ENTITY__HookService();
        });//__PLUGIN_ENTITY_SERVICES__//

        /* Uncomment this if your plugin has assets, then create the __PLUGIN_ENTITY__AssetService class in the include folder
        $this->addService(ServiceInterface::ASSETS_SERVICE_NAME, function () {
            // Asset service
            return new __PLUGIN_ENTITY__AssetService();
        });
        */
        /* Uncomment this if your plugin has particular routes, then create the __PLUGIN_ENTITY__RouteService class in the include folder
        $this->addService(ServiceInterface::ROUTE_SERVICE_NAME, function () {
            // Route service
            return new __PLUGIN_ENTITY__RouteService();
        });
        */
        /* Uncomment this if your plugin has page settings, then create the __PLUGIN_ENTITY__PageSettingsService class in the include folder
        $this->addService(ServiceInterface::PAGE_SETTINGS_SERVICE_NAME, function () {
            // Page settings service
            return new __PLUGIN_ENTITY__PageSettingsService();
        });
        */

        /* Uncomment this if your plugin has an api, then create the __PLUGIN_ENTITY__ApiService class in the include folder
        $this->addService(ServiceInterface::API_SERVICE_NAME, function () {
            //  Api service
            return new __PLUGIN_ENTITY__ApiService();
        });
        */

        return $this;
    }
}
