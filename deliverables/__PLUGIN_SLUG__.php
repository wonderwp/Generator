<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @wordpress-plugin
 * __PLUGIN_METAS__
 */

use WonderWp\Component\PluginSkeleton\Service\ActivatorInterface;
use WonderWp\Component\PluginSkeleton\Service\DeactivatorInterface;
use WonderWp\Component\PluginSkeleton\ManagerInterface;
use WonderWp\Component\DependencyInjection\Container;
use WonderWp\Component\Service\ServiceInterface;
use __PLUGIN_NS__\__PLUGIN_ENTITY__Manager;

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

define('WWP_PLUGIN___PLUGIN_CONST___NAME', '__PLUGIN_SLUG__');
define('WWP_PLUGIN___PLUGIN_CONST___VERSION', '1.0.0');
define('WWP___PLUGIN_CONST___TEXTDOMAIN', '__PLUGIN_SLUG__');
if (!defined('WWP_PLUGIN___PLUGIN_CONST___MANAGER')) {
    define('WWP_PLUGIN___PLUGIN_CONST___MANAGER', __PLUGIN_ENTITY__Manager::class);
}

/**
 * Register activation hook
 * The code that runs during plugin activation.
 * This action is documented in includes/ErActivator.php
 */
register_activation_hook(__FILE__, function () {
    $activator = Container::getInstance()->offsetGet(WWP_PLUGIN___PLUGIN_CONST___NAME . '.Manager')->getService(ServiceInterface::ACTIVATOR_NAME);

    if ($activator instanceof ActivatorInterface) {
        $activator->activate();
    }
});

/**
 * Register deactivation hook
 * The code that runs during plugin deactivation.
 * This action is documented in includes/MembreDeactivator.php
 */
register_deactivation_hook(__FILE__, function () {
    $deactivator = Container::getInstance()->offsetExists(WWP_PLUGIN___PLUGIN_CONST___NAME . '.Manager') ? Container::getInstance()->offsetGet(WWP_PLUGIN___PLUGIN_CONST___NAME . '.Manager')->getService(ServiceInterface::DEACTIVATOR_NAME) : null;

    if ($deactivator instanceof DeactivatorInterface) {
        $deactivator->deactivate();
    }
});

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 * This class is called the manager
 * Instanciate here because it handles autoloading
 */
$plugin = WWP_PLUGIN___PLUGIN_CONST___MANAGER;
$plugin = new $plugin(WWP_PLUGIN___PLUGIN_CONST___NAME, WWP_PLUGIN___PLUGIN_CONST___VERSION);

if (!$plugin instanceof ManagerInterface) {
    throw new \BadMethodCallException(sprintf('Invalid manager class for %s plugin : %s', WWP_PLUGIN___PLUGIN_CONST___NAME, WWP_PLUGIN___PLUGIN_CONST___MANAGER));
}

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
$plugin->run();
