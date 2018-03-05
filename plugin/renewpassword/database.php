<?php
/* For license terms, see /license.txt */
/**
 * Plugin database installation script. Can only be executed if included
 * inside another script loading global.inc.php
 * @package chamilo.plugin.renewpassword
 */
/**
 * Check if script can be called
 */
if (!function_exists('api_get_path')) {
    die('This script must be loaded through the Chamilo plugin installer sequence');
}
/**
 * Create the script context, then execute database queries to enable
 */
