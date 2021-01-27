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

$codePath = api_get_path(WEB_CODE_PATH);
$pluginPath = api_get_path(WEB_PLUGIN_PATH);
$toolName = get_lang('Dashboard');

$userId = api_get_user_id();
$controller = new IndexManager(get_lang('MyCourses'));
$courseAndSessions = $controller->returnCoursesAndSessionsViewBySession($userId, true);
/*
echo "<pre>";
echo var_dump($courseAndSessions);
echo "</pre>";
*/
//exit;

$htmlHeadXtra[] = '
    <style>
        .menu-item-gestor {

        }
        .menu-item-gestor a {
            color: #337ab7;
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
    </style>';


$htmlHeadXtra[] = '<script>
    $(function() {
        var height_max = 0;
        $(".box-session-active").each(function(index) {
            var height_div = parseInt($(this).height());
            if (height_div > height_max) {
                height_max = height_div;
            }
        });
        $(".box-session-active").height(height_max);

        
        var height_max = 0;
        $(".box-session-future").each(function(index) {
            var height_div = parseInt($(this).height());
            if (height_div > height_max) {
                height_max = height_div;
            }
        });
        $(".box-session-future").height(height_max);
    });
    </script>';

Display::display_header($toolName);
/*
echo '<div class="row">';
echo '<div class="col-xs-12 breadcrumb" style="padding: 10px 20px 0px 20px; border-radius: 10px;">';
// Icon 1
echo '<div class="menu-item-gestor col-md-3 col-sm-4 col-xs-12">';
echo '<div class="panel panel-default" style="margin-bottom:10px">';
echo '<div class="session panel-body">';
$icon = Display::return_icon(
    'course.png',
    'Cursos',
    array("style" => "margin-right:5px; vertical-align: text-bottom;"),
    ICON_SIZE_MEDIUM
);
$tools = Display::url(
    $icon.' <span class="item-name">Cursos</span>',
    $codePath.'admin/course_list.php'
);
echo $tools;
echo '</div>';
echo '</div>';
echo '</div>';

// Icon 2
echo '<div class="menu-item-gestor col-md-3 col-sm-4 col-xs-12">';
echo '<div class="panel panel-default" style="margin-bottom:10px">';
echo '<div class="session panel-body">';
$icon = Display::return_icon(
    'user.png',
    'Estudiantes',
    array("style" => "margin-right:5px; vertical-align: text-bottom;"),
    ICON_SIZE_MEDIUM
);
$tools = Display::url(
    $icon.' <span class="item-name">Estudiantes</span>',
    $codePath.'admin/user_list.php'
);
echo $tools;
echo '</div>';
echo '</div>';
echo '</div>';

// Icon 3
echo '<div class="menu-item-gestor col-md-3 col-sm-4 col-xs-12">';
echo '<div class="panel panel-default" style="margin-bottom:10px">';
echo '<div class="session panel-body">';
$icon = Display::return_icon(
    'announce.png',
    'Anuncios',
    array("style" => "margin-right:5px; vertical-align: text-bottom;"),
    ICON_SIZE_MEDIUM
);
$tools = Display::url(
    $icon.' <span class="item-name">Anuncios</span>',
    $codePath.'admin/system_announcements.php'
);
echo $tools;
echo '</div>';
echo '</div>';
echo '</div>';

// Icon 4
echo '<div class="menu-item-gestor col-md-3 col-sm-4 col-xs-12">';
echo '<div class="panel panel-default" style="margin-bottom:10px">';
echo '<div class="session panel-body">';
$icon = Display::return_icon(
    'zoom_meet.png',
    'Videoconferencias',
    array("style" => "margin-right:5px; vertical-align: text-bottom;"),
    ICON_SIZE_MEDIUM
);
$tools = Display::url(
    $icon.' <span class="item-name">Videoconferencia</span>',
    $codePath.'admin/configure_plugin.php?name=zoom'
);
echo $tools;
echo '</div>';
echo '</div>';
echo '</div>';

echo '<div class="clearfix"></div>';
echo '</div>';

echo '</div>';
*/
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
        
        //$coachList = CourseManager::get_coachs_from_course_to_string($row['session_id'], $infoCourse['id']);
        //$groupInfo['coach'] = $coachList;
        $listActivos[$sessionId] = $groupInfo;
    }
}

$iconSetting = Display::return_icon(
    'session.png',
    get_lang('Session'),
    array(),
    ICON_SIZE_MEDIUM
);

echo '<li><a href="#tabs-1">Cursos activos</a></li>';
echo '<li><a href="#tabs-2">Cursos futuros</a></li>';
echo '<li><a href="#tabs-3">Cursos finalizados</a></li>';
echo '</ul>';

echo '<div id="tabs-1">';
if (empty($listActivos)) {
    echo Display::return_message("No hay curso activos", 'warning', true);
}
echo '<div class="row">';
foreach ($listActivos as $key => $value) {
    $sessionId = $key;
    $sessionInfo = api_get_session_info($sessionId);
    echo '<div class="col-md-12 col-lg-6">';
    echo '<div class="panel panel-default">';
    echo '<div class="session panel-body box-session-active">';
    echo '<div class="row">';
    echo '<div class="col-md-12">';
        echo '<ul class="info-session list-inline" style="margin-bottom:0;">';
          echo '<li>';
            $dateSessionParse = SessionManager::parseSessionDates($sessionInfo, true);
            echo '<i class="fa fa-calendar" aria-hidden="true"></i> '.$dateSessionParse['access'];
          echo '</li>';
        echo '</ul>';
        echo '<div class="sessions-items">';
          echo '<div style="height:40px; overflow: hidden;">';
            echo '<h4 style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">';
              echo '<a title="'.htmlspecialchars($value['name']).'" href="'.$codePath.'session/index.php?session_id='.$sessionId.'">'.htmlspecialchars($value['name']).'</a>&nbsp;';
            echo '</h4>';
          echo '</div>';
        
        if (count($value['courses']) === 0) {
            echo '<div class="alert alert-warning">'.get_lang('NoCoursesForThisSession').'</div>';
        } else {
            echo '<table class="table" style="margin-bottom:0px">';
            foreach ($value['courses'] as $course) {
                $courseUrl = api_get_course_url($course['course_code'], $sessionId);
                $courseId = $course['real_id'];
            
                echo '<tr>';
                echo '<td>';
                echo Display::url(
                    $course['title'],
                    $courseUrl
                );
                echo '</td>';
                echo '<td class="text-right">';
                
                $allowZoom = api_get_plugin_setting('zoom', 'tool_enable') === 'true';
                if ($allowZoom) {
                    echo Display::url(
                        Display::return_icon('zoom_meet.png', 'Videoconferencia Zoom'),
                        $pluginPath."zoom/start.php?id_session=$sessionId&cidReq=".$course['course_code']
                    );
                }
                
                $course_info = api_get_course_info_by_id($courseId);
                
                $course_info['id_session'] = $sessionId;
                $course_info['status'] = $course['status'];
                
                // For each course, get if there is any notification icon to show
                // (something that would have changed since the user's last visit).
                $show_notification = !api_get_configuration_value('hide_course_notification')
                ? Display::show_notification($course_info)
                : '';
                echo $show_notification;
                
                echo '</td>';
                echo '</tr>';
            } // foreach course
            echo '</table>';
        }
        echo '</div>';
   
    echo '</div>';
    echo '</div>';
    echo '</div>';
    echo '</div>';
    echo '</div>';
}

$sessionId = 0;

    echo '<div class="col-md-12 col-lg-12">';
    echo '<div class="panel panel-default">';
    echo '<div class="session panel-body box-couse-active">';
    echo '<div class="row">';
    echo '<div class="col-md-12">';

    echo '<div class="sessions-items">';
    
    if (count($courseAndSessions['courses']) > 0) { 
        echo '<table class="table" style="margin-bottom:0px">';
        echo '<tr><th colspan="3">'.get_lang('Courses').'</th></tr>';
        foreach ($courseAndSessions['courses'] as $key => $courseItem) {
            $courseUrl = $courseItem['course']['course_public_url'];
            $courseId = $courseItem['course']['real_id'];
            echo '<tr>';
            echo '<td>';
            echo Display::url(
                '<img src="'.$courseItem['course']['course_image'].'" /> ',
                $courseUrl
                );
            echo '</td>';
            echo '<td style="vertical-align: middle;">';
            echo Display::url(
                $courseItem['course']['title'],
                $courseUrl
            );
            echo '</td>';
            echo '<td class="text-right" style="vertical-align: middle;">';
            
            $allowZoom = api_get_plugin_setting('zoom', 'tool_enable') === 'true';
            if ($allowZoom) {
                echo Display::url(
                    Display::return_icon('zoom_meet.png', 'Videoconferencia Zoom'),
                    $pluginPath."zoom/start.php?id_session=$sessionId&cidReq=".$courseItem['code']
                ).' ';
            }
           
            $course_info = api_get_course_info_by_id($courseId);
            
            $userInCourseStatus = CourseManager::getUserInCourseStatus($userId, $course_info['real_id']);
            $course_info['status'] = empty($sessionId) ? $userInCourseStatus : STUDENT;
            $course_info['id_session'] = $sessionId;
            
            // For each course, get if there is any notification icon to show
            // (something that would have changed since the user's last visit).
            $show_notification = !api_get_configuration_value('hide_course_notification')
            ? Display::show_notification($course_info)
            : '';
            echo $show_notification;
            
            echo '</td>';
            echo '</tr>';
        }
        echo '</table>';
    }
    echo '</div>';
    
    echo '</div>';
    echo '</div>';
    echo '</div>';
    echo '</div>';
    echo '</div>';

echo '</div>';
echo '</div>';
echo '<div id="tabs-2">';
if (empty($listFuturos)) {
    echo Display::return_message("No hay curso futuros", 'warning', true);
}
echo '<div class="row">';
foreach ($listFuturos as $key => $value) {
    $sessionId = $key;
    $sessionInfo = api_get_session_info($sessionId);
    echo '<div class="col-md-12 col-lg-6">';
    echo '<div class="panel panel-default">';
    echo '<div class="session panel-body box-session-future">';
    echo '<div class="row">';
    echo '<div class="col-md-12">';
    echo '<ul class="info-session list-inline" style="margin-bottom:0;">';
    echo '<li>';
    $dateSessionParse = SessionManager::parseSessionDates($sessionInfo, true);
    echo '<i class="fa fa-calendar" aria-hidden="true"></i> '.$dateSessionParse['access'];
    echo '</li>';
    echo '</ul>';
    echo '<div class="sessions-items">';
    echo '<div style="height:40px; overflow: hidden;">';
    echo '<h4 style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">';
    echo '<a title="'.htmlspecialchars($value['name']).'" href="#">'.htmlspecialchars($value['name']).'</a>&nbsp;';
    echo '</h4>';
    echo '</div>';
    
    if (count($value['courses']) === 0) {
        echo '<div class="alert alert-warning">'.get_lang('NoCoursesForThisSession').'</div>';
    } else {
        echo '<table class="table" style="margin-bottom:0px">';
        foreach ($value['courses'] as $course) {
            $courseUrl = "#"; // api_get_course_url($course['course_code'], $sessionId);
            $courseId = $course['real_id'];
            
            echo '<tr>';
            echo '<td>';
            echo Display::url(
                $course['title'],
                $courseUrl
            );
            echo '</td>';
            /*
            echo '<td class="text-right">';
            
            $allowZoom = api_get_plugin_setting('zoom', 'tool_enable') === 'true';
            if ($allowZoom) {
                echo Display::url(
                    Display::return_icon('zoom_meet.png', 'Videoconferencia Zoom'),
                    '#' //$pluginPath."zoom/start.php?id_session=$sessionId&cidReq=".$course['course_code']
                );
            }
            
            $course_info = api_get_course_info_by_id($courseId);
            
            $course_info['id_session'] = $sessionId;
            $course_info['status'] = $course['status'];
            
            echo '</td>';
            */
            echo '</tr>';
        } // foreach course
        echo '</table>';
    }
    
    echo '</div>';
    
    echo '</div>';
    echo '</div>';
    echo '</div>';
    echo '</div>';
    echo '</div>';
}
echo '</div>';
echo '</div>';
echo '<div id="tabs-3">';
if (empty($listPasados)) {
    echo Display::return_message("No hay curso finalizados", 'warning', true);
}
echo '<div class="row">';
if (count($listPasados) > 0) {
    echo '<table class="data_table table">';
    echo '<tr><th>Curso</th><th>Opciones</th></tr>';
    foreach ($listPasados as $key => $value) {
        $sessionId = $key;
        $sessionInfo = api_get_session_info($sessionId);
        
        if ($sessionInfo['visibility'] == SESSION_INVISIBLE) {
            continue;
        }
        echo '<tr>';
        echo '<td><h4><a href="'.$codePath.'session/index.php?session_id='.$sessionId.'">'.htmlspecialchars($value['name']).'</a></h4>';
        $dateSessionParse = SessionManager::parseSessionDates($sessionInfo, true);
        echo '<i class="fa fa-calendar" aria-hidden="true"></i> '.$dateSessionParse['access'];
        echo '</td>';
        
        $tools = Display::url(
            $iconSetting,
            $codePath.'session/index.php?session_id='.$key
        );
        echo '<td style="vertical-align:middle; min-width:120px;">';
        echo $tools;
        echo '</td>';
        echo '</tr>';
    }
    echo '</table>';
}
echo '</div>';
echo '</div>';

echo '</div>';
echo '</div>';
Display::display_footer();