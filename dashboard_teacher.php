<?php

use Chamilo\CoreBundle\Entity\Course;
use Chamilo\CoreBundle\Entity\Session;
use Chamilo\CoreBundle\Entity\SessionRelCourseRelUser;

$cidReset = true;
require_once 'main/inc/global.inc.php';

api_block_anonymous_users();

if (!api_is_teacher()) {
    api_not_allowed(true);
}

$codePath = api_get_path(WEB_CODE_PATH);
$coursePath = api_get_path(WEB_COURSE_PATH);
$pluginPath = api_get_path(WEB_PLUGIN_PATH);
$toolName = get_lang('Dashboard');

$userId = api_get_user_id();
$controller = new IndexManager(get_lang('MyCourses'));
$courseAndSessions = $controller->returnCoursesAndSessionsViewBySession($userId, true);

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
        .ui-tabs .ui-tabs-nav li.ui-tabs-active {
            background: #FFFFFF !important;
        }
        .ui-tabs .ui-state-default, .ui-tabs .ui-widget-content .ui-state-default, .ui-tabs .ui-widget-header .ui-state-default {
            background: #F0F0F0 !important;
        }
        .ui-state-active a, .ui-state-active a:link, .ui-state-active a:visited{
            font-weight: bold;
        }

        .cat-session-activos {
            position: absolute;
            bottom: 0;
            /* right: 30px; */
        }
        .cat-session-futuros {
            position: absolute;
            bottom: 0;
            /* right: 30px; */
        }

    </style>';

/*
$htmlHeadXtra[] = '<script>
    $(function() {

    });
    </script>';
*/
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
    ["style" => "margin-right:5px; vertical-align: text-bottom;"],
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
    ["style" => "margin-right:5px; vertical-align: text-bottom;"],
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
    ["style" => "margin-right:5px; vertical-align: text-bottom;"],
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

// contenido tabs
echo '<div class="row">';

echo '<div class="col-md-12">';
echo '<script>
            $(function(){
                $("#tabs").tabs();
            });
        </script>';
echo '<div id="tabs">';
echo '<ul>';
$listActivos = [];
$listFuturos = [];
$listPasados = [];

$categories = SessionManager::get_all_session_category();
$orderedCategories = [];
if (!empty($categories)) {
    foreach ($categories as $category) {
        $orderedCategories[$category['id']] = $category['name'];
    }
}

foreach ($courseAndSessions['sessions'] as $key => $sessionCategory) {
    foreach ($sessionCategory['sessions'] as $sessionItem) {
        $groupInfo = [
            'id' => $sessionItem['session_id'],
            'session_category_id' => $key ? $key : null,
            'session_category_name' => $sessionCategory['session_category']['name'],
            'name' => $sessionItem['session_name'],
            'access_start_date' => $sessionItem['access_start_date']
            ? $sessionItem['access_start_date']
            : null,
            'access_end_date' => $sessionItem['access_end_date']
            ? $sessionItem['access_end_date']
            : null,
            'coach_access_start_date' => $sessionItem['coach_access_start_date']
            ? $sessionItem['coach_access_start_date']
            : null,
            'coach_access_end_date' => $sessionItem['coach_access_end_date']
            ? $sessionItem['coach_access_end_date']
            : null,
            'courses' => $sessionItem['courses'],
        ];

        $sessionId = $groupInfo['id'];
        $accessStartDate = $groupInfo['access_start_date'];
        $accessEndDate = $groupInfo['access_end_date'];

        if (!empty($accessStartDate) && strtotime(api_get_local_time($accessStartDate)) > time()) {
            // futuro curso
            $listFuturos[$sessionId] = $groupInfo;
            continue;
        }

        if (!empty($accessEndDate) && strtotime(api_get_local_time($accessEndDate)) < time()) {
            $listPasados[$sessionId] = $groupInfo;
            continue;
        }

        $listActivos[$sessionId] = $groupInfo;
    }
}

$iconSetting = Display::return_icon(
    'settings.png',
    get_lang('Settings'),
    [],
    ICON_SIZE_MEDIUM
);

echo '<li><a href="#tabs-1">'.get_lang('ActiveGroups').'</a></li>';
if (!empty($listFuturos)) {
    echo '<li><a href="#tabs-2">'.get_lang('FutureGroups').'</a></li>';
}
if (!empty($listPasados)) {
    echo '<li><a href="#tabs-3">'.get_lang('GroupsCompleted').'</a></li>';
}
echo '</ul>';

echo '<div id="tabs-1">';
if (empty($listActivos)) {
    echo Display::return_message("No hay curso activos", 'warning', true);
}
echo '<div class="row">';
echo '<div class="col-md-12">';
if (count($listActivos) > 0) {
    echo '<table class="table data_table">';

    foreach ($listActivos as $key => $value) {
        $sessionId = $key;
        $sessionInfo = api_get_session_info($sessionId);
        $session = api_get_session_entity($sessionId);

        foreach ($value['courses'] as $courseItem) {
            $course = api_get_course_entity($courseItem['real_id']);
            echo '<tr>';

            // Course image
            $imagePath = CourseManager::getPicturePath($course);
            echo '<td>';
            echo '<img src="'.$imagePath.'" />';
            echo '</td>';

            // Session name and course title
            echo '<td>';
            echo '<a title="'.htmlspecialchars($value['name']).'" href="'.$coursePath.$course->getCode().'/index.php?id_session='.$sessionId.'">';
            echo '<span style="font-weight: bold; font-size: 16px;">'.htmlspecialchars($value['name']).'</span>';
            echo '</a>';
            echo '<br><span style="font-style: italic; font-size: 14px;">'.htmlspecialchars($course->getTitle()).'</span>';
            /*
            if (!empty($value['session_category_id'])) {
                echo '<span class="btn btn-primary btn-xs pull-right">';
                echo $orderedCategories[$value['session_category_id']];
                echo '</span>';
            }
            */
            echo '</td>';

            // Dates
            echo '<td style="font-size:14px; ">';
            $newDates = SessionManager::convert_dates_to_local($sessionInfo, false);
            echo date("d/m/Y", strtotime($newDates['access_start_date'])).'<br>';
            if (!empty($newDates['access_end_date'])) {
                echo date("d/m/Y", strtotime($newDates['access_end_date']));
            }
            echo '</td>';

            // Coach
            /*
            $namesOfCoaches = [];
            $coachSubscriptions = $session->getUserCourseSubscriptionsByStatus($course, Session::COACH);

            if ($coachSubscriptions) {
                foreach ($coachSubscriptions as $subscription) {
                    $namesOfCoaches[] = $subscription->getUser()->getCompleteNameWithUserName();
                }
            }

            $coachHtml = ($namesOfCoaches ? implode('<br>', $namesOfCoaches) : 'Sin tutores');

            echo '<td style="vertical-align:middle; font-size:14px; text-align:center" class="text-primary">';
            echo $coachHtml;
            echo '</td>';
            */
            $students = SessionManager::getCountUsersInCourseSession($course, $session);
            // Student
            echo '<td style="vertical-align:middle; font-size:14px; text-align:center" class="text-primary">';
            if ($students > 0) {
                echo '<span style="font-size:18px;">'.$students.'</span><br>matrículas activas';
            } else {
                echo 'Sin alumnos matrículados';
            }
            echo '</td>';

            // Acciones
            echo '<td class="text-right" style="vertical-align:middle">';
            $actions = Display::url(
                Display::return_icon('2rightarrow.png', get_lang('Details')),
                $codePath.'lp/lp_controller.php?cidReq='.$course->getCode().'&id_session='.$sessionId
            );
            echo $actions;
            echo '</td>';

            echo '</tr>';
        }
    }
    echo '</table>';
}
echo '</div>';

echo '</div>';
echo '</div>';
if (!empty($listFuturos)) {
    echo '<div id="tabs-2">';
    echo '<div class="row">';
    echo '<div class="col-md-12">';

    echo '<table class="data_table table">';
    foreach ($listFuturos as $key => $value) {
        $sessionId = $key;
        $sessionInfo = api_get_session_info($sessionId);
        $session = api_get_session_entity($sessionId);

        foreach ($value['courses'] as $courseItem) {
            $course = api_get_course_entity($courseItem['real_id']);

            $newDates = SessionManager::convert_dates_to_local($sessionInfo, false);
            $checkAccess = true;
            $accessStartDate = $newDates['coach_access_start_date'];
            $accessEndDate = $newDates['access_end_date'];

            if (!empty($accessStartDate) && strtotime($accessStartDate) > time()) {
                $checkAccess = false;
            }

            if (!empty($accessEndDate) && strtotime($accessEndDate) < time()) {
                $checkAccess = false;
            }

            echo '<tr>';
            // Course image
            $imagePath = CourseManager::getPicturePath($course);
            echo '<td>';
            echo '<img src="'.$imagePath.'" />';
            echo '</td>';

            // Session name and course title
            echo '<td>';
            if ($checkAccess) {
                echo '<a title="'.htmlspecialchars($value['name']).'" href="'.$codePath.'session/index.php?session_id='.$sessionId.'">';
            } else {
                echo '<a title="'.htmlspecialchars($value['name']).'" href="#">';
            }
            echo '<span style="font-weight: bold; font-size: 16px;">'.htmlspecialchars($value['name']).'</span>';
            echo '</a>';
            echo '<br><span style="font-style: italic; font-size: 14px;">'.htmlspecialchars($course->getTitle()).'</span>';
            if (!empty($value['session_category_id'])) {
                echo '<span class="btn btn-primary btn-xs pull-right">';
                echo $orderedCategories[$value['session_category_id']];
                echo '</span>';
            }
            echo '</td>';

            // Dates
            echo '<td style="font-size:14px; ">';
            echo "<span>Acceso profesores: </span><br>";
            echo date("d/m/Y", strtotime($newDates['coach_access_start_date']));
            if (!empty($newDates['coach_access_end_date'])) {
                echo ' - '.date("d/m/Y", strtotime($newDates['coach_access_end_date']));
            }
            echo '</td>';

            // Dates
            echo '<td style="font-size:14px; ">';
            echo "<span>Acceso estudiantes: </span><br>";
            echo date("d/m/Y", strtotime($newDates['access_start_date']));
            if (!empty($newDates['access_end_date'])) {
                echo ' - '.date("d/m/Y", strtotime($newDates['access_end_date']));
            }
            echo '</td>';

            // Coach
            $namesOfCoaches = [];
            $coachSubscriptions = $session->getUserCourseSubscriptionsByStatus($course, Session::COACH);

            if ($coachSubscriptions) {
                /** @var SessionRelCourseRelUser $subscription */
                foreach ($coachSubscriptions as $subscription) {
                    $namesOfCoaches[] = $subscription->getUser()->getCompleteNameWithUserName();
                }
            }
            $coachHtml = ($namesOfCoaches ? implode('<br>', $namesOfCoaches) : 'Sin tutores');

            echo '<td style="vertical-align:middle; font-size:14px; text-align:center" class="text-primary">';
            echo $coachHtml;
            echo '</td>';

            // Acciones
            echo '<td class="text-right" style="vertical-align:middle">';
            if ($checkAccess) {
                echo Display::url(
                    Display::return_icon('2rightarrow.png', get_lang('Details')),
                    $coursePath.$course->getCode()."/index.php?id_session=$sessionId"
                );
            }
            echo '</td>';

            echo '</tr>';
        }
    }
    echo '</table>';

    echo '</div>';
    echo '</div>';
    echo '</div>';
}

if (!empty($listPasados)) {
    echo '<div id="tabs-3">';
    echo '<div class="row">';
    echo '<div class="col-md-12">';

    echo '<table class="data_table table">';
    foreach ($listPasados as $key => $value) {
        $sessionId = $key;
        $sessionInfo = api_get_session_info($sessionId);
        $session = api_get_session_entity($sessionId);

        foreach ($value['courses'] as $courseItem) {
            $course = api_get_course_entity($courseItem['real_id']);

            $newDates = SessionManager::convert_dates_to_local($sessionInfo, false);
            $checkAccess = true;
            $accessStartDate = $newDates['coach_access_start_date'];
            $accessEndDate = $newDates['access_end_date'];

            if (!empty($accessStartDate) && strtotime($accessStartDate) > time()) {
                $checkAccess = false;
            }

            if (!empty($accessEndDate) && strtotime($accessEndDate) < time()) {
                $checkAccess = false;
            }

            echo '<tr>';
            // Course image
            $imagePath = CourseManager::getPicturePath($course);
            echo '<td>';
            echo '<img src="'.$imagePath.'" />';
            echo '</td>';

            // Session name and course title
            echo '<td>';
            if ($checkAccess) {
                echo '<a title="'.htmlspecialchars($value['name']).'" href="'.$codePath.'session/index.php?session_id='.$sessionId.'">';
            } else {
                echo '<a title="'.htmlspecialchars($value['name']).'" href="#">';
            }
            echo '<span style="font-weight: bold; font-size: 16px;">'.htmlspecialchars($value['name']).'</span>';
            echo '</a>';
            echo '<br><span style="font-style: italic; font-size: 14px;">'.htmlspecialchars($course->getTitle()).'</span>';
            if (!empty($value['session_category_id'])) {
                echo '<span class="btn btn-primary btn-xs pull-right">';
                echo $orderedCategories[$value['session_category_id']];
                echo '</span>';
            }
            echo '</td>';

            // Dates
            echo '<td style="font-size:14px; ">';
            echo "<span>Acceso profesores: </span><br>";
            echo date("d/m/Y", strtotime($newDates['coach_access_start_date']));
            if (!empty($newDates['coach_access_end_date'])) {
                echo ' - '.date("d/m/Y", strtotime($newDates['coach_access_end_date']));
            }
            echo '</td>';

            // Dates
            echo '<td style="font-size:14px; ">';
            echo "<span>Acceso estudiantes: </span><br>";
            echo date("d/m/Y", strtotime($newDates['access_start_date']));
            if (!empty($newDates['access_end_date'])) {
                echo ' - '.date("d/m/Y", strtotime($newDates['access_end_date']));
            }
            echo '</td>';

            // Coach
            $namesOfCoaches = [];
            $coachSubscriptions = $session->getUserCourseSubscriptionsByStatus($course, Session::COACH);

            if ($coachSubscriptions) {
                /** @var SessionRelCourseRelUser $subscription */
                foreach ($coachSubscriptions as $subscription) {
                    $namesOfCoaches[] = $subscription->getUser()->getCompleteNameWithUserName();
                }
            }
            $coachHtml = ($namesOfCoaches ? implode('<br>', $namesOfCoaches) : 'Sin tutores');

            echo '<td style="vertical-align:middle; font-size:14px; text-align:center" class="text-primary">';
            echo $coachHtml;
            echo '</td>';

            // Acciones
            echo '<td class="text-right" style="vertical-align:middle">';
            if ($checkAccess) {
                echo Display::url(
                        Display::return_icon('2rightarrow.png', get_lang('Details')),
                        $coursePath.$course->getCode()."/index.php?id_session=$sessionId"
                        );
            }
            echo '</td>';

            echo '</tr>';
        }
    }
    echo '</table>';

    echo '</div>';
    echo '</div>';
    echo '</div>';
}
/*
// Lista de videoconferencias de zoom
$zoomHtml = '<div class="alert alert-warning">Sin salas de videoconferencia próximas</div>';
$zoomMeetingList = $zoomMeetingDate = [];
$now = strtotime(date("Y-m-d"));

foreach ($courseAndSessions['sessions'] as $catSession) {
    foreach ($catSession['sessions'] as $sessionItem) {
        $sessionItemId = $sessionItem['session_id'];
        foreach ($sessionItem['courses'] as $courseItem) {
            $courseItemCode = $courseItem['course_code'];
            $courseItemVisibilty = $courseItem['visibility'];
            if ($courseItemVisibilty == COURSE_VISIBILITY_HIDDEN) {
                continue;
            }

            $courseInfo = api_get_course_info($courseItemCode);
            $courseId = $courseInfo['real_id'];

            $sql = "SELECT * FROM plugin_zoom_meeting WHERE course_id=$courseId AND session_id=$sessionItemId";
            $res = Database::query($sql);
            while ($row = Database::fetch_assoc($res)) {
                if ($now > strtotime($row['start_time'])) {
                    continue;
                }
                $zoomMeetingDate[] = strtotime($row['start_time']);
                $zoomMeetingList[] = $row['meeting_id'];
            }
        }
    }
}

array_multisort($zoomMeetingDate, $zoomMeetingList);

if (!empty($zoomMeetingList)) {
    $pluginZoom = ZoomPlugin::create();
    $zoomHtml = '<div class="panel panel-default">';
    $zoomHtml .= '<div class="panel-heading"><h4>Próximas videoconferencias</h4></div>';
    $zoomHtml .= '<div class="panel-body">';
    $zoomHtml .= '<table class="table">';
    $zoomHtml .= '<tr>';
    $zoomHtml .= '<th>'.$pluginZoom->get_lang('Course').'</th>';
    $zoomHtml .= '<th>'.$pluginZoom->get_lang('Topic').'</th>';
    $zoomHtml .= '<th>'.$pluginZoom->get_lang('StartTime').'</th>';
    //$zoomHtml .= '<th>'.$pluginZoom->get_lang('Duration').'</th>';
    $zoomHtml .= '<th>'.$pluginZoom->get_lang('Access').'</th>';
    $zoomHtml .= '</tr>';

    $em = Database::getManager();
    $i = 0;
    $limRow = api_get_configuration_value('limit_row_meeting') ? api_get_configuration_value('limit_row_meeting') : 10;
    foreach ($zoomMeetingList as $meetingItemId) {
        if ($i >= $limRow) {
            break;
        }
        $meeting = $pluginZoom->getMeetingRepository()->findOneBy(['meetingId' => $meetingItemId]);
        $meetingInfoGet = $meeting->getMeetingInfoGet();
        $infoCourse = api_get_course_info_by_id($meeting->getCourse());
        $zoomHtml .= '<tr>';
        $zoomHtml .= '<td>'.$infoCourse['title'].'</td>';
        $min = $meetingInfoGet->duration > 0 ? ' ('.$meetingInfoGet->duration.' min)' : '';
        $zoomHtml .= '<td>'.$meetingInfoGet->topic.$min.'</td>';
        $zoomHtml .= '<td>'.$meeting->startDateTime->format('Y-m-d H:i').'</td>';
        //$zoomHtml .= '<td>'.$meetingInfoGet->duration.'</td>';

        $zoomHtml .= '<td>';
        if (!$meeting->checkStartDateTime()) {
            $zoomHtml .= 'No disponible';
        } else {
            $zoomHtml .= '<a class="btn btn-primary btn-xs" href="'.$pluginPath.'zoom/join_meeting.php?meetingId='.$meetingItemId.'&cidReq='.$infoCourse['code'].'&id_session='.$meeting->getSession()->getId().'">';
            $zoomHtml .= $pluginZoom->get_lang('Join');
            $zoomHtml .= '</a>';
        }
        $zoomHtml .= '</td>';

        $zoomHtml .= '</tr>';
    }
    $zoomHtml .= '</table>';
    $zoomHtml .= '</div>';
    $zoomHtml .= '</div>';
}
echo $zoomHtml;
*/
echo '</div>';
echo '</div>';
Display::display_footer();
