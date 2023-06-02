<?php
// ---------------------------------------------------------
// block_ckc_requests_manager is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// block_ckc_requests_manager is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.
//
// COURSE REQUEST MANAGER BLOCK FOR MOODLE
// by Kyle Goslin & Daniel McSweeney
// Copyright 2012-2014 - Institute of Technology Blanchardstown.
// ---------------------------------------------------------
/**
 * COURSE REQUEST MANAGER
 *
 * @package   block_ckc_requests_manager
 * @copyright 2018 Kyle Goslin, Daniel McSweeney
 * @copyright 2021-2022 Michael Milette (TNG Consulting Inc.), Daniel Keaman
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once '../../config.php';
global $GLOBALS['CFG'], $GLOBALS['DB'];
require_once "$GLOBALS['CFG']->libdir/formslib.php";

require_login();
require_once 'validate_admin.php';
require_once 'lib/boot.php';

$PAGE->set_url('/blocks/ckc_requests_manager/cmanager_config.php');
$PAGE->set_context(context_system::instance());


// Navigation Bar
$PAGE->navbar->ignore_active();
$PAGE->navbar->add(get_string('cmanagerDisplay', 'block_ckc_requests_manager'), new moodle_url('/blocks/ckc_requests_manager/cmanager_admin.php'));
$PAGE->navbar->add(get_string('configurecoursemanagersettings', 'block_ckc_requests_manager'), new moodle_url('/blocks/ckc_requests_manager/cmanager_confighome.php'));
$PAGE->navbar->add(get_string('emailConfig', 'block_ckc_requests_manager'));
$PAGE->set_heading(get_string('configureemailsettings', 'block_ckc_requests_manager'));
$PAGE->set_title(get_string('configureemailsettings', 'block_ckc_requests_manager'));
echo $OUTPUT->header();



$context = context_system::instance();
if (has_capability('block/cmanager:viewconfig', $context)) {
} else {
    print_error(get_string('cannotviewconfig', 'block_ckc_requests_manager'));
}


?>
<head>


<link rel="stylesheet" type="text/css" href="css/main.css" />
<script src="js/jquery/jquery-3.3.1.min.js"></script>
<script src="js/jquery/jquery-ui.1.12.1.min.js"></script>


<script>
function cancelConfirm(i,langString) {
    var answer = confirm(langString)
    if (answer){

        window.location = "cmanager_config.php?t=d&&id=" + i;
    }
    else{

    }
}

/**
 * This function is used to save the text from the
 * list of textareas using ajax.
 */
function saveChangedText(object, idname, langString){

    var fieldvalue = object.value;


    $.post("ajax_functions.php", { type: 'updatefield', value: fieldvalue, id: idname },
               function(data) {
                   $("#saved").modal();
               });

}

</script>

</head>

<?php
// If any records were set to be deleted.
if (isset($_GET['t']) && isset($_GET['id'])) {
    if (required_param('t', PARAM_TEXT) == 'd') {
        $deleteId = required_param('id', PARAM_INT);
        // Delete the record
        $deleteQuery = "id = $deleteId";
        $GLOBALS['DB']->delete_records_select('block_ckc_requests_manager_config', $deleteQuery);
        echo "<script>window.location='cmanager_config.php';</script>";
    }
}

/**
 * Config form form cmanager
 *
 * @package   block_ckc_requests_manager
 * @copyright 2018 Kyle Goslin, Daniel McSweeney
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_ckc_requests_manager_config_form extends moodleform
{


    function definition()
    {
        global $GLOBALS['CFG'];
        global $currentSess;
        global $mid;
        global $GLOBALS['USER'], $GLOBALS['DB'];

        $currentRecord = $GLOBALS['DB']->get_record('block_ckc_requests_manager_records', ['id' => $currentSess]);
        $mform         =& $this->_form;
        // Don't forget the underscore!
        // Back Button
        $mform->addElement('html', '<p><a href="cmanager_confighome.php" class="btn btn-default"><img src="icons/back.png" alt=""> '.get_string('back', 'block_ckc_requests_manager').'</a></p>');

        // Email text box
        $approvedTextRecord = $GLOBALS['DB']->get_record('block_ckc_requests_manager_config', ['varname' => 'approved_text']);

        $emailText = '';
        if ($approvedTextRecord != null) {
            $emailText = $approvedTextRecord->value;
        }

        // Approved user email
        $approved_user_email       = $GLOBALS['DB']->get_record('block_ckc_requests_manager_config', ['varname' => 'approveduseremail']);
        $approved_user_email_value = '';
        if (!empty($approved_user_email)) {
            $approved_user_email_value = stripslashes($approved_user_email->value);
        }

        // Approved admin email
        $approved_admin_email       = $GLOBALS['DB']->get_record('block_ckc_requests_manager_config', ['varname' => 'approvedadminemail']);
        $approved_admin_email_value = '';
        if (!empty($approved_admin_email)) {
            $approved_admin_email_value = stripslashes($approved_admin_email->value);
        }

        // Request new module user
        $request_new_module_user       = $GLOBALS['DB']->get_record('block_ckc_requests_manager_config', ['varname' => 'requestnewmoduleuser']);
        $request_new_module_user_value = '';
        if (!empty($request_new_module_user)) {
            $request_new_module_user_value = stripslashes($request_new_module_user->value);
        }

        // Request new module admin
        $request_new_module_admin       = $GLOBALS['DB']->get_record('block_ckc_requests_manager_config', ['varname' => 'requestnewmoduleadmin']);
        $request_new_module_admin_value = '';
        if (!empty($request_new_module_admin)) {
            $request_new_module_admin_value = stripslashes($request_new_module_admin->value);
        }

        // Comment email admin
        $comment_email_admin       = $GLOBALS['DB']->get_record('block_ckc_requests_manager_config', ['varname' => 'commentemailadmin']);
        $comment_email_admin_value = '';
        if (!empty($comment_email_admin)) {
            $comment_email_admin_value = stripslashes($comment_email_admin->value);
        }

        // Comment email user
        $comment_email_user       = $GLOBALS['DB']->get_record('block_ckc_requests_manager_config', ['varname' => 'commentemailuser']);
        $comment_email_user_value = '';
        if (!empty($comment_email_user)) {
            $comment_email_user_value = stripslashes($comment_email_user->value);
        }

        // Request denied admin
        $module_request_denied_admin       = $GLOBALS['DB']->get_record('block_ckc_requests_manager_config', ['varname' => 'modulerequestdeniedadmin']);
        $module_request_denied_admin_value = '';
        if (!empty($module_request_denied_admin)) {
            $module_request_denied_admin_value = stripslashes($module_request_denied_admin->value);
        }

        // Request denied user
        $module_request_denied_user       = $GLOBALS['DB']->get_record('block_ckc_requests_manager_config', ['varname' => 'modulerequestdenieduser']);
        $module_request_denied_user_value = '';
        if (!empty($module_request_denied_user)) {
            $module_request_denied_user_value = stripslashes($module_request_denied_user->value);
        }

        // Handover current
        $handover_current       = $GLOBALS['DB']->get_record('block_ckc_requests_manager_config', ['varname' => 'handovercurrent']);
        $handover_current_value = '';
        if (!empty($handover_current)) {
            $handover_current_value = stripslashes($handover_current->value);
        }

        // Handover user
        $handover_user       = $GLOBALS['DB']->get_record('block_ckc_requests_manager_config', ['varname' => 'handoveruser']);
        $handover_user_value = '';
        if (!empty($handover_user)) {
             $handover_user_value = stripslashes($handover_user->value);
        }

        // Handover admin
        $handover_admin       = $GLOBALS['DB']->get_record('block_ckc_requests_manager_config', ['varname' => 'handoveradmin']);
        $handover_admin_value = '';
        if (!empty($handover_admin)) {
            $handover_admin_value = stripslashes($handover_admin->value);
        }

        $statsCode  = get_string('totalRequests', 'block_ckc_requests_manager').':';
        $whereQuery = "varname = 'admin_email'";
        $modRecords = $GLOBALS['DB']->get_recordset_select('block_ckc_requests_manager_config', $whereQuery);

        // get the current values for naming and autoKey from the database and use in the setting of seleted values for dropdowns
        $autoKey     = $GLOBALS['DB']->get_field_select('block_ckc_requests_manager_config', 'value', "varname = 'autoKey'");
        $naming      = $GLOBALS['DB']->get_field_select('block_ckc_requests_manager_config', 'value', "varname = 'naming'");
        $snaming     = $GLOBALS['DB']->get_field_select('block_ckc_requests_manager_config', 'value', "varname = 'snaming'");
        $emailSender = $GLOBALS['DB']->get_field_select('block_ckc_requests_manager_config', 'value', "varname = 'emailSender'");

        $selfcat = $GLOBALS['DB']->get_field_select('block_ckc_requests_manager_config', 'value', "varname = 'selfcat'");

                $fragment1 = '
        <h3>'.get_string('emailConfigSectionHeader', 'block_ckc_requests_manager').'</h3>
        <p>'.get_string('emailConfigInfo', 'block_ckc_requests_manager').'</p>
		<div class="row">
			<div class="col-sm-1">'.get_string('config_addemail', 'block_ckc_requests_manager').'</div>
			<div class="col-sm-3">';
        foreach ($modRecords as $record) {
            $fragment1 .= '<div class="row">';
            $fragment1 .= '<div class="col-sm-9">'.$record->value.'</div>';
            $fragment1 .= '<div class="col-sm-3"><a onclick="cancelConfirm('.$record->id.',\''.get_string('configure_deleteMail', 'block_ckc_requests_manager').'\')" href="#" aria-label="'.get_string('formBuilder_confirmDelete', 'block_ckc_requests_manager').'" title="'.get_string('formBuilder_confirmDelete', 'block_ckc_requests_manager').'"><i class="icon fa fa-trash fa-fw" aria-hidden="true"></i></a></div>';
            $fragment1 .= '</div>';
        }

        $fragment1 .= '
                <div class="row">
                    <div class="col-sm-12">
                        <input type="text" name="newemail" id="newemail"/>
                        <input class="btn btn-default" type="submit" name="addemailbutton" id="addemailbutton" value="'.get_string('SaveEMail', 'block_ckc_requests_manager').'">
                    </div>
                </div>
            </div>
        </div>

        <h3 class="mt-4">'.get_string('emailConfigContents', 'block_ckc_requests_manager').'</h3>
        <p>'.get_string('emailConfigHeader', 'block_ckc_requests_manager').'</p>
        <ul>
            <li>'.get_string('email_courseCode', 'block_ckc_requests_manager').': <strong>[course_code]</strong></li>
            <li>'.get_string('email_courseName', 'block_ckc_requests_manager').': <strong>[course_name]</strong></li>
            <li>'.get_string('email_enrolmentKey', 'block_ckc_requests_manager').': <strong>[e_key]</strong></li>
            <li>'.get_string('email_fullURL', 'block_ckc_requests_manager').': <strong>[full_link]</strong></li>
            <li>'.get_string('email_sumLink', 'block_ckc_requests_manager').': <strong>[req_link]</strong></li>
        </ul>

        <h3 class="mt-4">'.get_string('email_newCourseApproved', 'block_ckc_requests_manager').' - '.get_string('email_UserMail', 'block_ckc_requests_manager').'</h3>
        <p>'.get_string('configure_leaveblankmail', 'block_ckc_requests_manager').'</p>
        <textarea name="approveduseremail" id="approveduseremail"  style="width:70%; height: 250px;">'.$approved_user_email_value.'</textarea><br>
        <input class="btn btn-default" type="button" value="'.get_string('SaveChanges', 'block_ckc_requests_manager').'" onClick="saveChangedText(approveduseremail, \'approveduseremail\',\''.get_string('ChangesSaved', 'block_ckc_requests_manager').'\')"/><br>

        <h3 class="mt-4">'.get_string('email_newCourseApproved', 'block_ckc_requests_manager').' - '.get_string('email_AdminMail', 'block_ckc_requests_manager').'</h3>
        <p> '.get_string('configure_leaveblankmail', 'block_ckc_requests_manager').'</p>
        <textarea name="approvedadminemail" id="approvedadminemail" style="width:70%; height: 250px;">'.$approved_admin_email_value.'</textarea><br>
        <input class="btn btn-default" type="button" value="'.get_string('SaveChanges', 'block_ckc_requests_manager').'" onClick="saveChangedText(approvedadminemail, \'approvedadminemail\')"/><br>

        <h3 class="mt-4">'.get_string('email_requestNewModule', 'block_ckc_requests_manager').' - '.get_string('email_UserMail', 'block_ckc_requests_manager').'</h3>
        <p>'.get_string('configure_leaveblankmail', 'block_ckc_requests_manager').'</p>
        <textarea name="requestnewmoduleuser" id="requestnewmoduleuser" style="width:70%; height: 250px;">'.$request_new_module_user_value.'</textarea><br>
        <input class="btn btn-default" type="button" value="'.get_string('SaveChanges', 'block_ckc_requests_manager').'" onClick="saveChangedText(requestnewmoduleuser, \'requestnewmoduleuser\')"/><br>

        <h3 class="mt-4">'.get_string('email_requestNewModule', 'block_ckc_requests_manager').' - '.get_string('email_AdminMail', 'block_ckc_requests_manager').'</h3>
        <p>'.get_string('configure_leaveblankmail', 'block_ckc_requests_manager').'</p>
        <textarea name="requestnewmoduleadmin" id="requestnewmoduleadmin" style="width:70%; height: 250px;">'.$request_new_module_admin_value.'</textarea><br>
        <input class="btn btn-default" type="button" value="'.get_string('SaveChanges', 'block_ckc_requests_manager').'" onClick="saveChangedText(requestnewmoduleadmin, \'requestnewmoduleadmin\')"/>

        <h3 class="mt-4">'.get_string('email_commentNotification', 'block_ckc_requests_manager').' - '.get_string('email_AdminMail', 'block_ckc_requests_manager').'</h3>
        <p>'.get_string('configure_leaveblankmail', 'block_ckc_requests_manager').'</p>
        <textarea name="commentemailadmin" id="commentemailadmin" style="width:70%; height: 250px;">'.$comment_email_admin_value.'</textarea><br>
        <input class="btn btn-default" type="button" value="'.get_string('SaveChanges', 'block_ckc_requests_manager').'" onClick="saveChangedText(commentemailadmin, \'commentemailadmin\')"/>

        <h3 class="mt-4">'.get_string('email_commentNotification', 'block_ckc_requests_manager').' - '.get_string('email_UserMail', 'block_ckc_requests_manager').'</h3>
        <p>'.get_string('configure_leaveblankmail', 'block_ckc_requests_manager').'</p>
        <textarea name="commentemailuser" id="commentemailuser" style="width:70%; height: 250px;">'.$comment_email_user_value.'</textarea><br>
        <input class="btn btn-default" type="button" value="'.get_string('SaveChanges', 'block_ckc_requests_manager').'" onClick="saveChangedText(commentemailuser, \'commentemailuser\')"/>

        <h3 class="mt-4">'.get_string('email_requestDenied', 'block_ckc_requests_manager').' - '.get_string('email_AdminMail', 'block_ckc_requests_manager').'</h3>
        <p>'.get_string('configure_leaveblankmail', 'block_ckc_requests_manager').'</p>
        <textarea name="modulerequestdeniedadmin" id="modulerequestdeniedadmin" style="width:70%; height: 250px;">'.$module_request_denied_admin_value.'</textarea><br>
        <input class="btn btn-default" type="button" value="'.get_string('SaveChanges', 'block_ckc_requests_manager').'" onClick="saveChangedText(modulerequestdeniedadmin, \'modulerequestdeniedadmin\')"/>

        <h3 class="mt-4">'.get_string('email_requestDenied', 'block_ckc_requests_manager').' - '.get_string('email_UserMail', 'block_ckc_requests_manager').'</h3>
        <p>'.get_string('configure_leaveblankmail', 'block_ckc_requests_manager').'</p>
        <textarea name="modulerequestdenieduser" id="modulerequestdenieduser" style="width:70%; height: 250px;">'.$module_request_denied_user_value.'</textarea><br>
        <input class="btn btn-default" type="button" value="'.get_string('SaveChanges', 'block_ckc_requests_manager').'" onClick="saveChangedText(modulerequestdenieduser, \'modulerequestdenieduser\')"/>

        <h3 class="mt-4">'.get_string('email_handover', 'block_ckc_requests_manager').' - '.get_string('email_currentOwner', 'block_ckc_requests_manager').'</h3>
        <p>'.get_string('configure_leaveblankmail', 'block_ckc_requests_manager').'</p>
        <textarea name="handovercurrent" id="handovercurrent" style="width:70%; height: 250px;">'.$handover_current_value.'</textarea><br>
        <input class="btn btn-default" type="button" value="'.get_string('SaveChanges', 'block_ckc_requests_manager').'" onClick="saveChangedText(handovercurrent, \'handovercurrent\')"/>

        <h3 class="mt-4">'.get_string('email_handover', 'block_ckc_requests_manager').' - '.get_string('email_UserMail', 'block_ckc_requests_manager').'</h3>
        <p>'.get_string('configure_leaveblankmail', 'block_ckc_requests_manager').'</p>
        <textarea name="handoveruser" id="handoveruser" style="width:70%; height: 250px;">'.$handover_user_value.'</textarea><br>
        <input class="btn btn-default" type="button" value="'.get_string('SaveChanges', 'block_ckc_requests_manager').'" onClick="saveChangedText(handoveruser, \'handoveruser\')"/>

        <h3 class="mt-4">'.get_string('email_handover', 'block_ckc_requests_manager').' - '.get_string('email_AdminMail', 'block_ckc_requests_manager').'</h3>
        <p>'.get_string('configure_leaveblankmail', 'block_ckc_requests_manager').'</p>
        <textarea name="handoveradmin" id="handoveradmin" style="width:70%; height: 250px;">'.$handover_admin_value.'</textarea><br>
        <input class="btn btn-default" type="button" value="'.get_string('SaveChanges', 'block_ckc_requests_manager').'" onClick="saveChangedText(handoveradmin, \'handoveradmin\')"/>
    ';

                $mainSlider = '
    <p></p>
    &nbsp;
    <p></p>
    '.$fragment1.'


    ';

        // add the main slider
        $mform->addElement('html', $mainSlider);

    }//end definition()


}//end class

$mform = new block_ckc_requests_manager_config_form();

if ($mform->is_cancelled()) {
    echo "<script>window.location='../cmanager_admin.php';</script>";
    die;
} else if (isset($_POST['addemailbutton'])) {
    global $GLOBALS['USER'];
    global $GLOBALS['CFG'];

    // Add an email address
    $post_email = required_param('newemail', PARAM_EMAIL);
    if ($post_email != '' && block_ckc_requests_manager_validate_email($post_email)) {
        $newrec          = new stdClass();
        $newrec->varname = 'admin_email';
        $newrec->value   = $post_email;
        $GLOBALS['DB']->insert_record('block_ckc_requests_manager_config', $newrec);
    }

    echo "<script>window.location='cmanager_config.php';</script>";
    die;
} else {
    $mform->focus();
    $mform->set_data($mform);
    $mform->display();
    echo $OUTPUT->footer();
}//end if

    echo generateGenericPop('saved', get_string('ChangesSaved', 'block_ckc_requests_manager'), get_string('ChangesSaved', 'block_ckc_requests_manager'), get_string('ok', 'block_ckc_requests_manager'));


/**
 * Very basic funciton for validating an email address.
 * This should really be replaced with something a little better!
 */
function block_ckc_requests_manager_validate_email($email)
{
    $valid = true;

    if ($email == '') {
        $valid = false;
    }

    $pos = strpos($email, '.');
    if ($pos === false) {
        $valid = false;
    }

    $pos = strpos($email, '@');
    if ($pos === false) {
        $valid = false;
    }

    if ($valid) {
        return true;
    } else {
        return false;
    }

}//end block_ckc_requests_manager_validate_email()

