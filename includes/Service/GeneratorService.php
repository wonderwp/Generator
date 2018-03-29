<?php

namespace WonderWp\Plugin\Generator\Service;

use WonderWp\Component\DependencyInjection\Container;
use WonderWp\Component\HttpFoundation\Result;
use WonderWp\Plugin\Generator\GeneratorManager;

class GeneratorService
{

    /** @var string[] */
    protected $data;
    /** @var Container */
    protected $container;
    /** @var GeneratorManager */
    protected $manager;
    /** @var  \WP_Filesystem_Direct */
    protected $fileSystem;
    /** @var array */
    protected $folders;

    /**
     * @return string[]
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param string[] $data
     *
     * @return static
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    public function generate()
    {
        $this->container  = Container::getInstance();
        $this->fileSystem = $this->container['wwp.fileSystem'];
        $this->manager    = $this->container['wonderwp_generator.Manager'];

        $check = $this->checkDatas();
        if ($check->getCode() === 200) {
            $msg = "Starting Generation of the " . $this->data['name'] . " plugin.";
            \WP_CLI::line($msg);

            try {
                $this
                    ->prepareDatas()
                    ->createBaseFolders()
                    ->generateIndexFile()
                    ->generateBootstrapFile()//✓
                    ->generateManager()//✓
                    ->generateActivator()//✓
                    ->generateDeActivator()//
                    ->generateHookService()//✓
                    ->generateAdminController()//✓
                ;
            } catch (\Exception $e) {
                \WP_CLI::error($e->getMessage(), true);
            }

            \WP_CLI::success('Plugin generation finished');
        } else {
            \WP_CLI::error(implode("\n", $check->getData('errors')), true);
        }
    }

    protected function checkDatas()
    {
        $requiredDatas = ['name', 'desc', 'namespace'];
        $errors        = [];
        foreach ($requiredDatas as $req) {
            if (empty($this->data[$req])) {
                $errors[$req] = 'Attribute ' . $req . ' is missing';
            }
        }
        $code = empty($errors) ? 200 : 403;

        return new Result($code, ['datas' => $this->data, 'errors' => $errors]);
    }

    protected function prepareDatas()
    {
        $this->data['className'] = $this->data['classprefix'];

        return $this;
    }

    protected function createBaseFolders()
    {
        $this->folders                = [];
        $this->folders['base']        = WP_PLUGIN_DIR . '/' . sanitize_title($this->data['name']);
        $this->folders['includes']    = $this->folders['base'] . '/includes';
        $this->folders['services']    = $this->folders['includes'] . '/Service';
        $this->folders['controllers'] = $this->folders['includes'] . '/Controller';
        $this->folders['languages']   = $this->folders['base'] . '/languages';
        $errors                       = [];

        foreach ($this->folders as $folder) {
            if (!is_dir($folder)) {
                if (!$this->fileSystem->mkdir($folder, FS_CHMOD_DIR, true)) {
                    $errors[] = new \WP_Error(500, 'Required folder creation failed: ' . $folder);
                }
            }
        }

        if (!empty($errors)) {
            throw new \Exception('Plugin generation error : ' . implode("\n", $errors));
        }

        return $this;
    }

    /**
     * @return self
     */
    protected function generateIndexFile()
    {
        $this->importDeliverable('index.php');

        return $this;
    }

    /**
     * @return self
     */
    protected function generateBootstrapFile()
    {
        $this->importDeliverable('__PLUGIN_SLUG__.php');

        return $this;
    }

    /**
     * @return self
     */
    protected function generateManager()
    {
        $replacements = [
            '//__PLUGIN_ENTITY_CONFIG__//'        => '',
            '//__PLUGIN_ENTITY_SERVICES__//'      => '',
            '__PLUGIN_PARENT_MANAGER_NAMESPACE__' => 'WonderWp\Component\PluginSkeleton\AbstractPluginManager',
            '__PLUGIN_PARENT_MANAGER__'           => 'AbstractPluginManager',
            '//__PLUGIN_EXTRA_USES//'             => '',
        ];

        $replacements['//__PLUGIN_ENTITY_SERVICES__//'] = $this->replacePlaceholders(<<<'EOD'

        $this->addService(ServiceInterface::ACTIVATOR_NAME, function () {
            //  Activator service
            return new __PLUGIN_ENTITY__Activator($this);
        });
        
EOD
        );

        $this->importDeliverable('includes' . DIRECTORY_SEPARATOR . '__PLUGIN_ENTITY__Manager.php', $replacements);

        return $this;
    }

    /**
     * @return self
     */
    protected function generateActivator()
    {
        $replacements = [
            '//__PLUGIN_ACTIVATION_TASKS__//' => <<<'EOD'
        $this->copyLanguageFiles(dirname(__DIR__) . '/languages');
EOD
            ,
        ];

        $replacements['__PLUGIN_PARENT_ACTIVATOR_NAMESPACE__'] = 'WonderWp\Component\PluginSkeleton\Service\AbstractPluginActivator';
        $replacements['__PLUGIN_PARENT_ACTIVATOR__']           = 'AbstractPluginActivator';

        $this->importDeliverable('includes' . DIRECTORY_SEPARATOR . 'Service' . DIRECTORY_SEPARATOR . '__PLUGIN_ENTITY__Activator.php', $replacements);

        return $this;
    }

    /**
     * @return self
     */
    protected function generateDeActivator()
    {
        // TODO
        return $this;
    }

    /**
     * @return self
     */
    protected function generateHookService()
    {
        $this->importDeliverable('includes' . DIRECTORY_SEPARATOR . 'Service' . DIRECTORY_SEPARATOR . '__PLUGIN_ENTITY__HookService.php');

        return $this;
    }

    /**
     * @return self
     */
    protected function generateAdminController()
    {
        $replacements = [
            '//__PLUGIN_DEFAULT_ACTION__//'          => '',
            '__PLUGIN_PARENT_CONTROLLER_NAMESPACE__' => 'WonderWp\Component\PluginSkeleton\Controller\AbstractPluginBackendController',
            '__PLUGIN_PARENT_CONTROLLER__'           => 'AbstractPluginBackendController',
        ];

        if (!empty($this->data['table'])) {
            $replacements['//__PLUGIN_DEFAULT_ACTION__//'] = <<<'EOD'

        public function defaultAction()
        {
        }
EOD;
        }

        $this->importDeliverable('includes' . DIRECTORY_SEPARATOR . 'Controller' . DIRECTORY_SEPARATOR . '__PLUGIN_ENTITY__AdminController.php', $replacements);

        return $this;
    }

    /**
     * @param string $deliverable
     * @param array  $replacements
     *
     * @return bool
     */
    protected function importDeliverable($deliverable, array $replacements = [])
    {
        $src = implode(DIRECTORY_SEPARATOR, [$this->manager->getConfig('path.root'), 'deliverables', $deliverable]);

        if (!$this->fileSystem->exists($src) || !$this->fileSystem->is_readable($src)) {
            throw new \RuntimeException(sprintf('The deliverable file "%s" does not exist or is not readable', $src));
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

    /**
     * @param string   $string
     * @param string[] $replacements
     *
     * @return string
     */
    protected function replacePlaceholders($string, array $replacements = [])
    {
        $replacements = array_merge([
            '__PLUGIN_NAME__'       => $this->data['name'],
            '__PLUGIN_SLUG__'       => sanitize_title($this->data['name']),
            '__PLUGIN_DESC__'       => $this->data['desc'],
            '__PLUGIN_CONST__'      => strtoupper($this->data['classprefix']),
            '__PLUGIN_CONST_LOW__'  => strtolower($this->data['classprefix']),
            '__PLUGIN_ENTITY__'     => $this->data['classprefix'],
            '__PLUGIN_NS__'         => $this->data['namespace'],
            '__PLUGIN_CLASSNAME__'  => $this->data['className'],
            '__ESCAPED_PLUGIN_NS__' => str_replace('\\', '\\\\', $this->data['namespace']),
        ], $replacements);

        return str_replace(array_keys($replacements), array_values($replacements), $string);
    }

}
