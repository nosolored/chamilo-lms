<?php

$cidReset = true;
require_once 'main/inc/global.inc.php';

api_block_anonymous_users();

if (!api_is_teacher()) {
    api_not_allowed(true);
}

$codePath = api_get_path(WEB_CODE_PATH);
$coursePath = api_get_path(WEB_COURSE_PATH);
$pluginPath = api_get_path(WEB_PLUGIN_PATH);
$toolName = get_lang('Groups');
$userId = api_get_user_id();

$session_table = Database::get_main_table(TABLE_MAIN_SESSION);
$session_rel_course_rel_user_table = Database::get_main_table(TABLE_MAIN_SESSION_COURSE_USER);
$sessionIsCoach = [];
$sql = "SELECT DISTINCT s.id, name, access_start_date, access_end_date
        FROM $session_table s
        INNER JOIN $session_rel_course_rel_user_table session_rc_ru
        ON session_rc_ru.session_id = s.id AND session_rc_ru.user_id = '".$userId."'
        WHERE session_rc_ru.status = 2
        ORDER BY name ASC";
$result = Database::query($sql);
$sessionIsCoach = Database::store_result($result);

Display::display_header($toolName);
echo Display::page_header($toolName);
echo '<div class="row">';
echo '<div class="col-md-12">';
if (count($sessionIsCoach) > 0) {
    echo '<table class="table data_table">';
    foreach ($sessionIsCoach as $sessionItem) {
        $sessionInfo = api_get_session_info($sessionItem['id']);
        echo '<tr>';
        echo '<td>';
        echo '<a title="'.htmlspecialchars($sessionItem['name']).'" href="'.$codePath.'session/index.php?session_id='.$sessionItem['id'].'">';
        echo '<span style="font-weight: bold; font-size: 16px;">'.htmlspecialchars($sessionItem['name']).'</span>';
        echo '</a>';
        echo '</td>';
        echo '<td style="font-size:14px; ">';
        $newDates = SessionManager::convert_dates_to_local($sessionInfo, false);
        echo date("d/m/Y", strtotime($newDates['access_start_date'])).'<br>';
        if (!empty($newDates['access_end_date'])) {
            echo date("d/m/Y", strtotime($newDates['access_end_date']));
        }
        echo '</td>';
        echo '<td class="text-right" style="vertical-align:middle">';
        $actions = Display::url(
            Display::return_icon('2rightarrow.png', get_lang('Details')),
            $codePath.'session/index.php?session_id='.$sessionItem['id']
        );
        echo $actions;
        echo '</td>';
        echo '</tr>';
    }
    echo '</table>';
}

Display::display_footer();
