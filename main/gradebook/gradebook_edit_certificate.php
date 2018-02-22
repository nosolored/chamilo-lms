<?php
/* For licensing terms, see /license.txt */

/**
 * Script
 * @package chamilo.gradebook
 */
require_once __DIR__.'/../inc/global.inc.php';

api_block_anonymous_users();
GradebookUtils::block_students();

use ChamiloSession as Session;

api_protect_course_script(true);

$userId = api_get_user_id();
$courseId = api_get_course_int_id();
$courseCode = api_get_course_id();
$sessionId = api_get_session_id();

$table = Database::get_main_table(TABLE_MAIN_GRADEBOOK_CERTIFICATE_ALTERNATIVE);

$htmlHeadXtra[] = '
<script>
function date_certificate_switch_radio_button_1(){
    var input_date_start = document.getElementById("date_start");
    var input_date_end = document.getElementById("date_end");
    input_date_start.value = "";
    input_date_end.value = "";
    input_date_start.setAttribute("disabled","disabled");
    input_date_end.setAttribute("disabled","disabled");
}

function date_certificate_switch_radio_button_2(){
    var input_date_start = document.getElementById("date_start");
    var input_date_end = document.getElementById("date_end");
    input_date_start.removeAttribute("disabled");
    input_date_end.removeAttribute("disabled");
}   

function date_certificate_switch_radio_button_3(){
    var input_date_start = document.getElementById("date_start");
    var input_date_end = document.getElementById("date_end");
    input_date_start.value = "";
    input_date_end.value = "";
    input_date_start.setAttribute("disabled","disabled");
    input_date_end.setAttribute("disabled","disabled");
}

function type_date_expediction_switch_radio_button(){
    var input_type = document.getElementsByName("type_date_expediction");
    var type;
    for (var i=0;i<input_type.length;i++){
      if ( input_type[i].checked ) {
        type = input_type[i].value;
      }
    }
    var input_day = document.getElementById("day");
    var input_month = document.getElementById("month");
    var input_year = document.getElementById("year");
    if (type == 2) {
        input_day.removeAttribute("disabled");
        input_month.removeAttribute("disabled");
        input_year.removeAttribute("disabled");
    } else {
        input_day.setAttribute("disabled","disabled");
        input_month.setAttribute("disabled","disabled");
        input_year.setAttribute("disabled","disabled");
    }
}
        
function contents_type_switch_radio_button(){
    var input_type = document.getElementsByName("contents_type");
    var type;
    for (var i=0;i<input_type.length;i++){
      if ( input_type[i].checked ) {
        type = input_type[i].value;
      }
    }
    var input_contents = document.getElementById("contents");
    if (type == 2) {
        input_contents.removeAttribute("disabled");
    } else {
        input_contents.setAttribute("disabled","disabled");
    }
}
        
$(document).ready(function() {
    CKEDITOR.on("instanceReady", function (e) {
        showTemplates();
    });
        
    $( ".datepicker" ).datepicker();
});
        
</script>';

$htmlHeadXtra[] = api_get_css_asset('cropper/dist/cropper.min.css');
$htmlHeadXtra[] = api_get_asset('cropper/dist/cropper.min.js');
$htmlHeadXtra[] = '<script>
    function confirmation(name) {
        if (confirm("'.get_lang('AreYouSureToDeleteJS', '').' " + name + " ?")) {
                document.forms["profile"].submit();
        } else {
            return false;
        }
    }
    function show_image(image,width,height) {
        width = parseInt(width) + 100;
        height = parseInt(height) + 100;
        window_x = window.open(image,\'windowX\',\'width=\'+ width + \', height=\'+ height + \'\');
    }
                
    function hide_icon_edit(element_html)  {
        ident="#edit_image";
        $(ident).hide();
    }
    function show_icon_edit(element_html) {
        ident="#edit_image";
        $(ident).show();
    }
    </script>';

$htmlHeadXtra[] = '
<style>
    .form-control-cert {
        height: 34px;
        padding: 6px 12px;
        font-size: 14px;
        line-height: 1.42857143;
        color: #555;
        background-color: #fff;
        background-image: none;
        border: 1px solid #ccc;
        border-radius: 4px;
        -webkit-box-shadow: inset 0 1px 1px rgba(0,0,0,.075);
        box-shadow: inset 0 1px 1px rgba(0,0,0,.075);
        -webkit-transition: border-color ease-in-out .15s,-webkit-box-shadow ease-in-out .15s;
        -o-transition: border-color ease-in-out .15s,box-shadow ease-in-out .15s;
        transition: border-color ease-in-out .15s,box-shadow ease-in-out .15s;
    }
    .certificado-text-label {
        font-weight: 700;
        margin: 0 10px 0 10px;
    }
    input:disabled { background: #eee; }
    label { font-size: 13px; }
</style>';

$nameTools = get_lang('CertificateSetting');

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

//$infoCertificate = getInfoFormCertificado($userId, $courseId, $sessionId);

$interbreadcrumb[] = array("url" => "index.php?".api_get_cidreq(), "name" => get_lang('Gradebook'));
$form = new FormValidator('formEdit', 'post', api_get_self(), null, array('class' => 'form-vertical'));

if (isset($_POST['formSent']) && $_POST['formSent'] == 1 && $form->validate()) { 
    $formValues = $form->getSubmitValues();

    if (empty($formValues['contents'])) {
        $contents = '';
    } else {
        $contents = $formValues['contents'];
    }
    
    $check = Security::check_token('post');
    if ($check) {
        $date_start = str_replace('/', '-', $_POST['date_start']);
        $date_end = str_replace('/', '-', $_POST['date_end']);
        $params = [
                'c_id' => $courseId,
                'session_id' => $sessionId,
                'content_course' => $formValues['content_course'],
                'contents_type' => intval($_POST['contents_type']),
                'contents' => $contents,
                'date_change' => intval($_POST['date_change']),
                'date_start' => date("Y-m-d", strtotime($date_start)),
                'date_end' => date("Y-m-d", strtotime($date_end)),
                'place' => Database::escape_string($_POST['place']),
                'type_date_expediction' => intval($_POST['type_date_expediction']),
                'day' => Database::escape_string($_POST['day']),
                'month' => Database::escape_string($_POST['month']),
                'year' => Database::escape_string($_POST['year']),
                'signature_text1' => $formValues['signature_text1'],
                'signature_text2' => $formValues['signature_text2'],
                'signature_text3' => $formValues['signature_text3'],
                'signature_text4' => $formValues['signature_text4'],
                'margin' => intval($_POST['margin']),
                'certificate_default' => 0
        ];
        
        // Insert or Update
        if ($infoCertificate['id'] > 0) {
            $certificateId = $infoCertificate['id'];
            Database::update($table, $params, ['id = ?' => $certificateId]);
        } else {
            // Se procede a insertar
            Database::insert($table, $params);
            $certificateId = Database::insert_id();
        }
        
        // Image manager
        $base = api_get_path(SYS_UPLOAD_PATH);
        $pathCertificates = $base.'certificates/'.$certificateId.'/';
        
        if (!empty($formValues['remove_logo_left']) || $_FILES['logo_left']['size']) {
            @unlink($pathCertificates.$infoCertificate['logo_left']);
            $sql = "UPDATE $table SET logo_left = '' WHERE id = $certificateId";
            $rs = Database::query($sql);
        }
        
        if (!empty($formValues['remove_logo_center']) || $_FILES['logo_center']['size']) {
            @unlink($pathCertificates.$infoCertificate['logo_center']);
            $sql = "UPDATE $table SET logo_center = '' WHERE id = $certificateId";
            $rs = Database::query($sql);
        }
        
        if (!empty($formValues['remove_logo_right']) || $_FILES['logo_right']['size']) {
            @unlink($pathCertificates.$infoCertificate['logo_right']);
            $sql = "UPDATE $table SET logo_right = '' WHERE id = $certificateId";
            $rs = Database::query($sql);
        }
        
        if (!empty($formValues['remove_seal']) || $_FILES['seal']['size']) {
            @unlink($pathCertificates.$infoCertificate['seal']);
            $sql = "UPDATE $table SET seal = '' WHERE id = $certificateId";
            $rs = Database::query($sql);
        }
        
        if (!empty($formValues['remove_signature1']) || $_FILES['signature1']['size']) {
            @unlink($pathCertificates.$infoCertificate['signature1']);
            $sql = "UPDATE $table SET signature1 = '' WHERE id = $certificateId";
            $rs = Database::query($sql);
        }
        
        if (!empty($formValues['remove_signature2']) || $_FILES['signature2']['size']) {
            @unlink($pathCertificates.$infoCertificate['signature2']);
            $sql = "UPDATE $table SET signature2 = '' WHERE id = $certificateId";
            $rs = Database::query($sql);
        }
        
        if (!empty($formValues['remove_signature3']) || $_FILES['signature3']['size']) {
            @unlink($pathCertificates.$infoCertificate['signature3']);
            $sql = "UPDATE $table SET signature3 = '' WHERE id = $certificateId";
            $rs = Database::query($sql);
        }
        
        if (!empty($formValues['remove_signature4']) || $_FILES['signature4']['size']) {
            @unlink($pathCertificates.$infoCertificate['signature4']);
            $sql = "UPDATE $table SET signature4 = '' WHERE id = $certificateId";
            $rs = Database::query($sql);
        }
        
        if (!empty($formValues['remove_background']) || $_FILES['background']['size']) {
            @unlink($pathCertificates.$infoCertificate['background']);
            $sql = "UPDATE $table SET background = '' WHERE id = $certificateId";
            $rs = Database::query($sql);
        }
        
        $logo_left = $logo_center = $logo_right = $seal = $signature1 = $signature2 = $signature3 = $signature4 = $background = false;
        
        if ($_FILES['logo_left']['size']) {
            $new_picture = uploadImageCertificate(
                    $certificateId,
                    $_FILES['logo_left']['name'],
                    $_FILES['logo_left']['tmp_name'],
                    $formValues['logo_left_crop_result']
                    );
            if ($new_picture) {
                $sql = "UPDATE $table SET logo_left = '".$new_picture."' WHERE id = $certificateId";
                Database::query($sql);
                $logo_left = true;
            }
        }
        
        if ($_FILES['logo_center']['size']) {
            $new_picture = uploadImageCertificate(
                    $certificateId,
                    $_FILES['logo_center']['name'],
                    $_FILES['logo_center']['tmp_name'],
                    $formValues['logo_center_crop_result']
                    );
            if ($new_picture) {
                $sql = "UPDATE $table SET logo_center = '".$new_picture."' WHERE id = $certificateId";
                Database::query($sql);
                $logo_center = true;
            }
            
        }
        
        if ($_FILES['logo_right']['size']) {
            $new_picture = uploadImageCertificate(
                    $certificateId,
                    $_FILES['logo_right']['name'],
                    $_FILES['logo_right']['tmp_name'],
                    $formValues['logo_right_crop_result']
                    );
            if ($new_picture) {
                $sql = "UPDATE $table SET logo_right = '".$new_picture."' WHERE id = $certificateId";
                Database::query($sql);
                $logo_right = true;
            }
        }
        
        if ($_FILES['seal']['size']) {
            $new_picture = uploadImageCertificate(
                    $certificateId,
                    $_FILES['seal']['name'],
                    $_FILES['seal']['tmp_name'],
                    $formValues['seal_crop_result']
                    );
            if ($new_picture) {
                $sql = "UPDATE $table SET seal = '".$new_picture."' WHERE id = $certificateId";
                Database::query($sql);
                $seal = true;
            }
        }
        
        if ($_FILES['signature1']['size']) {
            $new_picture = uploadImageCertificate(
                    $certificateId,
                    $_FILES['signature1']['name'],
                    $_FILES['signature1']['tmp_name'],
                    $formValues['signature1_crop_result']
                    );
            if ($new_picture) {
                $sql = "UPDATE $table SET signature1 = '".$new_picture."' WHERE id = $certificateId";
                Database::query($sql);
                $signature1 = true;
            }
        }
        
        if ($_FILES['signature2']['size']) {
            $new_picture = uploadImageCertificate(
                    $certificateId,
                    $_FILES['signature2']['name'],
                    $_FILES['signature2']['tmp_name'],
                    $formValues['signature2_crop_result']
                    );
            if ($new_picture) {
                $sql = "UPDATE $table SET signature2 = '".$new_picture."' WHERE id = $certificateId";
                Database::query($sql);
                $signature2 = true;
            }
        }
        
        if ($_FILES['signature3']['size']) {
            $new_picture = uploadImageCertificate(
                    $certificateId,
                    $_FILES['signature3']['name'],
                    $_FILES['signature3']['tmp_name'],
                    $formValues['signature3_crop_result']
                    );
            if ($new_picture) {
                $sql = "UPDATE $table SET signature3 = '".$new_picture."' WHERE id = $certificateId";
                Database::query($sql);
                $signature3 = true;
            }
        }
        
        if ($_FILES['signature4']['size']) {
            $new_picture = uploadImageCertificate(
                    $certificateId,
                    $_FILES['signature4']['name'],
                    $_FILES['signature4']['tmp_name'],
                    $formValues['signature4_crop_result']
                    );
            if ($new_picture) {
                $sql = "UPDATE $table SET signature4 = '".$new_picture."' WHERE id = $certificateId";
                Database::query($sql);
                $signature4 = true;
            }
        }
        
        if ($_FILES['background']['size']) {
            $new_picture = uploadImageCertificate(
                    $certificateId,
                    $_FILES['background']['name'],
                    $_FILES['background']['tmp_name'],
                    $formValues['background_crop_result']
                    );
            if ($new_picture) {
                $sql = "UPDATE $table SET background = '".$new_picture."' WHERE id = $certificateId";
                Database::query($sql);
                $background = true;
            }
        }
        
        // Certificate Default
        if (intval($_POST['use_default'] == 1)) {
            $base = api_get_path(SYS_UPLOAD_PATH);
            
            $infoCertificateDefault = Database::select(
                '*',
                Database::get_main_table(TABLE_MAIN_GRADEBOOK_CERTIFICATE_ALTERNATIVE),
                ['where'=> ['certificate_default = ? ' => 1]],
                'first'
            );
            
            if (!is_array($infoCertificateDefault)) {
                $infoCertificateDefault = array();
            }
            
            $pathCertificatesDefault = $base.'certificates/default/';
            
            if (!file_exists($pathCertificates)) {
                mkdir($pathCertificates, api_get_permissions_for_new_directories(), true);
            }
            
            if (!empty($infoCertificateDefault['logo_left']) && !$logo_left) {
                copy($pathCertificatesDefault.$infoCertificateDefault['logo_left'], $pathCertificates.$infoCertificateDefault['logo_left']);
                $sql = "UPDATE $table SET logo_left = '".$infoCertificateDefault['logo_left']."' WHERE id = $certificateId";
                Database::query($sql);
            }
            
            if (!empty($infoCertificateDefault['logo_center']) && !$logo_center) {
                copy($pathCertificatesDefault.$infoCertificateDefault['logo_center'], $pathCertificates.$infoCertificateDefault['logo_center']);
                $sql = "UPDATE $table SET logo_center = '".$infoCertificateDefault['logo_center']."' WHERE id = $certificateId";
                Database::query($sql);
            }
            
            if (!empty($infoCertificateDefault['logo_right']) && !$logo_right) {
                copy($pathCertificatesDefault.$infoCertificateDefault['logo_right'], $pathCertificates.$infoCertificateDefault['logo_right']);
                $sql = "UPDATE $table SET logo_right = '".$infoCertificateDefault['logo_right']."' WHERE id = $certificateId";
                Database::query($sql);
            }
            
            if (!empty($infoCertificateDefault['seal']) && !$seal) {
                copy($pathCertificatesDefault.$infoCertificateDefault['seal'], $pathCertificates.$infoCertificateDefault['seal']);
                $sql = "UPDATE $table SET seal = '".$infoCertificateDefault['seal']."' WHERE id = $certificateId";
                Database::query($sql);
            }
            
            if (!empty($infoCertificateDefault['signature1']) && !$signature1) {
                copy($pathCertificatesDefault.$infoCertificateDefault['signature1'], $pathCertificates.$infoCertificateDefault['signature1']);
                $sql = "UPDATE $table SET signature1 = '".$infoCertificateDefault['signature1']."' WHERE id = $certificateId";
                Database::query($sql);
            }
            
            if (!empty($infoCertificateDefault['signature2']) && !$signature2) {
                copy($pathCertificatesDefault.$infoCertificateDefault['signature2'], $pathCertificates.$infoCertificateDefault['signature2']);
                $sql = "UPDATE $table SET signature2 = '".$infoCertificateDefault['signature2']."' WHERE id = $certificateId";
                Database::query($sql);
            }
            
            if (!empty($infoCertificateDefault['signature3']) && !$signature3) {
                copy($pathCertificatesDefault.$infoCertificateDefault['signature3'], $pathCertificates.$infoCertificateDefault['signature3']);
                $sql = "UPDATE $table SET signature3 = '".$infoCertificateDefault['signature3']."' WHERE id = $certificateId";
                Database::query($sql);
            }
            
            if (!empty($infoCertificateDefault['signature4']) && !$signature4) {
                copy($pathCertificatesDefault.$infoCertificateDefault['signature4'], $pathCertificates.$infoCertificateDefault['signature4']);
                $sql = "UPDATE $table SET signature4 = '".$infoCertificateDefault['signature4']."' WHERE id = $certificateId";
                Database::query($sql);
            }
            
            if (!empty($infoCertificateDefault['background']) && !$background) {
                copy($pathCertificatesDefault.$infoCertificateDefault['background'], $pathCertificates.$infoCertificateDefault['background']);
                $sql = "UPDATE $table SET background = '".$infoCertificateDefault['background']."' WHERE id = $certificateId";
                Database::query($sql);
            }
        }
        
        if (intval($_POST['defecto'] == 1)) {
            $infoCertificateDefault = Database::select(
                    '*',
                    Database::get_main_table(TABLE_MAIN_GRADEBOOK_CERTIFICATE_ALTERNATIVE),
                    ['where'=> ['certificate_default = ? ' => 1]],
                    'first'
                    );
            
            if (!is_array($infoCertificateDefault)) {
                $infoCertificateDefault = array();
            }
            

            if (!empty($infoCertificateDefault)) {
                $certificateDefaultId = $infoCertificateDefault['id'];
                $params['certificate_default'] = 1;
                Database::update($table, $params, ['id = ?' => $certificateDefaultId]);
                
            } else {
                $params['certificate_default'] = 1;
                Database::insert($table, $params);
                $certificateDefaultId = Database::insert_id();
            }
            
            $base = api_get_path(SYS_UPLOAD_PATH);
            $pathCertificatesDefault = $base.'certificates/default/';
            
            if (!file_exists($pathCertificatesDefault)) {
                mkdir($pathCertificatesDefault, api_get_permissions_for_new_directories(), true);
            }
            
            // Delete file in default folder
            $files = scandir($pathCertificatesDefault); 
            foreach($files as $file){
                if (is_file($pathCertificatesDefault.$file)) {
                    unlink($pathCertificatesDefault.$file);
                }
            }
            
            // Copy file of current certificate to default certificate
            $infoCertificate = Database::select(
                '*',
                $table,
                ['where'=> ['c_id = ? AND session_id = ?' => [$courseId, $sessionId]]],
                'first'
            );
            
            if (!is_array($infoCertificate)) {
                $infoCertificate = array();
            }
            
            if (!empty($infoCertificate['logo_left'])) {
                copy($pathCertificates.$infoCertificate['logo_left'], $pathCertificatesDefault.$infoCertificate['logo_left']);
            }
            $sql = "UPDATE $table SET logo_left = '".$infoCertificate['logo_left']."' WHERE id = $certificateDefaultId";
            $rs = Database::query($sql);
            
            if (!empty($infoCertificate['logo_center'])) {
                copy($pathCertificates.$infoCertificate['logo_center'], $pathCertificatesDefault.$infoCertificate['logo_center']);
            }
            $sql = "UPDATE $table SET logo_center = '".$infoCertificate['logo_center']."' WHERE id = $certificateDefaultId";
            $rs = Database::query($sql);
            
            if (!empty($infoCertificate['logo_right'])) {
                copy($pathCertificates.$infoCertificate['logo_right'], $pathCertificatesDefault.$infoCertificate['logo_right']);
            }
            $sql = "UPDATE $table SET logo_right = '".$infoCertificate['logo_right']."' WHERE id = $certificateDefaultId";
            $rs = Database::query($sql);
            
            if (!empty($infoCertificate['seal'])) {
                copy($pathCertificates.$infoCertificate['seal'], $pathCertificatesDefault.$infoCertificate['seal']);
            }
            $sql = "UPDATE $table SET seal = '".$infoCertificate['seal']."' WHERE id = $certificateDefaultId";
            $rs = Database::query($sql);
            
            if (!empty($infoCertificate['signature1'])) {
                copy($pathCertificates.$infoCertificate['signature1'], $pathCertificatesDefault.$infoCertificate['signature1']);
            }
            $sql = "UPDATE $table SET signature1 = '".$infoCertificate['signature1']."' WHERE id = $certificateDefaultId";
            $rs = Database::query($sql);
            
            if (!empty($infoCertificate['signature2'])) {
                copy($pathCertificates.$infoCertificate['signature2'], $pathCertificatesDefault.$infoCertificate['signature2']);
            }
            $sql = "UPDATE $table SET signature2 = '".$infoCertificate['signature2']."' WHERE id = $certificateDefaultId";
            $rs = Database::query($sql);
            
            if (!empty($infoCertificate['signature3'])) {
                copy($pathCertificates.$infoCertificate['signature3'], $pathCertificatesDefault.$infoCertificate['signature3']);
            }
            $sql = "UPDATE $table SET signature3 = '".$infoCertificate['signature3']."' WHERE id = $certificateDefaultId";
            $rs = Database::query($sql);
            
            if (!empty($infoCertificate['signature4'])) {
                copy($pathCertificates.$infoCertificate['signature4'], $pathCertificatesDefault.$infoCertificate['signature4']);
            }
            $sql = "UPDATE $table SET signature4 = '".$infoCertificate['signature4']."' WHERE id = $certificateDefaultId";
            $rs = Database::query($sql);
            
            if (!empty($infoCertificate['background'])) {
                copy($pathCertificates.$infoCertificate['background'], $pathCertificatesDefault.$infoCertificate['background']);
            }
            $sql = "UPDATE $table SET background = '".$infoCertificate['background']."' WHERE id = $certificateDefaultId";
            $rs = Database::query($sql);
        }
        
        Security::clear_token();
        header('Location: gradebook_edit_certificate.php?'.api_get_cidreq());
        exit;
    }
}

$useDefault = false;
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

    if (!empty($infoCertificate)) {
        $useDefault = true;
    }
}

/*	Display user interface */
// Display the header
Display::display_header($nameTools);

$actionsLeft .= Display::url(
    Display::return_icon('certificate.png', get_lang('Certificate'), '', ICON_SIZE_MEDIUM),
    'gradebook_print_certificate.php?'.api_get_cidreq()
);

echo Display::toolbarAction(
    'toolbar-document',
    array($actionsLeft)
);

$form->addElement('html', '<fieldset><legend>'.strtoupper(get_lang('StudentCourseInfo')).'</legend>');
$form->addElement('html', '<div class="col-sm-8">');

$dir = '/';
$_course = api_get_course_info();
$is_allowed_to_edit = api_is_allowed_to_edit(null, true);
$editorConfig = [
        'ToolbarSet' => ($is_allowed_to_edit ? 'Documents' : 'DocumentsStudent'),
        'Width' => '100%',
        'Height' => '300',
        'cols-size' => [0, 12, 0],
        'FullPage' => true,
        'InDocument' => true,
        'CreateDocumentDir' => api_get_path(WEB_COURSE_PATH).$_course['path'].'/document/',
        'CreateDocumentWebDir' => api_get_path(WEB_COURSE_PATH).$_course['path'].'/document/',
        'BaseHref' => api_get_path(WEB_COURSE_PATH).$_course['path'].'/document'.$dir
];
$form->addHtmlEditor(
    'content_course',
    '',
    false,
    true,
    $editorConfig,
    true
);

$form->addElement('html', '</div>');
$form->addElement('html', '<div class="col-sm-4">');
$all_information_by_create_certificate = DocumentManager::get_all_info_to_certificate(
        api_get_user_id(),
        api_get_course_id()
        );

$str_info = '';
foreach ($all_information_by_create_certificate[0] as $info_value) {
    $str_info .= $info_value.'<br/>';
}
$create_certificate = get_lang('CreateCertificateWithTags');
$form->addElement('html', Display::return_message($create_certificate.': <br /><br/>'.$str_info, 'normal', false));
$form->addElement('html', '</div>');
$form->addElement('html', '</fieldset>');
$form->addElement('html', '<div class="clearfix"></div>');

$form->addElement('html', '<fieldset><legend>'.strtoupper(get_lang('Contents')).'</legend>');
$extra = '';
if (empty($infoCertificate['contents_type'])) {
    $infoCertificate['contents_type'] = 0;
    $extra = 'disabled';
}
$form->addElement('html', '<div class="form-group ">
                <label for="formEdit_campo2" class="col-sm-2 control-label">'.get_lang('ContentsToShow').'</label>
                <div class="col-sm-10">
                <div class="radio">
                    <label><input name="contents_type" value="0" id="contents_type_0" type="radio" onclick="javascript: contents_type_switch_radio_button();" '.(($infoCertificate['contents_type'] == "0") ? 'checked' : '').'>'.get_lang('ContentsCourseDescription').'</label>
                    <br>
                    <label><input name="contents_type" value="1" id="contents_type_1" type="radio" onclick="javascript: contents_type_switch_radio_button();" '.(($infoCertificate['contents_type'] == "1") ? 'checked' : '').'>'.get_lang('ContentsIndexLearnpath').'</label>
                    <br>
                    <label><input name="contents_type" value="2" id="contents_type_2" type="radio" onclick="javascript: contents_type_switch_radio_button();" '.(($infoCertificate['contents_type'] == "2") ? 'checked' : '').'>'.get_lang('ContentsCustom').'</label>
                </div>
                </div>
            </div>');
//$form->addElement('textarea', 'contents', get_lang('Contents'), ['id' => 'contents', 'cols-size' => [2, 10, 0], 'rows' => 9, $extra]);

$editorConfig = [
        'ToolbarSet' => ($is_allowed_to_edit ? 'Documents' : 'DocumentsStudent'),
        'Width' => '100%',
        'Height' => '200',
        'cols-size' => [2, 10, 0],
        'FullPage' => true,
        'InDocument' => true,
        'CreateDocumentDir' => api_get_path(WEB_COURSE_PATH).$_course['path'].'/document/',
        'CreateDocumentWebDir' => api_get_path(WEB_COURSE_PATH).$_course['path'].'/document/',
        'BaseHref' => api_get_path(WEB_COURSE_PATH).$_course['path'].'/document'.$dir,
        'id' => 'contents',
        $extra
];
$form->addHtmlEditor(
    'contents',
    get_lang('Contents'),
    false,
    true,
    $editorConfig,
    true
);


$form->addElement('html', '<fieldset><legend>'.strtoupper(get_lang("Dates")).'</legend>');
$form->addElement('html', '<div class="form-group" style="padding-top: 10px;">
                <label for="date_certificate" class="col-sm-3 control-label">'.get_lang('CourseDeliveryDates').'</label>
                <div class="col-sm-9">
                <div class="radio" style="margin-top: -20px;">
                    <label><input name="date_change" value="2" id="date_certificate" type="radio" onclick="javascript: date_certificate_switch_radio_button_1();" '.(($infoCertificate['date_change'] == "2") ? 'checked' : '').'>'.get_lang('None').'</label>
                    <br>
                    <label><input name="date_change" value="1" id="date_certificate" type="radio" onclick="javascript: date_certificate_switch_radio_button_2();" '.(($infoCertificate['date_change'] == "1") ? 'checked' : '').'>'.get_lang('Custom').'</label>
                    <span style="margin: 0 10px; font-style: italic;">'.get_lang('From').'</span>
                    <input size="20" autofocus="autofocus" class="form-control-cert text-center datepicker" name="date_start" id="date_start" type="text" value="'.(($infoCertificate['date_change'] == "1") ? date("d/m/Y", strtotime($infoCertificate['date_start'])) : '').'" '.(($infoCertificate['date_change'] == "0") ? 'disabled' : '').'>
                    <span style="margin: 0 10px; font-style: italic;">'.get_lang('Until').'</span>
                    <input size="20" class="form-control-cert text-center datepicker" name="date_end" id="date_end" type="text" value="'.(($infoCertificate['date_change'] == "1") ? date("d/m/Y", strtotime($infoCertificate['date_end'])) : '').'" '.(($infoCertificate['date_change'] == "0") ? 'disabled' : '').'>
                    <br>
                    <label><input name="date_change" value="0" id="date_certificate_off" type="radio" onclick="javascript: date_certificate_switch_radio_button_3();" '.(($infoCertificate['date_change'] == "0") ? 'checked' : '').' '.(($sessionId == 0) ? 'disabled' : '').'>'.get_lang('UseDateSessionAccess').'</label>
                </div>
                </div>
            </div>');

$form->addElement('html', '<div class="form-group ">
                <label for="formEdit_campo2" class="col-sm-3 control-label">'.get_lang('ExpectionPlace').'</label>
                <div class="col-sm-9">
                    <input autofocus="autofocus" class="form-control-cert" name="place" id="place" type="text" value="'.$infoCertificate['place'].'">
                </div>
            </div>');

$form->addElement('html', '<div class="form-group ">
                <label for="formEdit_campo2" class="col-sm-3 control-label">'.get_lang('DateExpediction').'</label>
                <div class="col-sm-9">
                <div class="radio">
                    <label style="margin-bottom:9px;"><input name="type_date_expediction" value="3" id="type_date_expediction_3" type="radio" onclick="javascript: type_date_expediction_switch_radio_button();" '.(($infoCertificate['type_date_expediction'] == "3") ? 'checked' : '').'>'.get_lang('None').'</label>
                    <br>
                    <label><input name="type_date_expediction" value="1" id="type_date_expediction_1" type="radio" onclick="javascript: type_date_expediction_switch_radio_button();" '.(($infoCertificate['type_date_expediction'] == "1") ? 'checked' : '').'>'.get_lang('UseDateDownloadCertificate').'</label>
                    <br>
                    <label><input name="type_date_expediction" value="2" id="type_date_expediction_2" type="radio" onclick="javascript: type_date_expediction_switch_radio_button();" '.(($infoCertificate['type_date_expediction'] == "2") ? 'checked' : '').'>'.get_lang('UseCustomDate').'</label>
                    <span class="certificado-text-label">a</span>
                    <input size="4" autofocus="autofocus" class="form-control-cert text-center" name="day" id="day" type="text" value="'.$infoCertificate['day'].'" '.(($infoCertificate['type_date_expediction'] != "2") ? 'disabled' : '').'>
                    <span class="certificado-text-label">de</span>
                    <input size="10" autofocus="autofocus" class="form-control-cert text-center" name="month" id="month" type="text" value="'.$infoCertificate['month'].'" '.(($infoCertificate['type_date_expediction'] != "2") ? 'disabled' : '').'>
                    <span class="certificado-text-label">de</span>
                    <input size="4" autofocus="autofocus" class="form-control-cert text-center" name="year" id="year" type="text" value="'.$infoCertificate['year'].'" '.(($infoCertificate['type_date_expediction'] != "2") ? 'disabled' : '').'>
                    <br>
                    <label><input name="type_date_expediction" value="0" id="type_date_expediction_0" type="radio" onclick="javascript: type_date_expediction_switch_radio_button();" '.(($infoCertificate['type_date_expediction'] == "0") ? 'checked' : '').' '.(($sessionId == 0) ? 'disabled' : '').'>'.get_lang('UseDateEndAccessSession').'</label>    
                </div>
                </div>
            </div>');
$form->addElement('html', '</fieldset>');

$base = api_get_path(WEB_UPLOAD_PATH);
if ($useDefault) {
    $path = $base.'certificates/default/';
} else {
    $path = $base.'certificates/'.$infoCertificate['id'].'/';
}

$form->addElement('html', '<fieldset><legend>'.strtoupper(get_lang('LogosSeal')).'</legend>');
// Logo 1
$form->addElement('html', '<div class="col-sm-6">');
$form->addFile(
        'logo_left',
        get_lang('LogoLeft'),
        array('id' => 'logo_left', 'class' => 'picture-form', 'crop_image' => true, 'crop_scalable' => 'true')
        );

$form->addProgress();
if (!empty($infoCertificate['logo_left'])) {
    $form->addElement('checkbox', 'remove_logo_left', null, get_lang('DelImage'));
    $form->addElement('html', '<label class="col-sm-2">&nbsp;</label><img src="'.$path.$infoCertificate['logo_left'].'" width="100"  /><br><br>');
}
$allowed_picture_types = api_get_supported_image_extensions(false);
$form->addRule(
        'logo_left',
        get_lang('OnlyImagesAllowed').' ('.implode(', ', $allowed_picture_types).')',
        'filetype',
        $allowed_picture_types
        );
$form->addElement('html', '</div>');
// Logo 2
$form->addElement('html', '<div class="col-sm-6">');
$form->addFile(
        'logo_center',
        get_lang('LogoCenter'),
        array('id' => 'logo_center', 'class' => 'picture-form', 'crop_image' => true, 'crop_scalable' => 'true')
        );

$form->addProgress();
if (!empty($infoCertificate['logo_center'])) {
    $form->addElement('checkbox', 'remove_logo_center', null, get_lang('DelImage'));
    $form->addElement('html', '<label class="col-sm-2">&nbsp;</label><img src="'.$path.$infoCertificate['logo_center'].'" width="100"  /><br><br>');
}
$allowed_picture_types = api_get_supported_image_extensions(false);
$form->addRule(
        'logo_center',
        get_lang('OnlyImagesAllowed').' ('.implode(', ', $allowed_picture_types).')',
        'filetype',
        $allowed_picture_types
        );
$form->addElement('html', '</div><div class="clearfix"></div>');
// Logo 3
$form->addElement('html', '<div class="col-sm-6">');
$form->addFile(
        'logo_right',
        get_lang('LogoRight'),
        array('id' => 'logo_right', 'class' => 'picture-form', 'crop_image' => true, 'crop_scalable' => 'true')
        );

$form->addProgress();
if (!empty($infoCertificate['logo_right'])) {
    $form->addElement('checkbox', 'remove_logo_right', null, get_lang('DelImage'));
    $form->addElement('html', '<label class="col-sm-2">&nbsp;</label><img src="'.$path.$infoCertificate['logo_right'].'" width="100"  /><br><br>');
}
$allowed_picture_types = api_get_supported_image_extensions(false);
$form->addRule(
        'logo_right',
        get_lang('OnlyImagesAllowed').' ('.implode(', ', $allowed_picture_types).')',
        'filetype',
        $allowed_picture_types
        );
$form->addElement('html', '</div>');

$form->addElement('html', '<div class="col-sm-6">');
$form->addFile(
        'seal',
        get_lang('Seal'),
        array('id' => 'seal', 'class' => 'picture-form', 'crop_image' => true, 'crop_scalable' => 'true')
        );

$form->addProgress();
if (!empty($infoCertificate['seal'])) {
    $form->addElement('checkbox', 'remove_seal', null, get_lang('DelImage'));
    $form->addElement('html', '<label class="col-sm-2">&nbsp;</label><img src="'.$path.$infoCertificate['seal'].'" width="100"  /><br><br>');
}
$allowed_picture_types = api_get_supported_image_extensions(false);
$form->addRule(
        'seal',
        get_lang('OnlyImagesAllowed').' ('.implode(', ', $allowed_picture_types).')',
        'filetype',
        $allowed_picture_types
        );
$form->addElement('html', '</div><div class="clearfix"></div>');
$form->addElement('html', '</fieldset>');


$form->addElement('html', '<fieldset><legend>'.strtoupper(get_lang('Signatures')).'</legend>');
// signature 1
$form->addElement('html', '<div class="col-sm-6">');
$form->addText('signature_text1', get_lang('SignatureText1'), false, array('cols-size' => [2, 10, 0], 'autofocus'));
$form->addFile(
        'signature1',
        get_lang('Signature1'),
        array('id' => 'signature1', 'class' => 'picture-form', 'crop_image' => true, 'crop_scalable' => 'true')
        );

$form->addProgress();
if (!empty($infoCertificate['signature1'])) {
    $form->addElement('checkbox', 'remove_signature1', null, get_lang('DelImage'));
    $form->addElement('html', '<label class="col-sm-2">&nbsp;</label><img src="'.$path.$infoCertificate['signature1'].'" width="100"  /><br><br>');
}
$allowed_picture_types = api_get_supported_image_extensions(false);
$form->addRule(
        'signature1',
        get_lang('OnlyImagesAllowed').' ('.implode(', ', $allowed_picture_types).')',
        'filetype',
        $allowed_picture_types
        );
$form->addElement('html', '</div>');

// signature 2
$form->addElement('html', '<div class="col-sm-6">');
$form->addText('signature_text2', get_lang('SignatureText2'), false, array('cols-size' => [2, 10, 0], 'autofocus'));
$form->addFile(
        'signature2',
        get_lang('Signature2'),
        array('id' => 'signature2', 'class' => 'picture-form', 'crop_image' => true, 'crop_scalable' => 'true')
        );

$form->addProgress();
if (!empty($infoCertificate['signature2'])) {
    $form->addElement('checkbox', 'remove_signature2', null, get_lang('DelImage'));
    $form->addElement('html', '<label class="col-sm-2">&nbsp;</label><img src="'.$path.$infoCertificate['signature2'].'" width="100"  /><br><br>');
}
$allowed_picture_types = api_get_supported_image_extensions(false);
$form->addRule(
        'signature2',
        get_lang('OnlyImagesAllowed').' ('.implode(', ', $allowed_picture_types).')',
        'filetype',
        $allowed_picture_types
        );
$form->addElement('html', '</div><div class="clearfix"></div>');

// signature 3
$form->addElement('html', '<div class="col-sm-6">');
$form->addText('signature_text3', get_lang('SignatureText3'), false, array('cols-size' => [2, 10, 0], 'autofocus'));
$form->addFile(
        'signature3',
        get_lang('Signature3'),
        array('id' => 'signature3', 'class' => 'picture-form', 'crop_image' => true, 'crop_scalable' => 'true')
        );

$form->addProgress();
if (!empty($infoCertificate['signature3'])) {
    $form->addElement('checkbox', 'remove_signature3', null, get_lang('DelImage'));
    $form->addElement('html', '<label class="col-sm-2">&nbsp;</label><img src="'.$path.$infoCertificate['signature3'].'" width="100"  /><br><br>');
}
$allowed_picture_types = api_get_supported_image_extensions(false);
$form->addRule(
        'signature3',
        get_lang('OnlyImagesAllowed').' ('.implode(', ', $allowed_picture_types).')',
        'filetype',
        $allowed_picture_types
        );
$form->addElement('html', '</div>');

// signature 4
$form->addElement('html', '<div class="col-sm-6">');
$form->addText('signature_text4', get_lang('SignatureText4'), false, array('cols-size' => [2, 10, 0], 'autofocus'));
$form->addFile(
        'signature4',
        get_lang('Signature4'),
        array('id' => 'signature4', 'class' => 'picture-form', 'crop_image' => true, 'crop_scalable' => 'true')
        );

$form->addProgress();
if (!empty($infoCertificate['signature4'])) {
    $form->addElement('checkbox', 'remove_signature4', null, get_lang('DelImage'));
    $form->addElement('html', '<label class="col-sm-2">&nbsp;</label><img src="'.$path.$infoCertificate['signature4'].'" width="100"  /><br><br>');
}
$allowed_picture_types = api_get_supported_image_extensions(false);
$form->addRule(
        'signature4',
        get_lang('OnlyImagesAllowed').' ('.implode(', ', $allowed_picture_types).')',
        'filetype',
        $allowed_picture_types
        );
$form->addElement('html', '</div><div class="clearfix"></div>');
$form->addElement('html', '</fieldset><br>');

$form->addElement('html', '<div class="col-sm-6">');
$form->addElement('html', '<fieldset><legend>'.strtoupper(get_lang('BackgroundCertificate')).'</legend>');
// background
$form->addFile(
        'background',
        get_lang('Background'),
        array('id' => 'background', 'class' => 'picture-form', 'crop_image' => true, 'crop_ratio' => '297 / 210')
        );

$form->addProgress();
if (!empty($infoCertificate['background'])) {
    $form->addElement('checkbox', 'remove_background', null, get_lang('DelImage'));
    $form->addElement('html', '<label class="col-sm-2">&nbsp;</label><img src="'.$path.$infoCertificate['background'].'" width="100"  /><br><br>');
}
$allowed_picture_types = api_get_supported_image_extensions(false);
$form->addRule(
        'background',
        get_lang('OnlyImagesAllowed').' ('.implode(', ', $allowed_picture_types).')',
        'filetype',
        $allowed_picture_types
        );
$form->addElement('html', '</fieldset>');
$form->addElement('html', '</div>');

$form->addElement('html', '<div class="col-sm-6">');
$form->addElement('html', '<fieldset><legend>'.strtoupper(get_lang('OtherOptions')).'</legend>');
$marginOptions = array();
$i = 0;
while ($i < 298) {
    $marginOptions[$i] = $i.' mm';
    $i++;
}
$form->addElement(
    'select',
    'margin',
    get_lang('MarginRight'),
    $marginOptions,
    array('cols-size' => [4, 8, 0])
);

$form->addElement('html', '<div class="form-group ">
    <label class="col-sm-4 control-label">'.get_lang('SetDefaultTemplate').'</label>
    <div class="col-sm-8">
    <div class="checkbox">
        <label>
        <input cols-size="" name="defecto" value="1" type="checkbox" '.(($_GET['pordefecto'] == 1) ? 'checked' : '').'>
        <p class="help-block">'.get_lang('MessageDefaultTemplate').'</p>
        </label>
    </div>
    </div>
    </div>'
);
$form->addElement('html', '</fieldset>');
$form->addElement('html', '</div>');
$form->addElement('html', '<div class="clearfix"></div>');


$form->addElement('html', '
                <div class="form-group ">
                    <div class="col-sm-12 text-center">
                        <button class=" btn btn-primary" name="" type="submit">
                            <em class="fa fa-pencil"></em> '.get_lang('SaveCertificate').'
                        </button>
                    </div>
                </div>');

$form->addElement('hidden', 'formSent');

$infoCertificate['formSent'] = 1;
$form->setDefaults($infoCertificate);

$token = Security::get_token();
$form->addElement('hidden', 'sec_token');
$form->addElement('hidden', 'use_default');
$form->setConstants(array('sec_token' => $token, 'use_default' => $useDefault));

echo '<div class="page-create">
        <div class="row" style="overflow:hidden">
        <div id="doc_form" class="col-md-12">
            '.$form->returnForm().'
        </div>
      </div></div>';
Display::display_footer();

function uploadImageCertificate($certId, $file = null, $source_file = null, $cropParameters = '', $default = false)
{
    if (empty($certId)) {
        return false;
    }
    $delete = empty($file);
    if (empty($source_file)) {
        $source_file = $file;
    }
    
    $base = api_get_path(SYS_UPLOAD_PATH);
    $path = $base.'certificates/'.$certId.'/';
    if ($default) {
        $path = $base.'certificates/default/';
    }
    
    // If this directory does not exist - we create it.
    if (!file_exists($path)) {
        mkdir($path, api_get_permissions_for_new_directories(), true);
    }

    // Exit if only deletion has been requested. Return an empty picture name.
    if ($delete) {
        return '';
    }

    $allowed_types = api_get_supported_image_extensions();
    $file = str_replace('\\', '/', $file);
    $filename = (($pos = strrpos($file, '/')) !== false) ? substr($file, $pos + 1) : $file;
    $extension = strtolower(substr(strrchr($filename, '.'), 1));
    if (!in_array($extension, $allowed_types)) {
        return false;
    }
    
    $filename = api_replace_dangerous_char($filename);
    $filename = uniqid('').'_'.$filename;
    $filename = $certId.'_'.$filename;
    
    
    //Crop the image to adjust 1:1 ratio
    $image = new Image($source_file);
    $image->crop($cropParameters);
    
    $origin = new Image($source_file); // This is the original picture.
    $origin->send_image($path.$filename);
    
    $result = $origin;
    
    return $result ? $filename : false;
}
