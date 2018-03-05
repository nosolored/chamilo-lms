<?php
/* For license terms, see /license.txt */
/**
 * Description of renew_password_plugin
 * @package chamilo.plugin.renewpassword
 * @author Jose Angel Ruiz    <jaruiz@nosolored.com>
 */
/**
 * Plugin class for the RenewPassword plugin
 */
class RenewPasswordPlugin extends Plugin
{
    public $isAdminPlugin = true;
	/**
     *
     * @return StaticPlugin
     */
    static function create()
    {
        static $result = null;
        return $result ? $result : $result = new self();
    }

    protected function __construct()
    {
        parent::__construct('1.0', 'Jose Angel Ruiz - NoSoloRed (original author)', array('tool_enable' => 'boolean', 'validity_days' => 'text'));
    }

    /**
     * This method creates the tables required to this plugin
     */
    public function install()
    {

    }

    /**
     * This method drops the plugin tables
     */
    public function uninstall()
    {

    }
}
