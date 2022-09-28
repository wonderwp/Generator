<?php

namespace WonderWp\Plugin\Generator\Generator\Definition;

use RuntimeException;
use WonderWp\Component\DependencyInjection\Container;
use WonderWp\Component\Logging\LoggerInterface;
use WonderWp\Component\Logging\VoidLogger;
use WonderWp\Component\PluginSkeleton\AbstractManager;
use WonderWp\Plugin\Generator\GeneratorManager;
use WP_Filesystem_Direct;

abstract class AbstractGenerator implements GeneratorInterface
{
    //Attributes
    /** @var string[] */
    protected $data;
    /** @var  WP_Filesystem_Direct */
    protected $fileSystem;
    /** @var LoggerInterface */
    protected $logger;
    /** @var array */
    protected $folders;
    /** @var Container */
    protected $container;
    /** @var GeneratorManager */
    protected $manager;

    /** Constructor */
    public function __construct(AbstractManager $manager)
    {
        $this->container  = Container::getInstance();
        $this->fileSystem = $this->container['wwp.fileSystem'];
        $this->manager    = $manager;
        $this->logger     = new VoidLogger();
    }

    /**
     * @return string[]
     */
    public function getData()
    {
        return $this->data;
    }

    /** @inheritDoc */
    public function setData(array $data)
    {
        $this->data = $data;

        return $this;
    }

    /** @inheritDoc */
    public function setLogger($logger)
    {
        $this->logger = $logger;

        return $this;
    }

    protected function prepareDatas()
    {
        if (empty($this->data['classprefix'])) {
            $frags                     = explode('\\', $this->data['namespace']);
            $this->data['classprefix'] = end($frags);
        }
        $this->data['className'] = $this->data['classprefix'];

        return $this;
    }

    /**
     * @param string $deliverable
     * @param array $replacements
     *
     * @return bool
     */
    protected function importDeliverable($deliverable, array $replacements = [], $type = 'plugin')
    {
        $src = implode(DIRECTORY_SEPARATOR, [$this->manager->getConfig('path.root'), 'deliverables', $type, $deliverable]);

        if (!$this->fileSystem->exists($src) || !$this->fileSystem->is_readable($src)) {
            throw new RuntimeException(sprintf('The deliverable file "%s" does not exist or is not readable', $src));
        }

        $content = $this->replacePlaceholders($this->fileSystem->get_contents($src), $replacements);
        $dst     = implode(DIRECTORY_SEPARATOR, [$this->folders['base'], $this->replacePlaceholders($deliverable)]);

        if (!$this->fileSystem->exists(dirname($dst))) {
            $this->fileSystem->mkdir(dirname($dst));
        }

        if ($this->fileSystem->exists($dst)) {
            $this->fileSystem->delete($dst);
        }

        return $this->fileSystem->put_contents($dst, $content, FS_CHMOD_FILE);
    }
}