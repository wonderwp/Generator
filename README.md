# WonderWp Generator

A WonderWp package that acts as a plugin generator to speed up your plugin developments.

## Documentation

Documentation available here : http://wonderwp.net/Creating_a_plugin/Generator.html

## Summary

### Plugins

#### Generating a classic plugin

`vendor/bin/wp generate-plugin --name="myPluginName" --desc="This is my plugin description" --namespace="WonderWp\Plugin\MyPluginNameSpace"`

#### Generating a Custom Post Type Plugin

`vendor/bin/wp generate-plugin --name="myPluginCPTName" --desc="This is my CPT plugin description" --namespace="WonderWp\Plugin\MyPluginCPTNameSpace" --output_type="CPT"`

### Themes

### Generating a classic theme

`vendor/bin/wp generate-theme --name="myTheme" --namespace="WonderWp\Theme\MyTheme"`

- Generating a block theme

`vendor/bin/wp generate-theme --name="myBlockTheme" --namespace="WonderWp\Theme\MyBlockTheme" --output_type="block"` 