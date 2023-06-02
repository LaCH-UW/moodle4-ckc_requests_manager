<?php

/**
 * COURSE REQUEST MANAGER
 *
 * @category  Block
 * @package   RequestsManager
 * @author    Marcin Zbiegień <m.zbiegien@uw.edu.pl>
 * @copyright 2023 UW
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @link      https://uw.edu.pl
 */

require_once '../../config.php';

require_once $GLOBALS['CFG']->libdir.'/formslib.php';

require_login();

require_once 'lib/displayLists.php';

// Navigation Bar.
$PAGE->navbar->ignore_active();
$moduleMgrUrl = new moodle_url('/blocks/ckc_requests_manager/module_manager.php');
$PAGE->navbar->add(
    get_string('cmanagerDisplay', 'block_ckc_requests_manager'),
    $moduleMgrUrl
);
$PAGE->navbar->add(get_string('modrequestfacility', 'block_ckc_requests_manager'));
$mid = optional_param('id', '', PARAM_INT);
$PAGE->set_url('/blocks/ckc_requests_manager/review_request.php', ['id' => $mid]);
$PAGE->set_context(context_system::instance());
$PAGE->set_heading(get_string('requestReview_Summary', 'block_ckc_requests_manager'));
$PAGE->set_title(get_string('requestReview_Summary', 'block_ckc_requests_manager'));

echo $OUTPUT->header();

if (false === empty($mid)) {
    $_SESSION['mid'] = $mid;
} else {
    $mid = $_SESSION['mid'];
}


$context = context_system::instance();

if (false === has_capability('block/cmanager:addrecord', $context)) {
    print_error(get_string('cannotrequestcourse', 'block_ckc_requests_manager'));
}

/**
 * Review request
 *
 * Allow the user to review their request
 *
 * @category  Block
 * @package   RequestsManager
 * @author    Marcin Zbiegień <m.zbiegien@uw.edu.pl>
 * @copyright 2023 UW
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @link      https://uw.edu.pl
 */
class block_ckc_requests_manager_review_request_form extends moodleform
{


    /**
     * Form content definition.
     *
     * @return void
     *
     * @throws coding_exception On errors.
     * @throws dml_exception On errors.
     */
    function definition()
    {
        global $mid;

        // Don't forget the underscore!
        $this->_form->addElement(
            'html',
            '<p>'.get_string('requestReview_intro1', 'block_ckc_requests_manager').'</p>'
        );
        $this->_form->addElement(
            'html',
            '<p>'.get_string('requestReview_intro2', 'block_ckc_requests_manager').'</p>'
        );

        $rec = $GLOBALS['DB']->get_recordset_select(
            'block_ckc_requests_manager_records',
            'id = '.$mid
        );

        $displayModHTML = block_ckc_requests_manager_display_admin_list(
            $rec,
            false,
            false,
            false,
            ''
        );
        $this->_form->addElement('html', $displayModHTML);

        $buttonsArray   = [];
        $buttonsArray[] = &$this->_form->createElement(
            'submit',
            'submitbutton',
            get_string('requestReview_SubmitRequest', 'block_ckc_requests_manager')
        );
        $buttonsArray[] = &$this->_form->createElement(
            'submit',
            'alter',
            get_string('requestReview_AlterRequest', 'block_ckc_requests_manager')
        );
        $buttonsArray[] = &$this->_form->createElement(
            'cancel',
            'cancel',
            get_string('requestReview_CancelRequest', 'block_ckc_requests_manager')
        );
        $this->_form->addGroup($buttonsArray, 'buttonar', '', [' '], false);

    }//end definition()


}//end class

$this->_form = new block_ckc_requests_manager_review_request_form();
$fromData    = $this->_form->get_data();

if ($this->_form->is_cancelled() === true) {
    // Delete the record.
    $GLOBALS['DB']->delete_records_select(
        'block_ckc_requests_manager_records',
        'id = :mid',
        ['mid' => $mid]
    );
    echo "<script>window.location='module_manager.php'</script>";
    die;
} else if (false === empty($fromData)) {
    // If alter was pressed.
    if (isset($_POST['alter']) === true) {
        echo "<script>window.location='course_request.php?mode=2&edit=".$mid."'</script>";
        die;
    }

    include_once 'cmanager_email.php';

    global $mid;

    $rec = $GLOBALS['DB']->get_record(
        'block_ckc_requests_manager_records',
        ['id' => $mid]
    );

    $replaceValues                  = [];
    $replaceValues['[course_code']  = $rec->modcode;
    $replaceValues['[course_name]'] = $rec->modname;
    // $replaceValues['[p_code]'] = $rec->progcode;
    // $replaceValues['[p_name]'] = $rec->progname;
    $replaceValues['[e_key]']     = 'No key set';
    $replaceValues['[full_link]'] = get_string(
        'requestReview_ccdne',
        'block_ckc_requests_manager'
    );
    $replaceValues['[loc]']       = get_string(
        'reviewLocation',
        'block_ckc_requests_manager'
    ).': ';
    $replaceValues['[req_link]']  = $GLOBALS['CFG']->wwwroot.'/blocks/ckc_requests_manager/view_summary.php?id='.$mid;

    // Send email to admin saying we are requesting a new mod.
    block_ckc_requests_manager_request_new_mod_email_admins($replaceValues);

    // Send email to user to track that we are requesting a new mod.
    block_ckc_requests_manager_request_new_mod_email_user(
        $GLOBALS['USER']->id,
        $replaceValues
    );


    // Return to module manager.
    if (isset($_SESSION['CRMisAdmin']) === true) {
        if (boolval($_SESSION['CRMisAdmin']) === true) {
            echo "<script>window.location='cmanager_admin.php'</script>";
            die;
        }
    } else {
        echo "<script>window.location='module_manager.php'</script>";
        die;
    }
} else {
      $this->_form->focus();
      $this->_form->set_data($this->_form);
      $this->_form->display();
      echo $OUTPUT->footer();
}//end if


/**
 * Get username by id.
 *
 * @param integer $id User id.
 *
 * @return string
 */
function block_ckc_requests_manager_get_username($id)
{
    return $GLOBALS['DB']->get_field_select(
        'user',
        'username',
        'id = :uid',
        ['uid' => $id]
    );

}//end block_ckc_requests_manager_get_username()
