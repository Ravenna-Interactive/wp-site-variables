## Site Variables WordPress Plugin

Allows a multisite install to provide site defined variables that can be used
throughout the network sites.

### Installation

Download the zip, unzipped and upload the wp-site-variables directory to your
wp-content/plugins directory for your WordPress installation. After the plugin
is uploaded go to the plugin management page and click on "Network Activate".

After installing he Network Admin can define a list of variables that can be
configured for each site. This can be configured by using the __Super Admin ->
Site Variables__ menu.

Once variables are defined. Site admins can configure the values for these
variables by using the "Settings -> Site Variables" menu item.

Use this template tag to use the variables in your theme:

    <?php multivars_value('Variable Name'); ?>

Where the _'Variable Name'_ is the name of the variable you would like to
output

Full method signature:

    <?php multivars_value( $variable_name, $default_value = '', $echo = true ); ?>

__Arguments__:

1. `$variable_name`: _`String`_ (_Required_) - Variable name as defined in the options panel.
2. `$default_value`: _`String`_ - If a value has not been
defined for the give `$variable_name` (default: `''`)
3. `$echo`: _`Boolean`_ - Outputs the returned value to the template (default: _true_)

__Returns__:

(_`String`_) The value provided by the site configuration for the given
`$variable_name`. If no `$default` is provided then returns an empty
_`String`_.
