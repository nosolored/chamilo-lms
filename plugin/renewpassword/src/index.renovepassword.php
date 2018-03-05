<?php
/* For license terms, see /license.txt */
/**
 * Index of the Renew Password plugin courses list
 * @package chamilo.plugin.renewpassword
 */
/**
 *
 */
 
$plugin = RenewPasswordPlugin::create();
$enable = $plugin->get('tool_enable') == 'true';
$title = $plugin->get_lang('RenewPasswordMenu');
$pluginPath = api_get_path(WEB_PLUGIN_PATH).'renewpassword/src/';
if (api_is_platform_admin() && $enable) {
	echo '<div class="well sidebar-nav static">';
	echo '<h4>'.$title.'</h4>';
	echo '<ul class="nav nav-list">';
		echo '<li>';
		echo '<a href="'.$pluginPath.'cron_renew_password.php" target="_blank">'.$plugin->get_lang('RenewPassword').'</a>';
		echo '</li>';
	echo '</ul>';
	echo '</div>';
}
