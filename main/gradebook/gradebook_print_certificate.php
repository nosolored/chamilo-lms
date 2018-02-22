<?php
/* For licensing terms, see /license.txt */

/**
 * Script
 * @package chamilo.gradebook
 */
require_once __DIR__.'/../inc/global.inc.php';

api_block_anonymous_users();

use ChamiloSession as Session;

$userList = array();
if (empty($_GET['export_all'])) {
    if (!isset($_GET['student_id'])) {
        $studentId = api_get_user_id();
    } else {
        $studentId = intval($_GET['student_id']);
    }
    $userList[] = api_get_user_info($studentId);
    
    if (!isset($_GET['course_code'])) {
        api_protect_course_script(true);
        $courseId = api_get_course_int_id();
        $courseCode = api_get_course_id();
    } else {
        $courseCode = $_GET['course_code'];
        $courseId = api_get_course_int_id($courseCode);    
    }
    
    if (!isset($_GET['session_id'])) {
        $sessionId = api_get_session_id();
    } else {
        $sessionId = intval($_GET['session_id']);
    }
} else {
    $courseId = api_get_course_int_id();
    $courseCode = api_get_course_id();
    $sessionId = api_get_session_id();

    $certificateTable =  Database::get_main_table(TABLE_MAIN_GRADEBOOK_CERTIFICATE);
    $categoryTable = Database::get_main_table(TABLE_MAIN_GRADEBOOK_CATEGORY);
    $sql = "SELECT cer.user_id AS user_id
            FROM $certificateTable cer
            INNER JOIN $categoryTable cat
            ON (cer.cat_id = cat.id)
            WHERE cat.course_code = '$courseCode' AND cat.session_id = $sessionId";
    $rs = Database::query($sql);
    while ($row = Database::fetch_assoc($rs)) {
        $userList[] = api_get_user_info($row['user_id']);
    }
}

if ($sessionId > 0) {
    $sessionInfo = SessionManager::fetch($sessionId);
}

$table = Database::get_main_table(TABLE_MAIN_GRADEBOOK_CERTIFICATE_ALTERNATIVE);
$useDefault = false;
$base = api_get_path(WEB_UPLOAD_PATH).'certificates/';

// Get info certificate
$infoCertificate = Database::select(
    '*',
    $table,
    ['where'=> ['c_id = ? AND session_id = ?' => [$courseId, $sessionId]]],
    'first'
);

if (!is_array($infoCertificate)) {
    $infoCertificate = array();
}
    
if (empty($infoCertificate)) {
    $infoCertificate = Database::select(
        '*',
        Database::get_main_table(TABLE_MAIN_GRADEBOOK_CERTIFICATE_ALTERNATIVE),
        ['where'=> ['certificate_default = ? ' => 1]],
        'first'
    );
    
    if (!is_array($infoCertificate)) {
        $infoCertificate = array();
    }
    
    if (empty($infoCertificate)) {
        Display::display_header('Imprimir certificado');
        echo Display::return_message(get_lang('ErrorTemplateCertificate'), 'error');
        Display::display_footer();
        exit;
    } else {
        $useDefault = true;
        $base = $base.'default/';
        $path = $base;
    }
} else {
    $path = $base.$infoCertificate['id'].'/';
}
    
$widthCell = intval((297 - $infoCertificate['margin']) / 6);
$htmlText = '';
$htmlText .= '<link rel="stylesheet" type="text/css" href="'.api_get_path(WEB_CSS_PATH).'certificate.css">';
$htmlText .= '<link rel="stylesheet" type="text/css" href="'.api_get_path(WEB_CSS_PATH).'editor.css">';
$htmlText .= '<body>';

foreach ($userList as $userInfo) {
    $studentId = $userInfo['user_id'];
    
    if (empty($infoCertificate['background'])) {
        $htmlText .= '<div class="caraA" style="padding:0; page-break-before:always;" margin:0; padding:0;>';
    } else {
        $urlBackground = $path.$infoCertificate['background'];
        $htmlText .= '<div class="caraA" style="background-image:url('.$urlBackground.') no-repeat; background-repeat:no-repeat; background-image-resize:6; padding:0; page-break-before:always; border:1px solid #000;" margin:0; padding:0;>';
    }

    if (!empty($infoCertificate['logo_left'])) {
        $logoLeft = '<img style="max-height: 150px; max-width: '.(2 * $widthCell).'mm;" src="'.$path.$infoCertificate['logo_left'].'" />';
    } else {
        $logoLeft = '';
    }
    if (!empty($infoCertificate['logo_center'])) {
        $logoCenter = '<img style="max-height: 150px; max-width: '.(2 * $widthCell).'mm;" src="'.$path.$infoCertificate['logo_center'].'" />';
    } else {
        $logoCenter = '';
    }
    if (!empty($infoCertificate['logo_right'])) {
        $logoRight = '<img style="max-height: 150px; max-width: '.(2 * $widthCell).'mm;" src="'.$path.$infoCertificate['logo_right'].'" />';
    } else {
        $logoRight = '';
    }
    $htmlText .= '<table width="'.intval($widthCell * 6).'mm" height="200mm" style="margin-left:'.$infoCertificate['margin'].'mm;">';
    $htmlText .= '<tr>';
        $htmlText .= '<td colspan="4" class="logo">'.$logoLeft.'</td>';
        $htmlText .= '<td colspan="4" class="logo" style="text-align:center;">'.$logoCenter.'</td>';
        $htmlText .= '<td colspan="4" class="logo" style="text-align:right;">'.$logoRight.'</td>';
    $htmlText .= '</tr>';
    
    $all_user_info = DocumentManager::get_all_info_to_certificate(
        $studentId,
        $courseCode,
        true
    );
    $my_content_html = $infoCertificate['content_course'];
    $my_content_html = str_replace(chr(13).chr(10).chr(13).chr(10),chr(13).chr(10), $my_content_html);
    $info_to_be_replaced_in_content_html = $all_user_info[0];
    $info_to_replace_in_content_html = $all_user_info[1];
    $my_content_html = str_replace(
        $info_to_be_replaced_in_content_html,
        $info_to_replace_in_content_html,
        $my_content_html
    );
    $my_content_html = strip_tags($my_content_html, '<p><b><strong><table><tr><td><th><tbody><span><i><li><ol><ul><dd><dt><dl><br><hr><img><a><div>');
    $htmlText .= '<tr>';
        $htmlText .= '<td colspan="12" class="content-table">';
        $htmlText .= '<table width="100%">';
        $htmlText .= '<tr>';
            $htmlText .= '<td colspan="12" class="content-student">';
            $htmlText .= $my_content_html;
            $htmlText .= '</td>';
        $htmlText .= '</tr>';
        
        $htmlText .= '<tr>';
            $htmlText .= '<td colspan="12" class="course-date">';
            
            $startDate = '';
            $endDate = '';
            switch ($infoCertificate['date_change']) {
                case 0:
                    $htmlText .= get_lang('DateStartEnd');
                    if (!empty($sessionInfo['access_start_date'])) {
                        $startDate = date("d/m/Y", strtotime(api_get_local_time($sessionInfo['access_start_date'])));
                        $htmlText .= $startDate;
                    }
                    $htmlText .= ' - ';
                    if (!empty($sessionInfo['access_end_date'])) {
                        $endDate = date("d/m/Y", strtotime(api_get_local_time($sessionInfo['access_end_date'])));
                        $htmlText .= $endDate;
                    }
                    break;
                case 1:
                    $htmlText .= get_lang('DateStartEnd');
                    $startDate = date("d/m/Y", strtotime($infoCertificate['date_start']));
                    $endDate = date("d/m/Y", strtotime($infoCertificate['date_end']));
                    $htmlText .= '<b>'.$startDate.'-'.$endDate.'</b>';
                    break;
                case 2:
                    $htmlText .= '';
                    break;
            }
            $htmlText .= '</td>';
        $htmlText .= '</tr>';
        $htmlText .= '</table>';
        $htmlText .= '</td>';
    $htmlText .= '</tr>';
    $htmlText .= '<tr>';
        $htmlText .= '<td colspan="12" class="expediction">';
        if ($infoCertificate['type_date_expediction'] != 3) {
            $htmlText .= get_lang('ExpedictionIn').' '.$infoCertificate['place'];
            if ($infoCertificate['type_date_expediction'] == 1) {
                setlocale(LC_ALL,"es_ES");
                $htmlText .= strftime(" a %d de %B del %Y");
            } elseif ($infoCertificate['type_date_expediction'] == 2) {
                if (!empty($infoCertificate['day']) && !empty($infoCertificate['month']) && !empty($infoCertificate['year']) ) {
                    $htmlText .= ' a '.$infoCertificate['day'].' de '.$infoCertificate['month'].' de '.$infoCertificate['year'];
                } else {
                    $htmlText .= ' a ...... de ............ de .......';
                }
            } else {
                $fecha = api_get_local_time($sessionInfo['access_end_date']);
                setlocale(LC_ALL,"es_ES");
                $htmlText .= strftime(" a %d de %B del %Y", strtotime($fecha));
            }
        }
        $htmlText .= '</td>';
    $htmlText .= '</tr>';
    $htmlText .= '<tr>';
        $htmlText .= '<td colspan="2" class="seals" style="width:'.$widthCell.'mm">
                    '.((!empty($infoCertificate['signature_text1'])) ?  $infoCertificate['signature_text1'] : '').
                    '</td>
                    <td colspan="2" class="seals" style="width:'.$widthCell.'mm">
                        '.((!empty($infoCertificate['signature_text2'])) ?  $infoCertificate['signature_text2'] : '').
                    '</td>
                    <td colspan="2" class="seals" style="width:'.$widthCell.'mm">
                        '.((!empty($infoCertificate['signature_text3'])) ?  $infoCertificate['signature_text3'] : '').
                    '</td>
                    <td colspan="2" class="seals" style="width:'.$widthCell.'mm">
                        '.((!empty($infoCertificate['signature_text4'])) ?  $infoCertificate['signature_text4'] : '').
                    '</td>
                    <td colspan="4" class="seals" style="width:'.(2 * $widthCell).'mm">
                        '.((!empty($infoCertificate['seal'])) ?  get_lang('Seal') : '').
                    '</td>';
    $htmlText .= '</tr>';
    $htmlText .= '<tr>';
        $htmlText .= '<td colspan="2" class="logo-seals" style="width:'.$widthCell.'mm">
                        '.((!empty($infoCertificate['signature1'])) ?
                        '<img style="max-height: 100px; max-width: '.$widthCell.'mm;" src="'.$path.$infoCertificate['signature1'].'" />' : '').
                    '</td>
                    <td colspan="2" class="logo-seals" style="width:'.$widthCell.'mm">
                        '.((!empty($infoCertificate['signature2'])) ?
                        '<img style="max-height: 100px; '.$widthCell.'mm;" src="'.$path.$infoCertificate['signature2'].'" />' : '').
                    '</td>
                    <td colspan="2" class="logo-seals" style="width:'.$widthCell.'mm">
                        '.((!empty($infoCertificate['signature3'])) ?
                        '<img style="max-height: 100px; '.$widthCell.'mm;" src="'.$path.$infoCertificate['signature3'].'" />' : '').
                    '</td>
                    <td colspan="2" class="logo-seals" style="width:'.$widthCell.'mm">
                        '.((!empty($infoCertificate['signature4'])) ?
                        '<img style="max-height: 100px; '.$widthCell.'mm;" src="'.$path.$infoCertificate['signature4'].'" />' : '').
                    '</td>
                    <td colspan="4" class="logo-seals" style="width:'.(2 * $widthCell).'mm">
                        '.((!empty($infoCertificate['seal'])) ?
                        '<img style="max-height: 100px; '.(2 * $widthCell).'mm;" src="'.$path.$infoCertificate['seal'].'" />' : '').
                    '</td>';
    $htmlText .= '</tr>';
    $htmlText .= '</table>';
    $htmlText .= '</div>';
        
    // Rear certificate
    $htmlText .= '<div class="caraB" style="page-break-before:always;" margin:0; padding:0;>';
    
    if ($infoCertificate['contents_type'] == 0) {
        
        $contents_description = CourseDescription::get_data_by_description_type(3, $courseId, 0);
        
        $domd = new DOMDocument();
        libxml_use_internal_errors(true);
        $domd->loadHTML($contents_description['description_content']);
        libxml_use_internal_errors(false);
        
        $domx = new DOMXPath($domd);
        $items = $domx->query("//li[@style]");
        foreach($items as $item) {
            $item->removeAttribute("style");
        }
        
        $items = $domx->query("//span[@style]");
        foreach($items as $item) {
            $item->removeAttribute("style");
        }
        
        $output = $domd->saveHTML();
    
        $htmlText .= getIndexFiltered($output);
    }
    
    if ($infoCertificate['contents_type'] == 1) {
        $items = array();
        $categoriesTempList = learnpath::getCategories($courseId);
        $categoryTest = new CLpCategory();
        $categoryTest->setId(0);
        $categoryTest->setName(get_lang('WithOutCategory'));
        $categoryTest->setPosition(0);
        $categories = array(
                $categoryTest
        );
        if (!empty($categoriesTempList)) {
            $categories = array_merge($categories, $categoriesTempList);
        }
        
        foreach ($categories as $item) {
            $categoryId = $item->getId();
            if (!learnpath::categoryIsVisibleForStudent($item, api_get_user_entity($studentId))) {
                continue;
            }
            
            $sql = "SELECT 1 FROM c_item_property WHERE tool = 'learnpath_category' AND ref = $categoryId AND visibility = 0 AND (session_id = $sessionId OR session_id IS NULL)";
            $res = Database::query($sql);
            if (Database::num_rows($res) > 0) {
                continue;
            }
            
            $list = new LearnpathList(
                    $studentId,
                    $courseCode,
                    $sessionId,
                    null,
                    false,
                    $categoryId
                    );
            
            $flat_list = $list->get_flat_list();
            if (count($categories) > 1 && count($flat_list) > 0) {
                if ($item->getName() != "Sin categoría") {
                    $items[] = '<h5 style="margin:0">'.$item->getName().'</h5>';
                }
            } else {
                continue;
            }
            
            foreach ($flat_list as $learnpath) {
                $lp_id = $learnpath['lp_old_id'];
                $sql = "SELECT 1 FROM c_item_property WHERE tool = 'learnpath' AND ref = $lp_id AND visibility = 0 AND (session_id = $sessionId OR session_id IS NULL)";
                $res = Database::query($sql);
                if (Database::num_rows($res) > 0) {
                    continue;
                }
                $lp_name = $learnpath['lp_name'];
                 $items[] = $lp_name.'<br>';
            }
            $items[] = '<br>';
        }
        if (count($items) > 0) {
            $htmlText .= '<table width="100%" class="contents-learnpath">';
            $htmlText .= '<tr>';
            $htmlText .= '<td>';
            $mitad = intval(count($items) / 2) + 1;
            $i = 0;
            foreach ($items as $value) {
                if ($i == 50) { 
                    $htmlText .= '</td><td>';
                }
                $htmlText .= $value;
                $i++;
            }
            $htmlText .= '</td>';
            $htmlText .= '</tr>';
            $htmlText .= '</table>';
        }
        $htmlText .= '</td></table>';
    }
    
    if ($infoCertificate['contents_type'] == 2) {
        $htmlText .= '<table width="100%" class="contents-learnpath">';
        $htmlText .= '<tr>';
        $htmlText .= '<td>';
        $my_content_html = strip_tags($infoCertificate['contents'], '<p><b><strong><table><tr><td><th><span><i><li><ol><ul><dd><dt><dl><br><hr><img><a><div><h1><h2><h3><h4><h5><h6>');
        $htmlText .= $my_content_html;
        $htmlText .= '</td>';
        $htmlText .= '</tr>';
        $htmlText .= '</table>';
    }
    $htmlText .= '</div>';
}

$htmlText .= '</body>';

$fileName = 'certificate_'.date("Ymd_His");
$params = array(
        'filename' => $fileName,
        'pdf_title' => "Certificate",
        'pdf_description' => '',
        'format' => 'A4-L',
        'orientation' => 'L',
        'left' => 15,
        'top' => 15,
        'bottom' => 0,
);

$pdf = new PDF($params['format'], $params['orientation'], $params);
$pdf->content_to_pdf($htmlText, '', $fileName, null, 'D', false, null, false, true, false);

function getIndexFiltered($indice){
    $txt = strip_tags($indice, "<b><strong><i>");
    $txt = str_replace(chr(13).chr(10).chr(13).chr(10),chr(13).chr(10), $txt);
    $lineas = explode(chr(13).chr(10), $txt);
    $n = count($lineas);
    for ($x=0; $x<47; $x++) {
        $text1 .= $lineas[$x].chr(13).chr(10);
    }
    for ($x=47; $x<94; $x++) {
        $text2 .= $lineas[$x].chr(13).chr(10);
    }
    
    $showLeft = str_replace(chr(13).chr(10), "<br/>", $text1);
    $showRight = str_replace(chr(13).chr(10), "<br/>", $text2);
    $result = '<table width="100%">';
    $result .= '<tr>';
    $result .= '<td style="width:50%;vertical-align:top;padding-left:15px; font-size:12px;">'.$showLeft.'</td>';
    $result .= '<td style="vertical-align:top; font-size:12px;">'.$showRight.'</td>';
    $result .= '<tr>';
    $result .= '</table>';
    
    return $result;
}
 