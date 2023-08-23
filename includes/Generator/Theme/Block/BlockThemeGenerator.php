<?php

namespace WonderWp\Plugin\Generator\Generator\Theme\Block;

use Exception;
use WonderWp\Plugin\Generator\Generator\Theme\Classic\ClassicThemeGenerator;
use WonderWp\Plugin\Generator\Result\GenerationResult;
use WP_Error;
use function WonderWp\Functions\array_merge_recursive_distinct;

class BlockThemeGenerator extends ClassicThemeGenerator
{
    public function generate(): GenerationResult
    {
        $check = $this->checkDatas();

        if ($check->getCode() === 200) {
            try {
                $this
                    ->prepareDatas()
                    ->createBaseFolders()
                    ->generateIndexFile()
                    ->generateCssFile()
                    ->generateIndexhtmlFile()
                    ->generateHeaderFile()
                    ->generateFooterFile()
                ;
            } catch (Exception $e) {
                return new GenerationResult(500, ['msg' => $e->getMessage(), 'exception' => $e]);
            }

            return new GenerationResult(200, [
                'msg' => "
Theme generated in your themes folder.
Don't forget to add an autoload entry in your composer.json file, then to launch a composer dump-autoload command, if you'd like to use it right away.
More information in the documentation : http://wonderwp.net/Creating_a_theme/Getting_Started
",
            ]);
        } else {
            $errors = $check->getData('errors');

            return new GenerationResult(500, ['msg' => implode("\n", $errors), 'errors' => $errors]);
        }
    }

    protected function createBaseFolders(array $folders = [])
    {
        $this->folders                   = $folders;
        $this->folders['base']           = WP_CONTENT_DIR . '/themes/' . sanitize_title($this->data['name']);
        //$this->folders['assets']         = $this->folders['base'] . '/assets';
        //$this->folders['includes']       = $this->folders['base'] . '/includes';
        //$this->folders['services']       = $this->folders['includes'] . '/Service';
        $this->folders['parts']          = $this->folders['base'] . '/parts';
        $this->folders['templates']      = $this->folders['base'] . '/templates';
        $this->folders['languages']      = $this->folders['base'] . '/languages';
        $errors                          = [];

        foreach ($this->folders as $folder) {
            if (!is_dir($folder)) {
                if (!$this->fileSystem->mkdir($folder, FS_CHMOD_DIR)) {
                    $errors[] = new WP_Error(500, 'Required folder creation failed: ' . $folder);
                }
            }
        }

        if (!empty($errors)) {
            throw new Exception('Theme base folders generation error : ' . implode("\n", $errors));
        }

        return $this;
    }

    protected function generateIndexHtmlFile()
    {
        $this->importDeliverable('templates' . DIRECTORY_SEPARATOR. 'index.html', [], 'theme');

        return $this;
    }
}    protected function generateHeaderFile(array $givenReplacements = [])

    protected function generateHeaderFile(array $givenReplacements = [])
    {
        $this->importDeliverable('parts' . DIRECTORY_SEPARATOR. 'header.html', [], 'theme');

        return $this;
    }

    protected function generateFooterFile(array $givenReplacements = [])
    {
        $this->importDeliverable('parts' . DIRECTORY_SEPARATOR. 'footer.html', [], 'theme');

        return $this;
    }
}
