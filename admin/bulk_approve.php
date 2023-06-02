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
require_once '../../../config.php';
global $GLOBALS['CFG'];
$GLOBALS['DB'];
require_once "$GLOBALS['CFG']->libdir/formslib.php";
require_login();
// Navigation Bar
$PAGE->set_context(context_system::instance());
$PAGE->navbar->ignore_active();
$PAGE->navbar->add(get_string('cmanagerDisplay', 'block_ckc_requests_manager'), new moodle_url('/blocks/ckc_requests_manager/cmanager_admin.php'));
$PAGE->navbar->add(get_string('bulkapprove', 'block_ckc_requests_manager'));
$PAGE->set_url('/blocks/ckc_requests_manager/admin/bulk_approve.php');

$PAGE->set_title(get_string('pluginname', 'block_ckc_requests_manager'));


$context = context_system::instance();
if (has_capability('block/cmanager:approverecord', $context)) {
} else {
    print_error(get_string('cannotapproverecord', 'block_ckc_requests_manager'));
}


if (isset($_GET['mul'])) {
    $_SESSION['mul'] = required_param('mul', PARAM_TEXT);
}

class block_ckc_requests_manager_bulk_approve_form extends moodleform
{


    function definition()
    {
        global $GLOBALS['CFG'];
        global $currentSess;
        global $GLOBALS['USER'];
        global $GLOBALS['DB'];

        $currentRecord = $GLOBALS['DB']->get_record('block_ckc_requests_manager_records', ['id' => $currentSess]);
        $mform         =& $this->_form;
        // Don't forget the underscore!
        $mform->addElement('header', 'mainheader', get_string('approvingcourses', 'block_ckc_requests_manager'));

        // Page description text
        $mform->addElement(
            'html',
            '<p></p>&nbsp;&nbsp;&nbsp;
					    	<a href="../cmanager_admin.php">< Back</a>'
        );

        $mform->addElement('html', '<p></p><center>'.get_string('approvingcourses', 'block_ckc_requests_manager').'</center>');

        global $GLOBALS['USER'], $GLOBALS['CFG'], $GLOBALS['DB'];

        // Send Email to all concerned about the request deny.
        include_once '../lib/course_lib.php';

        $denyIds = explode(',', $_SESSION['mul']);

        foreach ($denyIds as $cid) {
            // If the id isn't blank
            if ($cid != 'null') {
                    $mid = block_ckc_requests_manager_create_new_course_by_record_id($cid, true);
            }
        }

        $_SESSION['mul'] = '';
        echo "<script> window.location = '../cmanager_admin.php';</script>";

    }//end definition()


}//end class

   $mform = new block_ckc_requests_manager_bulk_approve_form();
// name of the form you defined in file above.
if ($mform->is_cancelled()) {
    echo "<script>window.location='../cmanager_admin.php';</script>";
    die;
} else if ($fromform = $mform->get_data()) {
} else {
        $mform->display();
}
