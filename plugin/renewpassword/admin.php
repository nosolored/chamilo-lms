<?php
require_once __DIR__.'/config.php';

$plugin = RenewPasswordPlugin::create();
$enable = $plugin->get('tool_enable') == 'true';
$pluginPath = api_get_path(WEB_PLUGIN_PATH).'renewpassword/src/cron_renew_password.php';

if (api_is_platform_admin() && $enable) {
    header('Location:'.$pluginPath);
    exit;
} else {
    header('Location: ../../index.php');
    exit;
}
