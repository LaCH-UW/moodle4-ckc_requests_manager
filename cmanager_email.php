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
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// require cfg was here
require_once $GLOBALS['CFG']->dirroot.'/lib/moodlelib.php';
require_once 'lib.php';

global $GLOBALS['DB'];
$senderemailaddress = $GLOBALS['DB']->get_field('block_ckc_requests_manager_config', 'value', ['varname' => 'emailsender'], IGNORE_MULTIPLE);

$emailsender              = new stdClass();
$emailsender->id          = 1;
$emailsender->email       = $senderemailaddress;
$emailsender->maildisplay = true;


/**
 * Preform a search and replace for any value tags
 * which were entered by the admin.
 */
function block_ckc_requests_manager_convert_tags_to_values($email, $replacevalues)
{
    // Course code: [course_code]
    $course_code_added = str_replace('[course_code]', $replacevalues['[course_code'], $email);

    // Course name: [course_name]
    $course_name_added = str_replace('[course_name]', $replacevalues['[course_name]'], $course_code_added);

    // Enrolment key: [e_key]
    $enroll_key_added = str_replace('[e_key]', $replacevalues['[e_key]'], $course_name_added);

    // Full URL to module: [full_link]
    $full_url_added = str_replace('[full_link]', $replacevalues['[full_link]'], $enroll_key_added);

    $req_link_added = str_replace('[req_link]', $replacevalues['[req_link]'], $full_url_added);

    // Location in catalog: [loc]
    $location_added = str_replace('[loc]', $replacevalues['[loc]'], $req_link_added);

    $new_email = $location_added;

    return $new_email;

}//end block_ckc_requests_manager_convert_tags_to_values()


/**
 * When a new course is approved email the user
 */
function block_ckc_requests_manager_new_course_approved_mail_user($uids, $current_mod_info)
{
    global $GLOBALS['USER'];
    global $GLOBALS['CFG'];
    global $GLOBALS['DB'];
    global $senderemailaddress;

    $uidarray = explode(' ', $uids);

    foreach ($uidarray as $singleid) {
        $emailinguserobject = $GLOBALS['DB']->get_record('user', ['id' => $singleid]);
        $subject            = get_string('emailSubj_userApproved', 'block_ckc_requests_manager');
        $rec                = $GLOBALS['DB']->get_record('block_ckc_requests_manager_config', ['varname' => 'approveduseremail']);

        if (strlen(trim($rec->value)) > 0) {
            // are there characters in the field.
            $messagetext       = block_ckc_requests_manager_convert_tags_to_values($rec->value, $current_mod_info);
            email_to_user(
                $emailinguserobject,
                $senderemailaddress,
                $subject,
                format_text($messagetext),
                $messagehtml   = '',
                $attachment    = '',
                $attachname    = '',
                true,
                $replyto       = '',
                $replytoname   = '',
                $wordwrapwidth = 79
            );
        }
    }//end foreach

}//end block_ckc_requests_manager_new_course_approved_mail_user()


 // function


/**
 *   When a new course is approved, email the admin(s)
 */
function block_ckc_requests_manager_new_course_approved_mail_admin($current_mod_info)
{
    global $GLOBALS['USER'], $GLOBALS['CFG'], $emailsender, $senderemailaddress, $GLOBALS['DB'];

    // Get each admin email
    $wherequery = "varname = 'admin_email'";
    $modrecords = $GLOBALS['DB']->get_recordset_select('block_ckc_requests_manager_config', $wherequery);

    $admin_email = $GLOBALS['DB']->get_field('block_ckc_requests_manager_config', 'value', ['varname' => 'approvedadminemail'], IGNORE_MULTIPLE);

    if (strlen(trim($admin_email)) > 0) {
        // are there characters in the field.
        $messagetext = block_ckc_requests_manager_convert_tags_to_values($admin_email, $current_mod_info);
        // Send an email to each admin
        foreach ($modrecords as $rec) {
            $to      = $rec->value;
            $from    = $emailsender->email;
            $subject = get_string('emailSubj_adminApproved', 'block_ckc_requests_manager');

            block_ckc_requests_manager_send_email_to_address($to, $subject, format_text($messagetext));
        }//end foreach
    }//end if

}//end block_ckc_requests_manager_new_course_approved_mail_admin()


/**
 *  Requesting a new module, email admin(s)
 */
function block_ckc_requests_manager_request_new_mod_email_admins($current_mod_info)
{
    global $GLOBALS['USER'], $GLOBALS['CFG'], $emailsender, $GLOBALS['DB'], $senderemailaddress;

    // Get each admin email
    $wherequery  = "varname = 'admin_email'";
    $modrecords  = $GLOBALS['DB']->get_records_select('block_ckc_requests_manager_config', $wherequery);
    $admin_email = $GLOBALS['DB']->get_record('block_ckc_requests_manager_config', ['varname' => 'requestnewmoduleadmin']);

    if (strlen(trim($admin_email->value)) > 0) {
        // are there characters in the field.
        $messagetext = block_ckc_requests_manager_convert_tags_to_values($admin_email->value, $current_mod_info);
        // Send an email to each admin
        foreach ($modrecords as $rec) {
            $to = $rec->value;
            // $from = $senderemailaddress;
            $subject = get_string('emailSubj_adminNewRequest', 'block_ckc_requests_manager');

            block_ckc_requests_manager_send_email_to_address($to, $subject, format_text($messagetext));
        }//end foreach
    }//end if

}//end block_ckc_requests_manager_request_new_mod_email_admins()


/**
 * Requesting a new module, email user
 */
function block_ckc_requests_manager_request_new_mod_email_user($uid, $current_mod_info)
{
    global $emailsender,$senderemailaddress, $GLOBALS['DB'];

    $emailinguserobject = $GLOBALS['DB']->get_record('user', ['id' => $uid]);
    $subject            = get_string('emailSubj_userNewRequest', 'block_ckc_requests_manager');
    $GLOBALS['USER']_email_message = $GLOBALS['DB']->get_record('block_ckc_requests_manager_config', ['varname' => 'requestnewmoduleuser']);

    if (strlen(trim($GLOBALS['USER']_email_message->value)) > 0) {
        // are there characters in the field.
        $messagetext       = block_ckc_requests_manager_convert_tags_to_values($GLOBALS['USER']_email_message->value, $current_mod_info);
        email_to_user(
            $emailinguserobject,
            $emailsender,
            $subject,
            format_text($messagetext),
            $messagehtml   = '',
            $attachment    = '',
            $attachname    = '',
            true,
            $replyto       = '',
            $replytoname   = '',
            $wordwrapwidth = 79
        );
    }//end if

}//end block_ckc_requests_manager_request_new_mod_email_user()


/**
 *  Send an email out to an address external to anything
 *  to do with Moodle.
 * */
function block_ckc_requests_manager_send_email_to_Address($to, $subject, $text)
{
    global $emailsender, $GLOBALS['CFG'], $GLOBALS['DB'], $senderemailaddress;

    $emailinguserobject                    = new stdClass();
    $emailinguserobject->id                = 1;
    $emailinguserobject->email             = $to;
    $emailinguserobject->maildisplay       = true;
    $emailinguserobject->username          = '';
    $emailinguserobject->mailformat        = 1;
    $emailinguserobject->firstnamephonetic = '';
    $emailinguserobject->lastnamephonetic  = '';
    $emailinguserobject->middlename        = '';
    $emailinguserobject->alternatename     = '';
    $emailinguserobject->firstname         = get_string('admin');
    $emailinguserobject->lastname          = '';

    email_to_user(
        $emailinguserobject,
        $senderemailaddress,
        $subject,
        $text,
        $messagehtml   = '',
        $attachment    = '',
        $attachname    = '',
        true,
        $replyto       = '',
        $replytoname   = '',
        $wordwrapwidth = 79
    );

}//end block_ckc_requests_manager_send_email_to_Address()


/**
 * Email a comment out to a user
 */
function block_ckc_requests_manager_email_comment_to_user($message, $uid, $mid, $current_mod_info)
{
    global $GLOBALS['USER'], $GLOBALS['CFG'], $emailsender, $GLOBALS['DB'];

    $emailinguserobject = $GLOBALS['DB']->get_record('user', ['id' => $uid]);
    $commentForUser     = $GLOBALS['DB']->get_field('block_ckc_requests_manager_config', 'value', ['varname' => 'commentemailuser'], IGNORE_MULTIPLE);

    if (strlen(trim($commentForUser)) > 0) {
        // are there characters in the field.
        $additionalSignature = block_ckc_requests_manager_convert_tags_to_values($commentForUser, $current_mod_info);
        $from                = $emailsender->email;
        $subject             = get_string('emailSubj_userNewComment', 'block_ckc_requests_manager');
        $messagetext         = get_string('emailSubj_Comment', 'block_ckc_requests_manager').":

$message

$additionalSignature
";
        email_to_user(
            $emailinguserobject,
            $from,
            $subject,
            format_text($messagetext),
            $messagehtml     = '',
            $attachment      = '',
            $attachname      = '',
            true,
            $replyto         = '',
            $replytoname     = '',
            $wordwrapwidth   = 79
        );
    }//end if

}//end block_ckc_requests_manager_email_comment_to_user()


/**
 * Email a comment to an admin
 */
function block_ckc_requests_manager_email_comment_to_admin($message, $mid, $current_mod_info)
{
    global $GLOBALS['USER'], $GLOBALS['CFG'], $emailsender, $GLOBALS['DB'];

    // Get each admin email
     $adminEmailAddresses = $GLOBALS['DB']->get_recordset_select('block_ckc_requests_manager_config', "varname = 'admin_email'");
    // Comment for admin
    $commentForAdmin = $GLOBALS['DB']->get_field('block_ckc_requests_manager_config', 'value', ['varname' => 'commentemailadmin'], IGNORE_MULTIPLE);

    if (strlen(trim($commentForAdmin)) > 0) {
        // Are there characters in the field?
        $additionalSignature = block_ckc_requests_manager_convert_tags_to_values($commentForAdmin, $current_mod_info);

        // Send an email to each admin
        foreach ($adminEmailAddresses as $rec) {
            $to          = $rec->value;
            $from        = $emailsender->email;
            $subject     = get_string('emailSubj_adminNewComment', 'block_ckc_requests_manager');
            $messagetext = get_string('emailSubj_Comment', 'block_ckc_requests_manager')."

$message

$additionalSignature
";
            // $headers = get_string('emailSubj_From','block_ckc_requests_manager') . $from;
            block_ckc_requests_manager_send_email_to_address($to, $subject, format_text($messagetext));
        }
    }

}//end block_ckc_requests_manager_email_comment_to_admin()


/**
 * When a module has been denied, send an email to the admin.
 */
function block_ckc_requests_manager_send_deny_email_admin($message, $mid, $current_mod_info)
{
    global $GLOBALS['USER'], $GLOBALS['CFG'], $emailsender, $GLOBALS['DB'];

    // Get each admin email
    $modrecords = $GLOBALS['DB']->get_records('block_ckc_requests_manager_config', ['varname' => 'admin_email']);

    $admin_email = $GLOBALS['DB']->get_record('block_ckc_requests_manager_config', ['varname' => 'modulerequestdeniedadmin']);
    if (strlen(trim($admin_email->value)) > 0) {
        // are there characters in the field.
        // Send an email to each admin
        foreach ($modrecords as $rec) {
            $to = $rec->value;

            $from    = $emailsender->email;
            $subject = get_string('emailSubj_adminDeny', 'block_ckc_requests_manager');

            $messagetext  = $message;
            $messagetext .= '';

            $messagetext .= block_ckc_requests_manager_convert_tags_to_values($admin_email->value, $current_mod_info);
            block_ckc_requests_manager_send_email_to_address($to, $subject, format_text($messagetext));
        }//end foreach
    }//end if

}//end block_ckc_requests_manager_send_deny_email_admin()


/**
 * Once a module has been denied, send an email to
 * the user.
 */
function block_ckc_requests_manager_send_deny_email_user($message, $GLOBALS['USER']id, $mid, $current_mod_info)
{
    global $GLOBALS['USER'], $GLOBALS['CFG'], $emailsender, $GLOBALS['DB'];

    $emailinguserobject    = $GLOBALS['DB']->get_record('user', ['id' => $GLOBALS['USER']id]);
    $from                  = $emailsender->email;
    $subject               = get_string('emailSubj_userDeny', 'block_ckc_requests_manager');
    $GLOBALS['USER']_email = $GLOBALS['DB']->get_record('block_ckc_requests_manager_config', ['varname' => 'modulerequestdenieduser']);

    if (strlen(trim($GLOBALS['USER']_email->value)) > 0) {
        // are there characters in the field.
        $messagetext       = $message;
        $messagetext      .= '';
        $messagetext      .= block_ckc_requests_manager_convert_tags_to_values($GLOBALS['USER']_email->value, $current_mod_info);
        email_to_user(
            $emailinguserobject,
            $from,
            $subject,
            format_text($messagetext),
            $messagehtml   = '',
            $attachment    = '',
            $attachname    = '',
            true,
            $replyto       = '',
            $replytoname   = '',
            $wordwrapwidth = 79
        );
    }

}//end block_ckc_requests_manager_send_deny_email_user()


/**
 * When a lecturer requests control of a module.
 */
function block_ckc_requests_manager_handover_email_lecturers($course_id, $currentUserId, $custommessage)
{
    global $GLOBALS['USER'], $GLOBALS['CFG'], $emailsender, $GLOBALS['DB'];
    $teacher_ids = '';

    // Send an email to the module owner
    // Get a list of all the lecturers
    if (! $course = $GLOBALS['DB']->get_record('course', ['id' => $course_id])) {
        error("That's an invalid course id");
    }

    // Get the teacher ids
    $teacher_ids = block_ckc_requests_manager_get_lecturer_ids_space_sep($course_id);

    // Collect info on the person who made the request
    $requester       = $GLOBALS['DB']->get_record('user', ['id' => $currentUserId]);
    $requester_email = $requester->email;

    $teacher_ids;
    $assignedlectureremails = '';

    // for each teacher id, email them
    $idarray = explode(' ', $teacher_ids);

    // ****** Email each of the people who are associated with the course ******
    $admin_email = $GLOBALS['DB']->get_record('block_ckc_requests_manager_config', ['varname' => 'handoveruser']);

    if (!empty((trim($admin_email->value))) {
        // Are there characters in the field?
        $custom_sig = $admin_email->value;
        foreach ($idarray as $single_id) {
            if (empty($single_id)) {
                continue;
            }

            $emailinguserobject      = $GLOBALS['DB']->get_record('user', ['id' => $single_id]);
            $assignedlectureremails .= ' '.$emailinguserobject->email;
            $from                    = $emailsender->email;
            $subject                 = get_string('emailSubj_teacherHandover', 'block_ckc_requests_manager');

            $messagetext = PHP_EOL.PHP_EOL.get_string('emailSubj_pleasecontact', 'block_ckc_requests_manager').": $requester_email

".$custommessage.'
'.$custom_sig;

            email_to_user(
                $emailinguserobject,
                $from,
                $subject,
                format_text($messagetext),
                $messagehtml    = '',
                $attachment     = '',
                $attachname     = '',
                $usetrueaddress = true,
                $replyto        = '',
                $replytoname    = '',
                $wordwrapwidth  = 79
            );
        }//end foreach
    }//end if

    // ***** Email the person who made the request
    $current_user_emailinguserobject = $GLOBALS['DB']->get_record('user', ['id' => $GLOBALS['USER']->id]);
    $admin_email = $GLOBALS['DB']->get_record('block_ckc_requests_manager_config', ['varname' => 'handovercurrent']);

    if (strlen(trim($admin_email->value)) > 0) {
        // are there characters in the field.
        $custom_sig  = $admin_email->value;
        $from        = $emailsender->email;
        $subject     = get_string('emailSubj_teacherHandover', 'block_ckc_requests_manager');
        $messagetext = '

'.get_string('emailSubj_mailSent1', 'block_ckc_requests_manager').':  '.$assignedlectureremails."

$custommessage

$custom_sig
";

        email_to_user(
            $current_user_emailinguserobject,
            $from,
            $subject,
            format_text($messagetext),
            $messagehtml    = '',
            $attachment     = '',
            $attachname     = '',
            $usetrueaddress = true,
            $replyto        = '',
            $replytoname    = '',
            $wordwrapwidth  = 79
        );
    }//end if

    // ******** Send an email to each admin *********
    $wherequery  = "varname = 'admin_email'";
     $modrecords = $GLOBALS['DB']->get_recordset_select('block_ckc_requests_manager_config', $wherequery);

    foreach ($modrecords as $rec) {
        $to      = $rec->value;
        $from    = $emailsender->email;
        $subject = get_string('emailSubj_teacherHandover', 'block_ckc_requests_manager');

        $admin_email = $GLOBALS['DB']->get_record('block_ckc_requests_manager_config', ['varname' => 'handoveradmin']);

        if (strlen(trim($admin_email->value)) > 0) {
            // are there characters in the field.
            $custom_sig   = $admin_email->value;
            $messagetext  = '';
            $messagetext .= '

';

                $messagetext          .= "
$custommessage

        ".get_string('emailSubj_teacherHandover', 'block_ckc_requests_manager').": $requester_email

$custom_sig
                                ";
            $headers                   = get_string('emailSubj_From', 'block_ckc_requests_manager').$from;
            $GLOBALS['USER']obj        = new stdClass();
            $GLOBALS['USER']obj->email = $to;

            block_ckc_requests_manager_send_email_to_address($to, $subject, format_text($messagetext));
        }//end if
    }//end foreach

}//end block_ckc_requests_manager_handover_email_lecturers()
