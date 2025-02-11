<?php

namespace __PLUGIN_NS__;

use __PLUGIN_NS__\Service\__PLUGIN_ENTITY__Activator;
use __PLUGIN_PARENT_MANAGER_NAMESPACE__;
use WonderWp\Component\DependencyInjection\Container;
use WonderWp\Component\Service\ServiceInterface;
//__MANAGER_EXTRA_USES//

/**
 * The manager is the file that registers everything your plugin is going to use / need.
 * It's the most important file for your plugin, the one that bootstraps everything.
 * The manager registers itself with the DI container, so you can retrieve it somewhere else and use its config / controllers / services
 * @see http://wonderwp.net/Creating_a_plugin/Plugin_architecture/Plugin_Manager
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
        $this->setConfig('path.url', plugin_dir_url(dirname(__FILE__)));
        $this->setConfig('textDomain', WWP___PLUGIN_CONST___TEXTDOMAIN);
        //__MANAGER_EXTRA_CONFIG__//

        // Register Controllers
        //__MANAGER_EXTRA_CONTROLLERS__//

        // Register Services
        //__MANAGER_EXTRA_SERVICES__//

        return $this;
    }
}
