<?php
/* For license terms, see /license.txt */
/**
 * Functions
 * @package chamilo.plugin.renewpassword
 */
 
/**
 * Update user pass
 * @return true or false
 */
function updatePassword($user_id)
{
    $table_user = Database :: get_main_table(TABLE_MAIN_USER);
    $sql = "SELECT 
                user_id AS uid,
                lastname AS lastName,
                firstname AS firstName,
                username AS loginName,
                password,
                 email,
                auth_source 
            FROM $table_user 
            WHERE user_id='".$user_id."'";
    $result = Database::query($sql);
    $user = Database::fetch_array($result);
    
    // Update pass
    $user['password'] = api_generate_password();
    UserManager::updatePassword($user_id, $user['password']);
    
    // Send mail with pass
    $plugin = RenewPasswordPlugin::create();
    $lang_subject = $plugin->get_lang('RenewPassword');
    $lang_intro = $plugin->get_lang('IntroEmail');
    
    $recipient_name = api_get_person_name($user['firstName'], $user['lastName'], null, PERSON_NAME_EMAIL_ADDRESS);
    $emailsubject = '['.api_get_setting('siteName').'] '.$lang_subject;
    $sender_name = api_get_person_name(api_get_setting('administratorName'), api_get_setting('administratorSurname'), null, PERSON_NAME_EMAIL_ADDRESS);
    $email_admin = api_get_setting('emailAdministrator');
    $emailbody = get_lang('Dear')." ".stripslashes(api_get_person_name($user['firstName'], $user['lastName'])).",<br><br>".$lang_intro." ".api_get_setting('siteName').".<br><br>".get_lang('Username')." : ".$user['loginName']."<br>".get_lang('Pass')." : ".stripslashes($user['password']);

    @api_mail_html($recipient_name, $user['email'], $emailsubject, $emailbody, $sender_name, $email_admin);
            
    echo get_lang('Updated')." - ".$user['firstName']." ".$user['lastName'].": ".$user['password']."<br><br>";
    return true;
}
