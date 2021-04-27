<?php

namespace __PLUGIN_NS__\CPT;

use WonderWp\Component\CPT\CustomPostType;
use function WonderWp\Functions\array_merge_recursive_distinct;

class __PLUGIN_ENTITY__CPT extends CustomPostType
{
    public function __construct($name = '', array $passed_opts = [], $taxonomy_name = '', array $passed_taxonomy_opts = [])
    {
        $name        = WWP_PLUGIN___PLUGIN_CONST___NAME;
        $defaultOpts = [
            'labels'       => [
                'name'         => trad($name . '.cpt', WWP___PLUGIN_CONST___TEXTDOMAIN),
                'not_found'    => trad($name . '.not_found', WWP___PLUGIN_CONST___TEXTDOMAIN),
                'add_new_item' => trad($name . '.add_new_item', WWP___PLUGIN_CONST___TEXTDOMAIN),
                'edit_item'    => trad($name . '.edit_item', WWP___PLUGIN_CONST___TEXTDOMAIN),
            ],
            'show_in_rest' => true,
            'rewrite'      => ['slug' => __($name, WWP___PLUGIN_CONST___TEXTDOMAIN)],
        ];
        $opts        = array_merge_recursive_distinct($defaultOpts, $passed_opts);
        parent::__construct($name, $opts, $taxonomy_name, $passed_taxonomy_opts);
    }
}
