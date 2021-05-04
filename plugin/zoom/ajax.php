<?php
/* For licensing terms, see /license.txt */

$course_plugin = 'zoom'; // needed in order to load the plugin lang variables

require_once __DIR__.'/config.php';

api_protect_course_script(true);

/**
 * Responses to AJAX calls.
 *
 * @package chamilo.plugin.zoom
 */

if (api_is_anonymous()) {
    api_not_allowed(true);
}

$plugin = ZoomPlugin::create();
$action = isset($_REQUEST['a']) ? $_REQUEST['a'] : null;

switch ($action) {
    case 'deleteSelectedMeeting':
        $list = isset($_REQUEST['list']) ? $_REQUEST['list'] : [];
        if (empty($list)) {
            echo json_encode(["status" => "false", "message" => $plugin->get_lang('NoMeetingSelected')]);
            exit;
        }

        $em = Database::getManager();
        try {
            foreach ($list as $value) {
                if (empty($value)) {
                    continue;
                }
                $meeting = $plugin->getMeetingRepository()->findOneBy(['meetingId' => $value]);

                if (null === $meeting) {
                    continue;
                }

                if ($meeting && $meeting->isCourseMeeting()) {
                    // Delete calendar event
                    $eventData = Database::select(
                        '*',
                        Database::get_course_table(TABLE_AGENDA),
                        ['where' => ['zoom_meeting_id = ?' => [$meeting->getId()]]],
                        'first'
                    );
                    
                    if (!empty($eventData)) {
                        $agenda = new Agenda('course');
                        $agenda->deleteEvent($eventData['id']);
                    }
                    
                    // No need to delete a instant meeting.
                    if (\Chamilo\PluginBundle\Zoom\API\Meeting::TYPE_INSTANT != $meeting->getMeetingInfoGet()->type) {
                        $meeting->getMeetingInfoGet()->delete();
                    }

                    $em->remove($meeting);
                    $em->flush();
                }
            }

            Display::addFlash(
                Display::return_message($plugin->get_lang('MeetingsDeleted'), 'confirm')
            );

            echo json_encode(["status" => "true"]);

        } catch (Exception $exception) {
            error_log(print_r($exception,1));
            Display::addFlash(
                Display::return_message("Error al borrar sala", 'error')
            );
            
            echo json_encode(["status" => "false", "message" => "Errro al borrar sala de zoom"]);
            //$this->handleException($exception);
        }

        break;
}


            