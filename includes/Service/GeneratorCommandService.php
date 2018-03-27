<?php

namespace WonderWp\Plugin\Generator\Service;

use WonderWp\Component\Task\TaskServiceInterface;
use WonderWp\Plugin\Generator\Command\PluginGeneratorCommand;

class GeneratorCommandService implements TaskServiceInterface
{
    const COMMAND_NAME = 'generate-plugin';

    /** @inheritdoc */
    public function registerCommands()
    {
        if (!class_exists('WP_CLI')) {
            return;
        }

        \WP_CLI::add_command(self::COMMAND_NAME, PluginGeneratorCommand::class);
    }
}
