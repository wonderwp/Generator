<?php
/**
 * Plugin Name:       wwp Generator
 * Plugin URI:        https://wonderwp.com
 * Description:       wonderwp plugin generator
 * Version:           1.0.0
 * Author:            Jeremy Desvaux
 * Author URI:        http://jdmweb.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wonderwp_generator
 * Domain Path:       /languages
 */

use WonderWp\Component\PluginSkeleton\Service\ActivatorInterface;
use WonderWp\Component\PluginSkeleton\Service\DeactivatorInterface;
use WonderWp\Component\PluginSkeleton\ManagerInterface;
use WonderWp\Component\DependencyInjection\Container;
use WonderWp\Component\Service\ServiceInterface;
use WonderWp\Plugin\Generator\GeneratorManager;

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

define('WWP_PLUGIN_GENERATOR_NAME', 'wonderwp_generator');
define('WWP_PLUGIN_GENERATOR_VERSION', '1.0.0');
define('WWP_GENERATOR_TEXTDOMAIN', 'wonderwp_generator');
if (!defined('WWP_PLUGIN_GENERATOR_MANAGER')) {
    define('WWP_PLUGIN_GENERATOR_MANAGER', GeneratorManager::class);
}

/**
 * Register activation hook
 * The code that runs during plugin activation.
 * This action is documented in includes/ErActivator.php
 */
register_activation_hook(__FILE__, function () {
    $activator = Container::getInstance()->offsetGet(WWP_PLUGIN_GENERATOR_NAME . '.Manager')->getService(ServiceInterface::ACTIVATOR_NAME);

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
    $deactivator = Container::getInstance()->offsetExists(WWP_PLUGIN_GENERATOR_NAME . '.Manager') ? Container::getInstance()->offsetGet(WWP_PLUGIN_GENERATOR_NAME . '.Manager')->getService(ServiceInterface::DEACTIVATOR_NAME) : null;

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
$plugin = WWP_PLUGIN_GENERATOR_MANAGER;
$plugin = new $plugin(WWP_PLUGIN_GENERATOR_NAME, WWP_PLUGIN_GENERATOR_VERSION);

if (!$plugin instanceof ManagerInterface) {
    throw new \BadMethodCallException(sprintf('Invalid manager class for %s plugin : %s', WWP_PLUGIN_GENERATOR_NAME, WWP_PLUGIN_GENERATOR_MANAGER));
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
