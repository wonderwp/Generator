<?php

namespace __PLUGIN_NS__\ListTable;

use WonderWp\Plugin\Core\Framework\PluginSkeleton\DoctrineListTable;

class __PLUGIN_ENTITY__ListTable extends DoctrineListTable
{
    /** @inheritdoc */
    public function get_columns()
    {
        $cols = parent::get_columns();

        /*
        foreach(array() as $col) {
            unset($cols[$col]);
        }
        */

        return $cols;
    }
}
