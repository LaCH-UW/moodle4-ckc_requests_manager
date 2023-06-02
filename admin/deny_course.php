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

// Navigation Bar
$PAGE->navbar->ignore_active();
$PAGE->navbar->add(get_string('cmanagerDisplay', 'block_ckc_requests_manager'), new moodle_url('/blocks/ckc_requests_manager/cmanager_admin.php'));
$PAGE->navbar->add(get_string('denycourse', 'block_ckc_requests_manager'));
$PAGE->set_url('/blocks/ckc_requests_manager/admin/deny_course.php');
$PAGE->set_context(context_system::instance());
$PAGE->set_heading(get_string('pluginname', 'block_ckc_requests_manager'));
$PAGE->set_title(get_string('pluginname', 'block_ckc_requests_manager'));
echo $OUTPUT->header();


$context = context_system::instance();
if (has_capability('block/cmanager:denyrecord', $context)) {
} else {
    print_error(get_string('cannotdenyrecord', 'block_ckc_requests_manager'));
}


?>



<?php
if (isset($_GET['id'])) {
    $mid             = required_param('id', PARAM_INT);
    $_SESSION['mid'] = $mid;
} else {
    $mid = $_SESSION['mid'];
}

class block_ckc_requests_manager_deny_form extends moodleform
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
        $denytext1 = $GLOBALS['DB']->get_field_select('block_ckc_requests_manager_config', 'value', "varname = 'denytext1'");
        $denytext2 = $GLOBALS['DB']->get_field_select('block_ckc_requests_manager_config', 'value', "varname = 'denytext2'");
        $denytext3 = $GLOBALS['DB']->get_field_select('block_ckc_requests_manager_config', 'value', "varname = 'denytext3'");
        $denytext4 = $GLOBALS['DB']->get_field_select('block_ckc_requests_manager_config', 'value', "varname = 'denytext4'");
        $denytext5 = $GLOBALS['DB']->get_field_select('block_ckc_requests_manager_config', 'value', "varname = 'denytext5'");
        $mform->addElement('header', 'mainheader', '<span style="font-size:18px">'.get_string('denyrequest_Title', 'block_ckc_requests_manager').'</span>');

        // Page description text
        $mform->addElement(
            'html',
            '<p><a href="../cmanager_admin.php" class="btn btn-default"><img src="../icons/back.png" alt=""> '.get_string('back', 'block_ckc_requests_manager').'</a></p>

		<script>
		function addSelectedText(num){
			var value = document.getElementById(\'cusvalue\' + num).value;
			document.getElementById(\'newcomment\').value += value;

		}
		</script>

	<style>
			#wrapper {
		    width: 80%;
		    border: 1px solid black;
		    overflow: hidden; /* will contain if #first is longer than #second */
			}
			#left {
			    width: 50%;
			    float:left; /* add this */

			}
			#right {
			    border: 0px solid green;
                width: 50%;
			    overflow: hidden; /* if you dont want #second to wrap below #first */
			}

	 </style>
	 <center>

	 <div id="wrapper" style="padding:10px">


		<div id="left">
		<p></p><br>
		<form>
		<table>
			 <tr>


		 		<td><textarea id="cusvalue1" rows="5"cols="60">'.$denytext1.'</textarea></td>
				<td>


							<button type="button" onclick="addSelectedText(1); return false;"> >> </button>

		 		</td>
		 	</tr>
		 	<tr>
		 	<td>
		 			<textarea type="text" id="cusvalue2" rows="5"cols="60">'.$denytext2.'</textarea>
		 		</td>
		 		<td>
		 			<button type="button" onclick="addSelectedText(2); return false;"> >> </button>


		 		</td>


		 	</tr>
		 	<tr>

		 	<td>
				<textarea type="text" id="cusvalue3" rows="5"cols="60">'.$denytext3.'</textarea>
				</td>

		 		<td>
		 			<button type="button" onclick="addSelectedText(3); return false;"> >> </button>

		 		</td>



		 	</tr>
		 	<tr>

		 	<td>
				<textarea type="text" id="cusvalue4" rows="5"cols="60">'.$denytext4.'</textarea>
				</td>
		 		<td>
		 			<button type="button" onclick="addSelectedText(4); return false;"> >> </button>

		 		</td>

		 	</tr>
		 	<tr>

		 	<td>
					<textarea type="text" id="cusvalue5" rows="5"cols="60">'.$denytext5.'</textarea>
		 		</td>
		 		<td>
		 			<button type="button" onclick="addSelectedText(5); return false;"> >> </button>

		 		</td>


		 	</tr>
	 		</table>
	      </form>
		</div>
		<div id="right">
 			'.get_string('denyrequest_reason', 'block_ckc_requests_manager').'.
 			<p></p>
		<textarea id="newcomment" name="newcomment" rows="30" cols="52" maxlength="280"></textarea>
		<p></p>
	</div>
		<input class="btn btn-default" type="submit" value="'.get_string('denyrequest_Btn', 'block_ckc_requests_manager').'"/>




		</div>
		</center>
	'
        );

    }//end definition()


}//end class


/**
 * Get custom text
 */
function customText()
{
    global $GLOBALS['DB'];

    $optionHTML = 'hh';
    // Deny Text
    $denytext1 = $GLOBALS['DB']->get_field_select('block_ckc_requests_manager_config', 'value', "varname = 'denytext1'");
    if (!empty($denytext1)) {
        $optionHTML .= '<option value="'.$denytext1.'">'.$denytext1.'</option>';
    }

    $denytext2 = $GLOBALS['DB']->get_field_select('block_ckc_requests_manager_config', 'value', "varname = 'denytext2'");
    if (!empty($denytext2)) {
        $optionHTML .= '<option value="'.$denytext2.'">'.$denytext2.'</option>';
    }

    $denytext3 = $GLOBALS['DB']->get_field_select('block_ckc_requests_manager_config', 'value', "varname = 'denytext3'");

    if (!empty($denytext3)) {
        $optionHTML .= '<option value="'.$denytext3.'">'.$denytext3.'</option>';
    }

    $denytext4 = $GLOBALS['DB']->get_field_select('block_ckc_requests_manager_config', 'value', "varname = 'denytext4'");
    if (!empty($denytext4)) {
        $optionHTML .= '<option value="'.$denytext4.'">'.$denytext4.'</option>';
    }

    $denytext5 = $GLOBALS['DB']->get_field_select('block_ckc_requests_manager_config', 'value', "varname = 'denytext5'");
    if (!empty($denytext5)) {
        $optionHTML .= '<option value="'.$denytext5.'">'.$denytext5.'</option>';
    }

    return $optionHTML;

}//end customText()


   $mform = new block_ckc_requests_manager_deny_form();
// name of the form you defined in file above.
if ($mform->is_cancelled()) {
    echo "<script>window.location='../cmanager_admin.php';</script>";
            die;
} else if ($fromform = $mform->get_data()) {
       global $GLOBALS['USER'];
} else {
        $mform->focus();
        $mform->set_data($mform);
        $mform->display();
          echo $OUTPUT->footer();
}


/**
 * Get a username for a given ID from Moodle
 */
function block_ckc_requests_manager_get_username($id)
{
    global $GLOBALS['DB'];
    return $GLOBALS['USER']name = get_field('user', 'username', ['id' => $id]);

}//end block_ckc_requests_manager_get_username()


if ($_POST) {
    global $GLOBALS['CFG'], $GLOBALS['DB'];

        // Send Email to all concerned about the request deny.
        include_once '../cmanager_email.php';


        $message = required_param('newcomment', PARAM_TEXT);



        // update the request record
        $newrec         = new stdClass();
        $newrec->id     = $mid;
        $newrec->status = 'REQUEST DENIED';
        $GLOBALS['DB']->update_record('block_ckc_requests_manager_records', $newrec);

        // Add a comment to the module
        $GLOBALS['USER']id   = $GLOBALS['USER']->id;
        $newrec              = new stdClass();
        $newrec->instanceid  = $mid;
        $newrec->createdbyid = $GLOBALS['USER']id;
        $newrec->message     = $message;
        $newrec->dt          = date('Y-m-d H:i:s');
        $GLOBALS['DB']->insert_record('block_ckc_requests_manager_comments', $newrec, false);



        $currentRecord = $GLOBALS['DB']->get_record('block_ckc_requests_manager_records', ['id' => $mid]);

        $requesterId = $currentRecord->createdbyid;
    // Store the ID of the person who made the request
        $replaceValues                  = [];
        $replaceValues['[course_code']  = $currentRecord->modcode;
        $replaceValues['[course_name]'] = $currentRecord->modname;
        // $replaceValues['[p_code]'] = $currentRecord->progcode;
        // $replaceValues['[p_name]'] = $currentRecord->progname;
        $replaceValues['[e_key]']     = '';
        $replaceValues['[full_link]'] = $GLOBALS['CFG']->wwwroot.'/blocks/ckc_requests_manager/comment.php?id='.$mid;
        $replaceValues['[loc]']       = '';
        $replaceValues['[req_link]']  = $GLOBALS['CFG']->wwwroot.'/blocks/ckc_requests_manager/view_summary.php?id='.$mid;


        block_ckc_requests_manager_send_deny_email_user($message, $requesterId, $mid, $replaceValues);

           block_ckc_requests_manager_send_deny_email_admin($message, $mid, $replaceValues);


        echo "<script> window.location = '../cmanager_admin.php';</script>";
}//end if


