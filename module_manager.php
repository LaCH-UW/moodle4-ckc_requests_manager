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
require_once 'lib/boot.php';

// Navigation Bar
$PAGE->navbar->ignore_active();
$moduleMgrUrl = new moodle_url('/blocks/ckc_requests_manager/module_manager.php');
$PAGE->navbar->add(
    get_string('cmanagerDisplay', 'block_ckc_requests_manager'),
    $moduleMgrUrl
);
$PAGE->set_url('/blocks/ckc_requests_manager/module_manager.php');
$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_heading(get_string('pluginname', 'block_ckc_requests_manager'));
$PAGE->set_title(get_string('pluginname', 'block_ckc_requests_manager'));

if (false === has_capability('block/cmanager:viewrecord', $context)) {
    print_error(get_string('cannotviewrecords', 'block_ckc_requests_manager'));
}

echo $OUTPUT->header();

$context = context_system::instance();

if (false === has_capability('block/cmanager:viewrecord', $context)) {
    print_error(get_string('cannotviewrecords', 'block_ckc_requests_manager'));
}
?>

<script type="text/javascript">
var id = 0;
// pop up a modal to ask the user are
// they sure that they want to cancel the
// request
function cancelConfirm(cid,langString) {
    $("#conf1").modal();
    id = cid;

}
</script>

<?php
/**
 * Module manager
 *
 * Main module manager form
 * Course request manager block for moodle main block interface
 *
 * @category  Block
 * @package   RequestsManager
 * @author    Marcin Zbiegień <m.zbiegien@uw.edu.pl>
 * @copyright 2023 UW
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @link      https://uw.edu.pl
 */
class block_ckc_requests_manager_module_manager_form extends moodleform
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
        // Don't forget the underscore!
        $this->_form->addElement(
            'html',
            '<p>'.get_string('cmanagerWelcome', 'block_ckc_requests_manager').'</p>'
        );
        $this->_form->addElement(
            'html',
            '<p><a class="btn btn-default" href="course_request.php?mode=1">'.get_string('cmanagerRequestBtn', 'block_ckc_requests_manager').'</a></p>'
        );

        $outputHtml = '<div id="pendingrequestcontainer">';
        // Get the list of pending requests.
        $pendingList = $GLOBALS['DB']->get_records(
            'block_ckc_requests_manager_records',
            [
                'createdbyid' => intval($GLOBALS['USER']->id),
                'status'      => 'PENDING',
            ],
            'id ASC'
        );

        $outputHtml .= block_ckc_requests_manager_display_admin_list(
            $pendingList,
            true,
            false,
            false,
            'user_manager'
        );
        $outputHtml .= generateGenericConfirm(
            'conf1',
            get_string('alert', 'block_ckc_requests_manager'),
            get_string('cmanagerConfirmCancel', 'block_ckc_requests_manager'),
            get_string('yes', 'block_ckc_requests_manager')
        );

        $outputHtml .= '
        <script>
        // cancel request click handler
        // just does a hard redirect to the delete page and back.
        $("#okconf1").click(function(){

              console.log("deleting request");
              window.location = "deleterequest.php?id=" + id;


            });

        </script>
        ';
        // Existing Requests.
        $this->_form->addElement('html', '<div style="">	'.$outputHtml.'</div>');

    }//end definition()


}//end class

$this->_form = new block_ckc_requests_manager_module_manager_form();

if ($this->_form->is_cancelled() === true) {
    echo "<script>window.location='module_manager.php';</script>";
    die;
}

$this->_form->display();
$this->_form->focus();
$this->_form->focus();
echo $OUTPUT->footer();
?>
<script src="js/bootstrap.min.js"/>
