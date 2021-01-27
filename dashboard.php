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

if (!api_is_platform_admin(true)) {
    api_not_allowed(true);
}

/**
 * Return an icon representing the visibility of the course
 */
function get_course_visibility_icon_dashboard($v)
{
    $style = 'margin-bottom:0;margin-right:5px;';
    switch ($v) {
        case 0:
            return Display::return_icon('bullet_red.png', get_lang('CourseVisibilityClosed'), array('style' => $style));
            break;
        case 1:
            return Display::return_icon('bullet_orange.png', get_lang('Private'), array('style' => $style));
            break;
        case 2:
            return Display::return_icon('bullet_green.png', get_lang('OpenToThePlatform'), array('style' => $style));
            break;
        case 3:
            return Display::return_icon('bullet_blue.png', get_lang('OpenToTheWorld'), array('style' => $style));
            break;
        case 4:
            return Display::return_icon('bullet_grey.png', get_lang('CourseVisibilityHidden'), array('style' => $style));
            break;
        default:
            return '';
    }
}

$codePath = api_get_path(WEB_CODE_PATH);
$pluginPath = api_get_path(WEB_PLUGIN_PATH);
$toolName = get_lang('Dashboard');

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


$htmlHeadXtra[] = '<script>
    $(function() {
        var height_max = 0;
        $(".box-session-active").each(function(index) {
            var height_div = parseInt($(this).height());
            if (height_div > height_max) {
                height_max = height_div;
            }
        });
        if ($( ".cat-session-activos" ).length) {
            $(".box-session-active").height(height_max + 25);
        } else {
            $(".box-session-active").height(height_max);
        }
        
        var height_max = 0;
        $(".box-session-future").each(function(index) {
            var height_div = parseInt($(this).height());
            if (height_div > height_max) {
                height_max = height_div;
            }
        });

        if ($( ".cat-session-futuros" ).length) {
            $(".box-session-future").height(height_max + 25);
        } else {
            $(".box-session-future").height(height_max);
        }
    });
    </script>';

Display::display_header($toolName);

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
$orderedCategories = array();
if (!empty($categories)) {
    foreach ($categories as $category) {
        $orderedCategories[$category['id']] = $category['name'];
    }
}

$em = Database::getManager();

/** @var SessionRepository $sessionRepository */
$sessionRepository = $em->getRepository('ChamiloCoreBundle:Session');
$databaseSessions = $sessionRepository->findAll();

foreach ($databaseSessions as $session) {
    $groupInfo = [
        'id' => $session->getId(),
        'id_coach' => $session->getGeneralCoach() ? $session->getGeneralCoach()->getId() : null,
        'session_category_id' => $session->getCategory() ? $session->getCategory()->getId() : null,
        'name' => $session->getName(),
        'description' => $session->getDescription(),
        'show_description' => $session->getShowDescription(),
        'duration' => $session->getDuration(),
        'nbr_courses' => $session->getNbrCourses(),
        'nbr_users' => $session->getNbrUsers(),
        'nbr_classes' => $session->getNbrClasses(),
        'session_admin_id' => $session->getSessionAdminId(),
        'visibility' => $session->getVisibility(),
        'promotion_id' => $session->getPromotionId(),
        'display_start_date' => $session->getDisplayStartDate()
        ? $session->getDisplayStartDate()->format('Y-m-d H:i:s')
        : null,
        'display_end_date' => $session->getDisplayEndDate()
        ? $session->getDisplayEndDate()->format('Y-m-d H:i:s')
        : null,
        'access_start_date' => $session->getAccessStartDate()
        ? $session->getAccessStartDate()->format('Y-m-d H:i:s')
        : null,
        'access_end_date' => $session->getAccessEndDate()
        ? $session->getAccessEndDate()->format('Y-m-d H:i:s')
        : null,
        'coach_access_start_date' => $session->getCoachAccessStartDate()
        ? $session->getCoachAccessStartDate()->format('Y-m-d H:i:s')
        : null,
        'coach_access_end_date' => $session->getCoachAccessEndDate()
        ? $session->getCoachAccessEndDate()->format('Y-m-d H:i:s')
        : null,
        'send_subscription_notification' => $session->getSendSubscriptionNotification(),
    ];

    $sessionId = $groupInfo['id'];
    $accessStartDate = $groupInfo['access_start_date'];
    $accessEndDate = $groupInfo['access_end_date'];

    if (!empty($accessStartDate) && strtotime($accessStartDate) > time()) {
        // futuro curso
        $listFuturos[$sessionId] = $groupInfo;
        continue;
    }
    
    if (!empty($accessEndDate) && strtotime($accessEndDate) < time()) {
        $listPasados[$sessionId] = $groupInfo;
        continue;
    }
    
    //$coachList = CourseManager::get_coachs_from_course_to_string($row['session_id'], $infoCourse['id']);
    //$groupInfo['coach'] = $coachList;
    $listActivos[$sessionId] = $groupInfo;
}

$iconSetting = Display::return_icon(
    'settings.png',
    get_lang('Settings'),
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
    echo '<div class="session panel-body ">';
    echo '<div class="row">';
    echo '<div class="col-md-12 box-session-active">';
        echo '<ul class="info-session list-inline" style="margin-bottom:0;">';
          echo '<li>';
            $dateSessionParse = SessionManager::parseSessionDates($sessionInfo, true);
            echo '<i class="fa fa-calendar" aria-hidden="true"></i> '.$dateSessionParse['access'];
          echo '</li>';
        echo '</ul>';
        echo '<div class="sessions-items">';
          echo '<div style="height:40px; overflow: hidden;">';
            echo '<h4 style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">';
              echo '<a title="'.htmlspecialchars($value['name']).'" href="'.$codePath.'session/resume_session.php?id_session='.$sessionId.'">'.htmlspecialchars($value['name']).'</a>&nbsp;';
            echo '</h4>';
          echo '</div>';

        /** @var Session $session */
        $session = $sessionRepository->find($sessionId);
        
        if ($session->getNbrCourses() === 0) {
            echo '<div class="alert alert-warning">'.get_lang('NoCoursesForThisSession').'</div>';
        } else {
            echo '<table class="table" style="margin-bottom:0px">';
            $courses = $sessionRepository->getCoursesOrderedByPosition($session);
            
            /** @var Course $course */
            foreach ($courses as $course) {
                $courseUrl = api_get_course_url($course->getCode(), $sessionId);
            
                echo '<tr>';
                echo '<td>';
                echo Display::url(
                    $course->getTitle(), //.' ('.$course->getVisualCode().')',
                    $courseUrl
                );
                echo '</td>';
                echo '<td class="text-right">';
                
                $allowZoom = api_get_plugin_setting('zoom', 'tool_enable') === 'true';
                if ($allowZoom) {
                    echo Display::url(
                        Display::return_icon('zoom_meet.png', 'Videoconferencia Zoom'),
                        $pluginPath."zoom/start.php?id_session=$sessionId&cidReq=".$course->getCode()
                    );
                }
                // announcement icon
                echo Display::url(
                    Display::return_icon('valves.gif', get_lang('announcement')),
                    $codePath."announcements/announcements.php?cidReq={$course->getCode()}&id_session=$sessionId"
                );
                // calendar icon
                echo Display::url(
                    Display::return_icon('agenda.gif', get_lang('calendar_event')),
                    $codePath."calendar/agenda.php?cidReq={$course->getCode()}&id_session=$sessionId"
                );
                // User icon
                echo Display::url(
                    Display::return_icon('members.gif', get_lang('user')),
                    $codePath."user/user.php?cidReq={$course->getCode()}&id_session=$sessionId"
                );
                
                echo '</td>';
                echo '</tr>';
            } // foreach course
            echo '</table>';
        }
        echo '</div>';
        
        
        if (!empty($value['session_category_id'])) {
            echo '<div class="cat-session-activos">';
            echo '<span class="btn btn-primary btn-xs">';
            echo $orderedCategories[$value['session_category_id']];
            echo '</span>';
            echo '</div>';
        }
        echo '</div>';
        echo '</div>';
    
    echo '</div>';
    echo '</div>';
    echo '</div>';
}

if (api_is_platform_admin()) {
    /** @var CourseRepository $courseRepository */
    $courseRepository = $em->getRepository('ChamiloCoreBundle:Course');
    $databaseCourses = $courseRepository->findAll();
    $sessionId = 0;
    
    echo '<div class="col-md-12 col-lg-12">';
    echo '<div class="panel panel-default">';
    echo '<div class="session panel-body box-couse-active">';
    echo '<div class="row">';
    echo '<div class="col-md-12">';
    
    echo '<div class="sessions-items">';
    
    echo '<table class="table" style="margin-bottom:0px">';
    echo '<tr><th colspan="3">'.get_lang('Courses').'</th></tr>';
    /** @var Course $course */
    foreach ($databaseCourses as $course) {
        $courseUrl = api_get_course_url($course->getCode(), $sessionId);
        
        echo '<tr>';
        echo '<td>';
        echo get_course_visibility_icon_dashboard($course->getVisibility());
        echo Display::url(
                $course->getTitle(), //.' ('.$course->getVisualCode().')',
                $courseUrl
                );
        echo '</td>';
        echo '<td class="text-right">';
        
        $icourse = api_get_course_info($course->getCode());
        echo Display::url(
            Display::return_icon('course_home.gif', get_lang('CourseHomepage')),
            $codePath.$icourse['path']."/index.php"
        );
            
        echo Display::url(
            Display::return_icon('statistics.gif', get_lang('Tracking')),
            $codePath."tracking/courseLog.php?".api_get_cidreq_params($course->getCode())
        );
        
        echo Display::url(
            Display::return_icon('edit.png', get_lang('Edit'), array(), ICON_SIZE_SMALL),
            $codePath."admin/course_edit.php?id=".$icourse['real_id']
        );
        
        $allowZoom = api_get_plugin_setting('zoom', 'tool_enable') === 'true';
        if ($allowZoom) {
            echo Display::url(
                    Display::return_icon('zoom_meet.png', 'Videoconferencia Zoom'),
                    $pluginPath."zoom/start.php?id_session=$sessionId&cidReq=".$course->getCode()
                    );
        }
        // announcement icon
        echo Display::url(
                Display::return_icon('valves.gif', get_lang('announcement')),
                $codePath."announcements/announcements.php?cidReq={$course->getCode()}&id_session=$sessionId"
                );
        // calendar icon
        echo Display::url(
                Display::return_icon('agenda.gif', get_lang('calendar_event')),
                $codePath."calendar/agenda.php?cidReq={$course->getCode()}&id_session=$sessionId"
                );
        // User icon
        echo Display::url(
                Display::return_icon('members.gif', get_lang('user')),
                $codePath."user/user.php?cidReq={$course->getCode()}&id_session=$sessionId"
                );
        
        echo '</td>';
        echo '</tr>';
    } // foreach course
    echo '</table>';
    echo '</div>';
    
    echo '</div>';
    echo '</div>';
    echo '</div>';
    echo '</div>';
    echo '</div>'; // courses section
}

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
    echo '<div class="session panel-body ">';
    echo '<div class="row">';
    echo '<div class="col-md-12 box-session-future">';
    echo '<ul class="info-session list-inline" style="margin-bottom:0;">';
    echo '<li>';
    $dateSessionParse = SessionManager::parseSessionDates($sessionInfo, true);
    echo '<i class="fa fa-calendar" aria-hidden="true"></i> '.$dateSessionParse['access'];
    echo '</li>';
    echo '</ul>';
    echo '<div class="sessions-items">';
    echo '<div style="height:40px; overflow: hidden;">';
    echo '<h4 style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">';
    echo '<a title="'.htmlspecialchars($value['name']).'" href="'.$codePath.'session/resume_session.php?id_session='.$sessionId.'">'.htmlspecialchars($value['name']).'</a>&nbsp;';
    echo '</h4>';
    echo '</div>';
    
    /** @var Session $session */
    $session = $sessionRepository->find($sessionId);
    
    if ($session->getNbrCourses() === 0) {
        echo '<div class="alert alert-warning">'.get_lang('NoCoursesForThisSession').'</div>';
    } else {
        echo '<table class="table" style="margin-bottom:0px">';
        $courses = $sessionRepository->getCoursesOrderedByPosition($session);
        
        /** @var Course $course */
        foreach ($courses as $course) {
            $courseUrl = api_get_course_url($course->getCode(), $sessionId);
            
            echo '<tr>';
            echo '<td>';
            echo Display::url(
                    $course->getTitle(),
                    $courseUrl
                    );
            echo '</td>';
            echo '<td class="text-right">';
            
            $allowZoom = api_get_plugin_setting('zoom', 'tool_enable') === 'true';
            if ($allowZoom) {
                echo Display::url(
                        Display::return_icon('zoom_meet.png', 'Videoconferencia Zoom'),
                        $pluginPath."zoom/start.php?id_session=$sessionId&cidReq=".$course->getCode()
                        );
            }
            // announcement icon
            echo Display::url(
                    Display::return_icon('valves.gif', get_lang('announcement')),
                    $codePath."announcements/announcements.php?cidReq={$course->getCode()}&id_session=$sessionId"
                    );
            // calendar icon
            echo Display::url(
                    Display::return_icon('agenda.gif', get_lang('calendar_event')),
                    $codePath."calendar/agenda.php?cidReq={$course->getCode()}&id_session=$sessionId"
                    );
            // User icon
            echo Display::url(
                    Display::return_icon('members.gif', get_lang('user')),
                    $codePath."user/user.php?cidReq={$course->getCode()}&id_session=$sessionId"
                    );
            
            echo '</td>';
            echo '</tr>';
        } // foreach course
        echo '</table>';
    }
    echo '</div>';
    
   
    
    if (!empty($value['session_category_id'])) {
        echo '<div class="cat-session-futuros">';
        echo '<span class="btn btn-primary btn-xs">';
        echo $orderedCategories[$value['session_category_id']];
        echo '</span>';
        echo '</div>';
    }
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
        echo '<tr>';
        echo '<td><h4><a href="'.$codePath.'session/resume_session.php?id_session='.$sessionId.'">'.htmlspecialchars($value['name']).'</a></h4>';
        $dateSessionParse = SessionManager::parseSessionDates($sessionInfo, true);
        echo '<i class="fa fa-calendar" aria-hidden="true"></i> '.$dateSessionParse['access'];
        echo '</td>';
        
        $tools = Display::url(
            $iconSetting,
            $codePath.'session/resume_session.php?id_session='.$key
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