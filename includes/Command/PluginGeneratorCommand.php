<?php

namespace WonderWp\Plugin\Generator\Command;

use WonderWp\Component\DependencyInjection\Container;
use WonderWp\Plugin\Generator\GeneratorManager;
use WonderWp\Plugin\Generator\Service\GeneratorService;

class PluginGeneratorCommand
{
    public function __invoke($args, $assocArgs)
    {
        $container = Container::getInstance();
        /** @var GeneratorManager $manager */
        $manager = $container[WWP_PLUGIN_GENERATOR_NAME . '.Manager'];

        /** @var GeneratorService $generator */
        $generator = $manager->getService('generator');

        if (!empty($assocArgs)) {
            $generator->setData($assocArgs);
            $generator->generate();
        }

    }
}
