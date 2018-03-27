<?php

namespace WonderWp\Plugin\Generator;

use WonderWp\Component\DependencyInjection\Container;
use WonderWp\Component\PluginSkeleton\AbstractPluginManager;
use WonderWp\Component\Service\ServiceInterface;

class GeneratorManager extends AbstractPluginManager
{
    public function register(Container $container)
    {
        parent::register($container);

        $this->addService(ServiceInterface::COMMAND_SERVICE_NAME, function () {
            return new GeneratorCommandService();
        });

        return $this;
    }

}
