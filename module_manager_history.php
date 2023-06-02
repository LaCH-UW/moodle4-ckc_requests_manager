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
$PAGE->navbar->add(get_string('myarchivedrequests', 'block_ckc_requests_manager'));
$PAGE->set_url('/blocks/ckc_requests_manager/module_manager.php');
$PAGE->set_context(context_system::instance());
$PAGE->set_heading(get_string('myarchivedrequests', 'block_ckc_requests_manager'));
$PAGE->set_title(get_string('myarchivedrequests', 'block_ckc_requests_manager'));

echo $OUTPUT->header();

$context = context_system::instance();

if (false === has_capability('block/cmanager:viewrecord', $context)) {
       print_error(get_string('cannotviewrecords', 'block_ckc_requests_manager'));
}
?>

<link rel="stylesheet" type="text/css" href="css/main.css" />
<script src="js/jquery/jquery-3.3.1.min.js"></script>
<script type="text/javascript">
    function cancelConfirm(id,langString) {
        //var answer = confirm("Are you sure you want to cancel this request?")
        var answer = confirm(langString)
        if (answer){

            window.location = "deleteRequest.php?id=" + id;
        }
        else{

        }
    }
</script>

<style>
    tr:nth-child(odd)        { background-color:#eee; }
    tr:nth-child(even)        { background-color:#fff; }
 </style>

<?php
/**
 * History manager
 * The management front end for the modules which have been processed
 * in the past.
 *
 * @category  Block
 * @package   RequestsManager
 * @author    Marcin Zbiegień <m.zbiegien@uw.edu.pl>
 * @copyright 2023 UW
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @link      https://uw.edu.pl
 */
class block_ckc_requests_manager_module_manager_history_form extends moodleform
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
        $this->_form->addElement(
            'html',
            '<p>'.get_string('cmanagerWelcome', 'block_ckc_requests_manager').'</p>'
        );
        $this->_form->addElement(
            'html',
            '<p><input class="btn btn-default" type="button" value="'.get_string('cmanagerRequestBtn', 'block_ckc_requests_manager').'" onclick="window.location.href=\'course_request.php?mode=1\'"></p>'
        );

        $uid = $GLOBALS['USER']->id;

        $selectQuery = "createdbyid = $uid AND status = 'COMPLETE' OR createdbyid = $uid AND status = 'REQUEST DENIED' ORDER BY id DESC";
        // $GLOBALS['DB']->sql_order_by_text('id', $numchars=32);
        $pendingList = $GLOBALS['DB']->get_recordset_select('block_ckc_requests_manager_records', $select = $selectQuery);
        // @QUESTION WTF is this for!?
        // $page1_fieldname1 = $GLOBALS['DB']->get_field_select('block_ckc_requests_manager_config', 'value', "varname = 'page1_fieldname1'");
        // $page1_fieldname2 = $GLOBALS['DB']->get_field_select('block_ckc_requests_manager_config', 'value', "varname = 'page1_fieldname2'");
        // $page1_fieldname4 = $GLOBALS['DB']->get_field_select('block_ckc_requests_manager_config', 'value', "varname = 'page1_fieldname4'");
        $outputHTML = '';
        $modsHTML   = block_ckc_requests_manager_display_admin_list(
            $pendingList,
            true,
            false,
            false,
            'user_history'
        );

        $outputHTML .= '<div id="existingrequest" style="border-bottom:1px solid black; height:300px; background:transparent"></div>';
        // @QUESTION WHY?? this overrides everything from previous line!
        $outputHTML = $modsHTML;
        $this->_form->addElement('html', $outputHTML);

    }//end definition()


}//end class

$mForm = new block_ckc_requests_manager_module_manager_history_form();

if ($mForm->is_cancelled() === true) {
    echo "<script>window.location='module_manager.php';</script>";
    die;
} else if (empty($mForm->get_data()) === true) {
    $mForm->focus();
    $mForm->display();
}

echo $OUTPUT->footer();
