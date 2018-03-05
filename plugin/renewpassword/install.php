<?php
/* For licensing terms, see /license.txt */
/**
 * Config the plugin
 * @package chamilo.plugin.renewpassword
 */
require_once __DIR__.'/config.php';

if (!api_is_platform_admin()) {
    die('You must have admin permissions to install plugins');
}

RenewPasswordPlugin::create()->install();

$fieldlabel = 'expired_date';
$fieldtype = '6';
$fieldtitle = 'Fecha válidez claves';
$fielddefault = '';
$fieldoptions = '';
$field_id = UserManager::create_extra_field($fieldlabel,$fieldtype,$fieldtitle,$fielddefault,$fieldoptions);
