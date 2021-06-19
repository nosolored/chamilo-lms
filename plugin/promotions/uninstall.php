<?php
/* For license terms, see /license.txt */

if (!api_is_platform_admin()) {
    exit('You must have admin permissions to uninstall plugins');
}

require_once __DIR__.'/src/PromotionsPlugin.php';
PromotionsPlugin::create()->uninstall();
