<?php

namespace WonderWp\Plugin\Generator\Generator\Definition;

use WonderWp\Component\Logging\LoggerInterface;
use WonderWp\Plugin\Generator\Result\GenerationResult;

interface GeneratorInterface
{
    /**
     * Method that should generate the plugin
     * @return GenerationResult
     */
    public function generate(): GenerationResult;

    /**
     * Data setter
     *
     * @param array $data
     *
     * @return static
     */
    public function setData(array $data);

    /**
     * @param LoggerInterface $logger
     *
     * @return static
     */
    public function setLogger(LoggerInterface $logger);
}
