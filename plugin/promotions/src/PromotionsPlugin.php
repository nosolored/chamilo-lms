<?php

/* For licensing terms, see /license.txt */

/**
 * Promotions plugin.
 *
 * @author Jose Angel Ruiz
 */
class PromotionsPlugin extends Plugin
{
    const TABLE_PROMOTIONS = 'plugin_promotions';

    public $isAdminPlugin = true;

    /**
     * Class constructor.
     */
    protected function __construct()
    {
        $version = '1.0';
        $author = 'José Angel Ruiz (NOSOLORED)';
        parent::__construct($version, $author, ['tool_enable' => 'boolean']);
        $this->isAdminPlugin = true;
    }

    /**
     * @return RedirectionPlugin
     */
    public static function create()
    {
        static $result = null;

        return $result ? $result : $result = new self();
    }
    
    /**
     * Creates this plugin's related tables in the internal database.
     * Installs course fields in all courses.
     *
     * @throws ToolsException
     */
    public function install()
    {
        $table = Database::get_main_table(self::TABLE_PROMOTIONS);
        $sql = "CREATE TABLE IF NOT EXISTS $table (
                id INT unsigned NOT NULL auto_increment PRIMARY KEY,
                content TEXT NULL,
                user_id INT unsigned NOT NULL DEFAULT 0,
                is_delete INT unsigned NOT NULL DEFAULT 0,
                create_at DATETIME,
                ends_at DATETIME,
                delete_at DATETIME
                )";
        Database::query($sql);
    }
    
    /**
     * Uninstall
     */
    public function uninstall()
    {
        $em = Database::getManager();
        $sm = $em->getConnection()->getSchemaManager();
        if ($sm->tablesExist(self::TABLE_PROMOTIONS)) {
            Database::query("DROP TABLE IF EXISTS ".self::TABLE_PROMOTIONS);
        }
    }
    
    public function getPromotions() {
        $result = '';

        $promotionsData = Database::select(
            '*',
            PromotionsPlugin::TABLE_PROMOTIONS,
            [
                'where' => [
                    'is_delete = ?' => [0],
                ],
            ]
        );
        
        foreach ($promotionsData as $item) {
            $result .= '<div id="promo'.$item['id'].'" class="panel panel-default">';
            $result .= '<div class="panel-body">';
            $result .= $item['content'];
            $result .= '</div>';
            $result .= '</div>';
        }
        
        return $result;
    }
}
