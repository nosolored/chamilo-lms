<?php

/* For licensing terms, see /license.txt */

/**
 * Plugin.
 *
 * @author Jose Angel Ruiz
 */
require_once __DIR__.'/config.php';
require_once __DIR__.'/src/PromotionsPlugin.php';

$plugin_info = PromotionsPlugin::create()->get_info();
