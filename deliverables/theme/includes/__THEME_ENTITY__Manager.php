<?php

namespace __THEME_NS__;

use WonderWp\Component\DependencyInjection\Container;
use __THEME_PARENT_MANAGER_NAMESPACE__;
use WonderWp\Component\Service\ServiceInterface;

/**
 * The manager is the file that registers everything your plugin is going to use / need.
 * It's the most important file for your plugin, the one that bootstraps everything.
 * The manager registers itself with the DI container, so you can retrieve it somewhere else and use its config / controllers / services
 * @see http://wonderwp.net/Creating_a_plugin/Plugin_architecture/Plugin_Manager
 */
class __THEME_ENTITY__Manager extends __THEME_PARENT_MANAGER__
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
        define('WWP_THEME_TEXTDOMAIN', '__THEME_TEXTDOMAIN__');
        parent::register($container);

        // Register Config
        $this->setConfig('path.root', get_stylesheet_directory());
        $this->setConfig('path.url', get_stylesheet_directory_uri());
        $this->setConfig('textDomain', WWP___THEME_CONST___TEXTDOMAIN);
        //__MANAGER_EXTRA_CONFIG__//
        return $this;
    }
}