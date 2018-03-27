<?php

namespace WonderWp\Plugin\Generator\Command;

class PluginGeneratorCommand
{
    public function __invoke($args)
    {
        if(!empty($args)){
            foreach($args as $key=>$val){
                \WP_CLI::line("$key : $val");
            }
        }
    }
}
