<?php

namespace WonderWp\Plugin\Generator\Service;

use WonderWp\Component\Task\TaskServiceInterface;
use WonderWp\Plugin\Generator\Command\PluginGeneratorCommand;
use WonderWp\Plugin\Generator\Command\ThemeGeneratorCommand;
use WP_CLI;

class GeneratorCommandService implements TaskServiceInterface
{

    public function register()
    {
        if (!class_exists('WP_CLI')) {
            return;
        }

        WP_CLI::add_command(PluginGeneratorCommand::COMMAND_NAME, PluginGeneratorCommand::class, PluginGeneratorCommand::getArgsDefinition());
        WP_CLI::add_command(ThemeGeneratorCommand::COMMAND_NAME, ThemeGeneratorCommand::class, ThemeGeneratorCommand::getArgsDefinition());
    }
}
