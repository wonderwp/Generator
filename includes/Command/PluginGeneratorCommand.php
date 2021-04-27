<?php

namespace WonderWp\Plugin\Generator\Command;

use WonderWp\Component\DependencyInjection\Container;
use WonderWp\Component\Logging\LoggerInterface;
use WonderWp\Component\Logging\WpCliLogger;
use WonderWp\Component\PluginSkeleton\Exception\ServiceNotFoundException;
use WonderWp\Plugin\Generator\GeneratorManager;
use WonderWp\Plugin\Generator\Service\Generator\GeneratorInterface;

class PluginGeneratorCommand
{
    protected static $managerReference = WWP_PLUGIN_GENERATOR_NAME . '.Manager';
    /** @var LoggerInterface */
    protected $logger;

    public function __invoke($args, $assocArgs)
    {
        $container = Container::getInstance();
        /** @var GeneratorManager $manager */
        $manager      = $container[static::$managerReference];
        $this->logger = new WpCliLogger();

        /** @var GeneratorInterface $generator */
        $type = !empty($assocArgs['output_type']) ? $assocArgs['output_type'] : '';
        if (!empty($type)) {
            try {
                $generator = $manager->getService($type . 'Generator');
            } catch (ServiceNotFoundException $e) {
                $this->logger->error($e->getMessage());

                return false;
            }
        } else {
            $generator = $manager->getService('generator');
        }

        $generator->setLogger($this->logger);

        if (!empty($assocArgs)) {
            $this->logger->info("Starting Generation of the " . $assocArgs['name'] . " plugin.");
            $generator->setData($assocArgs);
            $generationResult = $generator->generate();
            if ($generationResult->getCode() === 200) {
                $this->logger->success($generationResult->getData('msg'));
            } else {
                $this->logger->error($generationResult->getData('msg'));
                $this->logger->debug($generationResult);
            }
        } else {
            $this->logger->error('No data given to the generator. Not enough context provided to generate a plugin');
        }

    }
}
