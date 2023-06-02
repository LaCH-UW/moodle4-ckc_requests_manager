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
// Copyright 2012-2018 - Institute of Technology Blanchardstown.
// ---------------------------------------------------------
/**
 * COURSE REQUEST MANAGER
 *
 * @package   block_ckc_requests_manager
 * @copyright 2018 Kyle Goslin, Daniel McSweeney
 * @copyright 2021-2022 Michael Milette (TNG Consulting Inc.), Daniel Keaman
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once '../../../config.php';
global $GLOBALS['CFG'];
$formPath = "$GLOBALS['CFG']->libdir/formslib.php";
require_once $formPath;
require_login();

$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_url('/blocks/ckc_requests_manager/admin/comment.php');

// Navigation Bar
$PAGE->navbar->ignore_active();
$PAGE->navbar->add(get_string('cmanagerDisplay', 'block_ckc_requests_manager'), new moodle_url('/blocks/ckc_requests_manager/cmanager_admin.php'));
$PAGE->navbar->add(get_string('currentrequests', 'block_ckc_requests_manager'), new moodle_url('/cmanager_admin.php'));
$PAGE->navbar->add(get_string('addviewcomments', 'block_ckc_requests_manager'));
$PAGE->set_heading(get_string('addviewcomments', 'block_ckc_requests_manager'));
$PAGE->set_title(get_string('addviewcomments', 'block_ckc_requests_manager'));
echo $OUTPUT->header();

$context = context_system::instance();
if (has_capability('block/cmanager:addcomment', $context)) {
} else {
    print_error(get_string('cannotcomment', 'block_ckc_requests_manager'));
}



if (isset($_GET['id'])) {
    $mid             = required_param('id', PARAM_INT);
    $_SESSION['mid'] = $mid;
} else {
    $mid = $_SESSION['mid'];
}

$type = optional_param('type', '', PARAM_TEXT);
if (!empty($type)) {
    $_SESSION['type'] = $type;
} else {
    $type = '';
    $type = $_SESSION['type'];
}

$backLink = '';
if ($type == 'adminarch') {
    $backLink = '../cmanager_admin_arch.php';
} else if ($type == 'adminq') {
    $backLink = '../cmanager_admin.php';
}

$PAGE->set_url('/blocks/ckc_requests_manager/admin/comment.php', ['id' => $mid]);

class block_ckc_requests_manager_comment_form extends moodleform
{


    function definition()
    {
        global $GLOBALS['CFG'];
        global $currentSess;
        global $mid;
        global $GLOBALS['USER'];
        global $GLOBALS['DB'];
        global $backLink;

        $currentRecord = $GLOBALS['DB']->get_record('block_ckc_requests_manager_records', ['id' => $currentSess]);
        $mform         =& $this->_form;
        // Don't forget the underscore!
        // Page description text
        $mform->addElement('html', '<p><a href="'.$backLink.'" class="btn btn-default"><img src="../icons/back.png" alt=""> '.get_string('back', 'block_ckc_requests_manager').'</a></p>');
        $mform->addElement('html', '<p>'.get_string('comments_Forward', 'block_ckc_requests_manager').'.</p>');

        // Add a comment box.
        $mform->addElement(
            'html',
            '
                <textarea id="newcomment" name="newcomment" rows="5" cols="60"></textarea><br>
                <input class="btn btn-default mt-3" type="submit" value="'.get_string('comments_PostComment', 'block_ckc_requests_manager').'">
        '
        );

        // Previous comments.
        $whereQuery = "instanceid = '$mid'  ORDER BY id DESC";
        $modRecords = $GLOBALS['DB']->get_recordset_select('block_ckc_requests_manager_comments', $whereQuery);
        $htmlOutput = '<h2 class="h4 mt-3 p-2" style="border: 1px #000000 solid; width:100%; background: #E0E0E0">'.get_string('comments_comment', 'block_ckc_requests_manager').'</h2>';
        foreach ($modRecords as $record) {
            $createdbyid         = $record->createdbyid;
            $GLOBALS['USER']name = $GLOBALS['DB']->get_field_select('user', 'username', "id = '$createdbyid'");
            $htmlOutput         .= '<p><strong>'.get_string('comments_date', 'block_ckc_requests_manager').':</strong> '.$record->dt.'</p>';
            $htmlOutput         .= '<p><strong>'.get_string('comments_author', 'block_ckc_requests_manager').':</strong> '.$GLOBALS['USER']name.'</p>';
            $htmlOutput         .= '<p><strong>'.get_string('comments_comment', 'block_ckc_requests_manager').':</strong> '.$record->message.'</p>';
            $htmlOutput         .= '<hr>';
        }

        $mform->addElement('html', $htmlOutput);

    }//end definition()


}//end class

$mform = new block_ckc_requests_manager_comment_form();
// name of the form you defined in file above.
if ($mform->is_cancelled()) {
    echo "<script>window.location='".$backLink."';</script>";
    die;
} else if ($fromform = $mform->get_data()) {
} else {
    $mform->focus();
    $mform->set_data($mform);
    $mform->display();
    echo $OUTPUT->footer();
}

if ($_POST) {
    global $GLOBALS['USER'], $GLOBALS['CFG'], $GLOBALS['DB'], $mid;

    $GLOBALS['USER']id = $GLOBALS['USER']->id;

    $newrec              = new stdClass();
    $newrec->instanceid  = $mid;
    $newrec->createdbyid = $GLOBALS['USER']id;
    $newrec->message     = $_POST['newcomment'];
    $newrec->dt          = date('Y-m-d H:i:s');
    $GLOBALS['DB']->insert_record('block_ckc_requests_manager_comments', $newrec, false);

    // Send an email to everyone concerned.
    include_once '../cmanager_email.php';
    $message = required_param('newcomment', PARAM_TEXT);

    // Get all user id's from the record
    $currentRecord = $GLOBALS['DB']->get_record('block_ckc_requests_manager_records', ['id' => $mid]);



    $GLOBALS['USER']_ids = '';
    // Used to store all the user IDs for the people we need to email.
    $GLOBALS['USER']_ids = $currentRecord->createdbyid;
    // Add the current user
    // Get info about the current object.
    // Send email to the user
    $replaceValues                  = [];
    $replaceValues['[course_code']  = $currentRecord->modcode;
    $replaceValues['[course_name]'] = $currentRecord->modname;
    // $replaceValues['[p_code]'] = $currentRecord->progcode;
    // $replaceValues['[p_name]'] = $currentRecord->progname;
    $replaceValues['[e_key]']     = '';
    $replaceValues['[full_link]'] = $GLOBALS['CFG']->wwwroot.'/blocks/ckc_requests_manager/comment.php?id='.$mid;
    $replaceValues['[loc]']       = '';
    $replaceValues['[req_link]']  = $GLOBALS['CFG']->wwwroot.'/blocks/ckc_requests_manager/view_summary.php?id='.$mid;


    block_ckc_requests_manager_email_comment_to_user($message, $GLOBALS['USER']_ids, $mid, $replaceValues);
    block_ckc_requests_manager_email_comment_to_admin($message, $mid, $replaceValues);

    echo "<script> window.location = 'comment.php?type=".$type."&id=$mid';</script>";
}//end if
