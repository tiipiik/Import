<?php namespace Tiipiik\Import;

use Backend;
use System\Classes\PluginBase;

/**
 * Import Plugin Information File
 */
class Plugin extends PluginBase
{

    /**
     * Returns information about this plugin.
     *
     * @return array
     */
    public function pluginDetails()
    {
        return [
            'name'        => 'Import',
            'description' => 'Import datas from csv files',
            'author'      => 'Tiipiik',
            'icon'        => 'icon-magic'
        ];
    }

    public function registerSettings()
    {
        return [
            'import' => [
                'label' => 'Import',
                'icon' => 'icon-magic',
                'description' => 'Import datas from csv files',
                'url' => Backend::url('tiipiik/import/imports'),
                'order' => 100
            ]
        ];
    }

}
