<?php

namespace WonderWp\Plugin\Generator\Generator\Plugin\CPT;

use Exception;
use WonderWp\Component\PluginSkeleton\AbstractManager;
use WonderWp\Plugin\Generator\Result\GenerationResult;
use WonderWp\Plugin\Generator\Generator\Plugin\Base\BaseGenerator;
use WonderWp\Plugin\Generator\Generator\Plugin\CPT\ContentProvider\CptHookServiceContentProvider;
use WonderWp\Plugin\Generator\Generator\Plugin\CPT\ContentProvider\CptManagerContentProvider;
use WonderWp\Plugin\Generator\Generator\Plugin\CPT\ContentProvider\CptPublicControllerContentProvider;
use function WonderWp\Functions\array_merge_recursive_distinct;

class CPTGenerator extends BaseGenerator
{

    /** @var CptPublicControllerContentProvider */
    protected $publicControllerContentProvider;

    /**
     * @inheritDoc
     */
    public function __construct(AbstractManager $manager)
    {
        parent::__construct($manager);
        $this->managerContentProvider          = new CptManagerContentProvider();
        $this->hookServiceContentProvider      = new CptHookServiceContentProvider();
        $this->publicControllerContentProvider = new CptPublicControllerContentProvider();
    }

    /** @inheritDoc */
    public function generate(): GenerationResult
    {
        try {
            $result = parent::generate();
            $this
                ->generateCPT()
                ->generateRepository()
                ->generatePublicController()
                ->generateLanguages()
            ;
        } catch (Exception $e) {
            return new GenerationResult(500, ['msg' => $e->getMessage(), 'data' => $e->getTrace()]);
        }

        return $result;
    }

    protected function generateCpt($givenReplacements = [])
    {
        $baseReplacements = [];
        $this->importDeliverable('includes' . DIRECTORY_SEPARATOR . 'CPT' . DIRECTORY_SEPARATOR . '__PLUGIN_ENTITY__CPT.php', array_merge_recursive_distinct($baseReplacements, $givenReplacements));

        return $this;
    }

    protected function generatePublicController($givenReplacements = [])
    {
        $baseReplacements = [
            '__PLUGIN_PARENT_CONTROLLER_NAMESPACE__' => 'WonderWp\Component\CPT\CustomPostTypePublicController',
            '__PLUGIN_PARENT_CONTROLLER__'           => 'CustomPostTypePublicController',
            '//__PLUGIN_EXTRA_USES//'                => $this->replacePlaceholders($this->publicControllerContentProvider->getUsesContent()),
            '//__PLUGIN_DEFAULT_ACTION__//'          => $this->replacePlaceholders($this->publicControllerContentProvider->getDefaultActionContent()),
        ];

        $this->importDeliverable('includes' . DIRECTORY_SEPARATOR . 'Controller' . DIRECTORY_SEPARATOR . '__PLUGIN_ENTITY__PublicController.php', array_merge_recursive_distinct($baseReplacements, $givenReplacements));

        return $this;
    }

    protected function createBaseFolders(array $folders = [])
    {
        $folders['base']   = WP_PLUGIN_DIR . '/' . sanitize_title($this->data['name']);
        $folders['public'] = $folders['base'] . '/public';
        $folders['views']  = $folders['public'] . '/views';

        return parent::createBaseFolders($folders);
    }

    protected function generateRepository()
    {
        $this->importDeliverable('includes' . DIRECTORY_SEPARATOR . 'Repository' . DIRECTORY_SEPARATOR . '__PLUGIN_ENTITY__Repository.php');

        return $this;
    }

    protected function generateLanguages()
    {
        $pot = <<<'EOD'
msgid ""
msgstr ""

EOD;

        $potFile = $this->folders['languages'] . DIRECTORY_SEPARATOR . sanitize_title($this->data['name']) . '.pot';
        $this->fileSystem->put_contents($potFile, $pot, FS_CHMOD_FILE);

        return $this;
    }

}
