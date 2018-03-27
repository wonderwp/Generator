<?php

namespace WonderWp\Plugin\Generator\Service;

use WonderWp\Component\Task\TaskServiceInterface;
use WonderWp\Plugin\Generator\Command\PluginGeneratorCommand;

class GeneratorCommandService implements TaskServiceInterface
{
    /** @inheritdoc */
    public function registerCommands()
    {
        if (!class_exists('WP_CLI')) {
            return;
        }

        \WP_CLI::add_command('generateEntity', PluginGeneratorCommand::class);
    }
}
