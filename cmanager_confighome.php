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
require_once '../../config.php';
require_once "$GLOBALS['CFG']->libdir/formslib.php";

require_login();

// Navigation Bar
$PAGE->navbar->ignore_active();
$PAGE->navbar->add(get_string('cmanagerDisplay', 'block_ckc_requests_manager'), new moodle_url('/blocks/ckc_requests_manager/cmanager_admin.php'));
$PAGE->navbar->add(get_string('configurecoursemanagersettings', 'block_ckc_requests_manager'));

$PAGE->set_url('/blocks/ckc_requests_manager/block_ckc_requests_manager_confighome.php');
$PAGE->set_context(context_system::instance());
$PAGE->set_heading(get_string('configurecoursemanagersettings', 'block_ckc_requests_manager'));
$PAGE->set_title(get_string('configurecoursemanagersettings', 'block_ckc_requests_manager'));
echo $OUTPUT->header();


$context = context_system::instance();
if (has_capability('block/cmanager:viewconfig', $context)) {
} else {
    print_error(get_string('cannotviewconfig', 'block_ckc_requests_manager'));
}



/**
 * Config home
 *
 * Listing of config options
 *
 * @package   block_ckc_requests_manager
 * @copyright 2018 Kyle Goslin, Daniel McSweeney
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_ckc_requests_manager_confighome_form extends moodleform
{


    function definition()
    {
        $mform =& $this->_form;
        // Don't forget the underscore!
        $mainSlider = '
		<table style="width:100%; ">
		<tr>
		<td style="padding:25px; width:30px"><img src="icons/config/admin.png"></td>
		<td><b><a href="cmanager_adminsettings.php">'.get_string('configureadminsettings', 'block_ckc_requests_manager').'</a></b><br>'.get_string('configureadminsettings_desc', 'block_ckc_requests_manager').'</td>
	    </tr>

	    <tr>
		<td style="padding:25px; width:30px"><img src="icons/config/email.png"></td>

		<td><b><a href="cmanager_config.php">'.get_string('configureemailsettings', 'block_ckc_requests_manager').'</a></b><br>'.get_string('configureemailsettings_desc', 'block_ckc_requests_manager').'</td>

	    </tr>

    	<tr>

		<td style="padding:25px; width:30px"><img src="icons/config/config1.png"> </td>
		<td><b><a href="formeditor/page1.php">'.get_string('configurecourseformfields', 'block_ckc_requests_manager').'</a></b><br>'.get_string('configure_instruction2', 'block_ckc_requests_manager').'</td>
	    </tr>

	    <tr>
		<td style="padding:25px; width:30px"><img src="icons/config/config2.png"></td>
		<td><b><a href="formeditor/form_builder.php">'.get_string('informationform', 'block_ckc_requests_manager').'</a></b><br>'.get_string('configure_instruction3', 'block_ckc_requests_manager').'
		</td>

	    </tr>

		</table>';

        $mform->addElement('html', $mainSlider);

    }//end definition()


    // Close the function
}//end class

  // Close the class
$mform = new block_ckc_requests_manager_confighome_form();

if ($mform->is_cancelled()) {
} else if ($fromform = $mform->get_data()) {
} else {
    $mform->focus();
    $mform->display();
    echo $OUTPUT->footer();
}
