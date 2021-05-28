<?php

namespace WonderWp\Plugin\Generator\Service\Generator\Base\ContentProvider;

class BaseActivatorContentProvider
{
    public function getUsesContent()
    {
        return '';
    }

    /**
     * @return string
     */
    public function getActivationTasksContent()
    {
        return <<<'EOD'
        //Create here the tasks you'd like to perform upon your plugin activation
EOD;
    }
}
