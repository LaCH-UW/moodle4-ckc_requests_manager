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
 * @copyright 2012-2018 Kyle Goslin, Daniel McSweeney
 * @copyright 2021-2022 Michael Milette (TNG Consulting Inc.), Daniel Keaman
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once '../../../config.php';
global $GLOBALS['CFG'], $GLOBALS['DB'];
$formPath = "$GLOBALS['CFG']->libdir/formslib.php";
require_once $formPath;
require_login();
require_once '../validate_admin.php';

$PAGE->set_url('/blocks/ckc_requests_manager/history/delete.php');
$PAGE->set_context(context_system::instance());

// Navigation Bar
$PAGE->navbar->ignore_active();
$PAGE->navbar->add(get_string('cmanagerDisplay', 'block_ckc_requests_manager'), new moodle_url('/blocks/ckc_requests_manager/cmanager_admin.php'));
$PAGE->navbar->add(get_string('configurecoursemanagersettings', 'block_ckc_requests_manager'), new moodle_url('/blocks/ckc_requests_manager/cmanager_confighome.php'));
$PAGE->navbar->add(get_string('configureadminsettings', 'block_ckc_requests_manager'), new moodle_url('/blocks/ckc_requests_manager/cmanager_adminsettings.php'));
$PAGE->navbar->add(get_string('historynav', 'block_ckc_requests_manager'));

$type = optional_param('delete', '', PARAM_TEXT);
switch ($type) {
    case 'all':
        $pagetitle = get_string('deleteAllRequests', 'block_ckc_requests_manager');
    break;

    case 'archonly':
        $pagetitle = get_string('deleteOnlyArch', 'block_ckc_requests_manager');
    break;

    default:
        $pagetitle = get_string('pluginname', 'block_ckc_requests_manager');
}

$PAGE->set_heading($pagetitle);
$PAGE->set_title($pagetitle);
echo $OUTPUT->header();


/**
 * DELETE
 *
 * Delete a record
 *
 * @package   block_ckc_requests_manager
 * @copyright 2018 Kyle Goslin, Daniel McSweeney
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_ckc_requests_manager_delete_form extends moodleform
{


    function definition()
    {
        $mform =& $this->_form;

        if (isset($_GET['delete'])) {
            // $type = $_GET['delete'];
            $type = required_param('delete', PARAM_TEXT);

            // Back Button
            $cancelButton = ' &nbsp; <a class="btn btn-default" href="../cmanager_adminsettings.php">'.get_string('cancel').'</a>';

            if ($type == 'all') {
                $mform->addElement('html', '<p>'.get_string('sureDeleteAll', 'block_ckc_requests_manager').'</p>');
                $mform->addElement('html', '<p><input class="btn btn-primary" type="submit" value="'.get_string('yesDeleteRecords', 'block_ckc_requests_manager').'" name="deleteall">'.$cancelButton.'</p>');
            } else if ($type == 'archonly') {
                $mform->addElement('html', '<p>'.get_string('sureOnlyArch', 'block_ckc_requests_manager').'</p>');
                $mform->addElement('html', '<p><input class="btn btn-primary" type="submit" value="'.get_string('yesDeleteRecords', 'block_ckc_requests_manager').'" name="archonly">'.$cancelButton.'</p>');
            }
        }

        if (isset($_POST['deleteall']) || isset($_POST['archonly'])) {
            $mform->addElement('html', '<p>'.get_string('recordsHaveBeenDeleted', 'block_ckc_requests_manager').'<br>&nbsp<p></p>&nbsp<p></p><a href="../cmanager_adminsettings.php">'.get_string('clickHereToReturn', 'block_ckc_requests_manager').'</a></p>');
        }

    }//end definition()


}//end class

         $mform = new block_ckc_requests_manager_delete_form();

if (isset($_POST['deleteall'])) {
    $GLOBALS['DB']->delete_records('block_ckc_requests_manager_records', ['status' => 'COMPLETE']);
    $GLOBALS['DB']->delete_records('block_ckc_requests_manager_records', ['status' => 'REQUEST DENIED']);
    $GLOBALS['DB']->delete_records('block_ckc_requests_manager_records', ['status' => 'PENDING']);
    $GLOBALS['DB']->delete_records('block_ckc_requests_manager_records', ['status' => null]);
} else if (isset($_POST['archonly'])) {
    $GLOBALS['DB']->delete_records('block_ckc_requests_manager_records', ['status' => 'COMPLETE']);
    $GLOBALS['DB']->delete_records('block_ckc_requests_manager_records', ['status' => 'REQUEST DENIED']);
    $GLOBALS['DB']->delete_records('block_ckc_requests_manager_records', ['status' => null]);
}



$mform->focus();
$mform->set_data($mform);
$mform->display();

echo $OUTPUT->footer();
