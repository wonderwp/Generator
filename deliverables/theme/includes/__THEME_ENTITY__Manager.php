<?php

namespace __THEME_NS__;

use WonderWp\Component\DependencyInjection\Container;

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
        parent::register($container);

        return $this;
    }
}