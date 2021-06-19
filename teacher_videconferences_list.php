<?php

use Chamilo\CoreBundle\Entity\Course;
use Chamilo\CoreBundle\Entity\Repository\SequenceResourceRepository;
use Chamilo\CoreBundle\Entity\Repository\SessionRepository;
use Chamilo\CoreBundle\Entity\SequenceResource;
use Chamilo\CoreBundle\Entity\Session;
use Chamilo\CoreBundle\Entity\SessionRelCourseRelUser;
use Chamilo\CoreBundle\Entity\SessionRelCourse;

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
$now = date("Y-m-d");

$session_table = Database::get_main_table(TABLE_MAIN_SESSION);
$session_rel_course_rel_user_table = Database::get_main_table(TABLE_MAIN_SESSION_COURSE_USER);
$videoconferenceList = [];
$sql = "SELECT session_rc_ru.*, s.name, z.start_time, z.meeting_id
        FROM $session_table s
        INNER JOIN $session_rel_course_rel_user_table session_rc_ru
        ON session_rc_ru.session_id = s.id AND session_rc_ru.user_id = '".$userId."'
        INNER JOIN plugin_zoom_meeting z ON (session_rc_ru.session_id = z.session_id AND session_rc_ru.c_id = z.course_id)
        WHERE session_rc_ru.status = 2 AND z.start_time > '".$now."'
        ORDER BY start_time ASC";
$result = Database::query($sql);
$videoconferenceList = Database::store_result($result);

$htmlHeadXtra[] = '
    <style>
        .menu-item-gestor {
        
        }
        .menu-item-gestor a {
            color: #660000;
        }
        .panel-body {
            padding: 10px;
        }
        
        .item-name {
            font-weight: bold;
            font-size: 16px;
            margin-left: 5px;
            vertical-align: super;
        }
    </style>';

Display::display_header($toolName);
echo '<div class="row">';
echo '<div class="col-xs-12 breadcrumb" style="padding: 10px 20px 0px 20px; border-radius: 10px;">';

// Icon 1
echo '<div class="menu-item-gestor col-md-4 col-sm-4 col-xs-12">';
echo '<div class="panel panel-default" style="margin-bottom:10px">';
echo '<div class="session panel-body">';
$icon = Display::return_icon(
    'session.png',
    get_lang('Groups'),
    array("style" => "margin-right:5px; vertical-align: text-bottom;"),
    ICON_SIZE_MEDIUM
);
$tools = Display::url(
    $icon.' <span class="item-name">'.get_lang('Groups').'</span>',
    'teacher_groups.php'
);
echo $tools;
echo '</div>';
echo '</div>';
echo '</div>';

// Icon 2
echo '<div class="menu-item-gestor col-md-4 col-sm-4 col-xs-12">';
echo '<div class="panel panel-default" style="margin-bottom:10px">';
echo '<div class="session panel-body">';
$icon = Display::return_icon(
    'user.png',
    get_lang('Students'),
    array("style" => "margin-right:5px; vertical-align: text-bottom;"),
    ICON_SIZE_MEDIUM
);
$tools = Display::url(
    $icon.' <span class="item-name">'.get_lang('Students').'</span>',
    'teacher_student_list.php'
);
echo $tools;
echo '</div>';
echo '</div>';
echo '</div>';

// Icon 3
echo '<div class="menu-item-gestor col-md-4 col-sm-4 col-xs-12">';
echo '<div class="panel panel-default" style="margin-bottom:10px">';
echo '<div class="session panel-body">';
$icon = Display::return_icon(
    'zoom_meet.png',
    ZoomPlugin::create()->get_lang('ZoomVideoConferences'),
    array("style" => "margin-right:5px; vertical-align: text-bottom;"),
    ICON_SIZE_MEDIUM
);
$tools = Display::url(
    $icon.' <span class="item-name">'.ZoomPlugin::create()->get_lang('ZoomVideoConferences').'</span>',
    'teacher_videconferences_list.php'
);
echo $tools;
echo '</div>';
echo '</div>';
echo '</div>';

echo '<div class="clearfix"></div>';
echo '</div>';

echo '</div>';

echo '<h2>'.$toolName.'</h2>';
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
    $limRow = api_get_configuration_value('limit_row_meeting') ? api_get_configuration_value('limit_row_meeting') : 10;
    $i = 0;
    foreach ($videoconferenceList as $item ) {
        if ($i > $limRow) {
            break;
        }
        
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
        echo '<td style="vertical-align:middle">'.$meeting->startDateTime->format('d-m-Y H:i').'</td>';
        echo '<td style="vertical-align:middle">';
        if (!$meeting->checkStartDateTime()) {
            echo '<span class="btn btn-warning btn-xs">No disponible</span>';
        } else {
            // if (!$meeting->checkPassStartDateTime()) {
            //    echo '<span class="btn btn-success btn-xs">Realizada</span>';   
            // } else {
                echo '<a class="btn btn-primary btn-xs" href="'.$pluginPath.'zoom/join_meeting.php?meetingId='.$meetingItemId.'&cidReq='.$infoCourse['code'].'&id_session='.$meeting->getSession()->getId().'">';
                echo $pluginZoom->get_lang('Join');
                echo '</a>';
            // }
        }
        echo '</td>';
        echo '</tr>';
        $i++;
    }
    echo '</table>';
} else {
    echo '<div class="alert alert-warning">Sin videoconferencias activas</div>';
}

Display::display_footer();
