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
$pluginZoom = ZoomPlugin::create();
$toolName = $pluginZoom->get_lang('ZoomVideoConferences');
$userId = api_get_user_id();

$session_table = Database::get_main_table(TABLE_MAIN_SESSION);
$session_rel_course_rel_user_table = Database::get_main_table(TABLE_MAIN_SESSION_COURSE_USER);
$videoconferenceList = [];
$sql = "SELECT session_rc_ru.*, s.name, z.start_time, z.meeting_id
        FROM $session_table s
        INNER JOIN $session_rel_course_rel_user_table session_rc_ru
        ON session_rc_ru.session_id = s.id AND session_rc_ru.user_id = '".$userId."'
        INNER JOIN plugin_zoom_meeting z ON (session_rc_ru.session_id = z.session_id AND session_rc_ru.c_id = z.course_id)
        WHERE session_rc_ru.status = 2
        ORDER BY start_time ASC";
$result = Database::query($sql);
$videoconferenceList = Database::store_result($result);

Display::display_header($toolName);
echo Display::page_header($toolName);
echo '<div class="row">';
echo '<div class="col-md-12">';

if (count($videoconferenceList) > 0) {
    echo '<table class="table data_table">';
    echo '<tr>';
    echo '<th>'.$pluginZoom->get_lang('Group').'</th>';
    echo '<th>'.$pluginZoom->get_lang('Topic').'</th>';
    echo '<th>'.$pluginZoom->get_lang('StartTime').'</th>';
    echo '<th>'.$pluginZoom->get_lang('Access').'</th>';
    echo '</tr>';
    foreach ($videoconferenceList as $item) {
        $meetingItemId = $item['meeting_id'];
        $meeting = $pluginZoom->getMeetingRepository()->findOneBy(['meetingId' => $meetingItemId]);
        $meetingInfoGet = $meeting->getMeetingInfoGet();
        $infoCourse = api_get_course_info_by_id($meeting->getCourse());

        echo '<tr>';
        echo '<td style="vertical-align:middle">';
        echo '<a title="'.htmlspecialchars($item['name']).'" href="'.$codePath.'session/index.php?session_id='.$item['session_id'].'">';
        echo '<span style="font-weight: bold; font-size: 16px;">'.htmlspecialchars($item['name']).'</span>';
        echo '</a>';
        echo '<br><span style="font-style: italic; font-size: 14px;">'.htmlspecialchars($infoCourse['title']).'</span>';
        echo '</td>';

        $min = $meetingInfoGet->duration > 0 ? ' ('.$meetingInfoGet->duration.' min)' : '';
        echo '<td style="vertical-align:middle">'.$meetingInfoGet->topic.$min.'</td>';
        echo '<td style="vertical-align:middle">'.$meeting->startDateTime->format('Y-m-d H:i').'</td>';
        echo '<td style="vertical-align:middle">';
        if (!$meeting->checkStartDateTime()) {
            echo '<span class="btn btn-warning btn-xs">No disponible</span>';
        } else {
            if (!$meeting->checkPassStartDateTime()) {
                echo '<span class="btn btn-success btn-xs">Realizada</span>';
            } else {
                echo '<a class="btn btn-primary btn-xs" href="'.$pluginPath.'zoom/join_meeting.php?meetingId='.$meetingItemId.'&cidReq='.$infoCourse['code'].'&id_session='.$meeting->getSession()->getId().'">';
                echo $pluginZoom->get_lang('Join');
                echo '</a>';
            }
        }
        echo '</td>';
        echo '</tr>';
    }
    echo '</table>';
}

Display::display_footer();
