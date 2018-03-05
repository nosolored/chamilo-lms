<?php
/**
 * List of courses
 * @package chamilo.plugin.renewpassword
 */
require_once '../config.php';

$course_plugin = 'renewpassword';
$plugin = RenewPasswordPlugin::create();

$enable = $plugin->get('tool_enable') == 'true';

$days = intval($plugin->get('validity_days'));
if ($enable) {
    $interbreadcrumb[] = array("url" => "/admin/index.php", "name" => get_lang('Administration'));
    $nameTools = $plugin->get_lang('plugin_title');
    Display::display_header($nameTools);
    echo Display::page_header($plugin->get_lang('title_page'));
    
    $tableUser = Database::get_main_table(TABLE_MAIN_USER);
    $tableExtraField = Database::get_main_table(TABLE_EXTRA_FIELD);
    $tableExtraFieldValues = Database::get_main_table(TABLE_EXTRA_FIELD_VALUES);
    
    $sql = "SELECT id FROM $tableExtraField WHERE variable='expired_date';";
    $res = Database::query($sql);
    if (!$res) {
        echo $plugin->get_lang('DB_error');
        exit;
    }
    $tmp = Database::fetch_assoc($res);
    $fieldId = $tmp['id'];
    $count = 0;

    $sql = "SELECT * FROM $tableUser;";
    $res = Database::query($sql);
    $aux = array();
    while ($row = Database::fetch_assoc($res)) {
        $userInfo = api_get_user_info($row['user_id']);
        $sql = "SELECT * 
                FROM $tableExtraFieldValues 
                WHERE 
                    item_id='".$row['user_id']."' 
                    AND field_id='".$fieldId."'
                ";
        $res_tmp = Database::query($sql);
        if (Database::num_rows($res_tmp) > 0) {
            // Check
            $aux = Database::fetch_assoc($res_tmp);
            $expiredDate = $aux['value'];
            if (trim($expiredDate) == "" || $expiredDate == "0000-00-00") {
                // No value
                $registrationDate = $row['registration_date'];
                $date_tmp = date("Y-m-d H:i:s",strtotime($registrationDate." +".$days." DAYS"));
                if (strtotime($date_tmp) < time()) {
                    echo $plugin->get_lang('ExpiredAccount')." - ".$userInfo['firstname']." ".$userInfo['lastname']." <ID ".$row['user_id'].">: ".$date_tmp."<br>";    
                    $date_tmp = date("Y-m-d",time()+($days*24*3600));
                    updatePassword($row['user_id']);
                    $count++;
                }
                // Save date
                $sql = "UPDATE $tableExtraFieldValues 
                        SET value='".$date_tmp."' 
                        WHERE item_id='".$row['user_id']."' 
                        AND field_id='".$fieldId."';";
                Database::query($sql);
            } else {
                // Check time saved
                $date_tmp = date("Y-m-d H:i:s",strtotime($expiredDate));
                if (strtotime($date_tmp) < time()) {
                    echo $plugin->get_lang('ExpiredAccount')." - ".$userInfo['firstname']." ".$userInfo['lastname']." <ID ".$row['user_id'].">: ".$date_tmp."<br>";    
                    $date_tmp = date("Y-m-d",time()+($days*24*3600));
                    updatePassword($row['user_id']);
                    $sql = "UPDATE $tableExtraFieldValues SET value='".$date_tmp."' WHERE item_id='".$row['user_id']."' AND field_id='".$fieldId."';";
                    Database::query($sql);
                    $count++;
                }
            }
        } else {
            // Check time saved
            $registrationDate = $row['registration_date'];
            $date_tmp = date("Y-m-d H:i:s",strtotime($registrationDate." +".$days." DAYS"));
            if (strtotime($date_tmp) < time()) {
                echo $plugin->get_lang('ExpiredAccount')." - ".$userInfo['firstname']." ".$userInfo['lastname']." <ID ".$row['user_id'].">: ".$date_tmp."<br>";
                $date_tmp = date("Y-m-d",time()+($days*24*3600));
                updatePassword($row['user_id']);
            } else {
                echo  $plugin->get_lang('ActiveAccount')." - ".$userInfo['firstname']." ".$userInfo['lastname']." <ID ".$row['user_id']."> ".$plugin->get_lang('UpdateExpirationTime').": ".$date_tmp ."<br>";
            }
            
            $sql = "INSERT INTO $tableExtraFieldValues 
                        (value,item_id,field_id,created_at, updated_at) 
                    VALUES
                        ('".$date_tmp."','".$row['user_id']."','".$fieldId."','".date("Y-m-d H:i:s")."','".date("Y-m-d H:i:s")."')
                    ";
            echo $sql;
            Database::query($sql);
            $count++;
        }
    }
    if ($count == 0) {
        echo Display::return_message($plugin->get_lang('NoUpdatePassword'), 'info');
    }
    Display::display_footer();
} else {
    echo $plugin->get_lang('message_not_enabled');    
}
