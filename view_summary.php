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
$PAGE->navbar->add(get_string('viewsummary', 'block_ckc_requests_manager'));

$PAGE->set_url('/blocks/ckc_requests_manager/view_summary.php');
$PAGE->set_context(context_system::instance());
$PAGE->set_heading(get_string('viewsummary', 'block_ckc_requests_manager'));
$PAGE->set_title(get_string('viewsummary', 'block_ckc_requests_manager'));

echo $OUTPUT->header();


$mid = $_SESSION['mid'];

if (isset($_GET['id']) === true) {
    $mid             = required_param('id', PARAM_INT);
    $_SESSION['mid'] = $mid;
}

/**
 * Course request form
 *
 * @category  Block
 * @package   RequestsManager
 * @author    Marcin Zbiegień <m.zbiegien@uw.edu.pl>
 * @copyright 2023 UW
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @link      https://uw.edu.pl
 */
class block_ckc_requests_manager_view_summary_form extends moodleform
{


    /**
     * Form content definition.
     *
     * @return void
     *
     * @throws coding_exception On errors.
     * @throws dml_exception On errors.
     */
    public function definition()
    {
        global $mid;

        $rec = $GLOBALS['DB']->get_record(
            'block_ckc_requests_manager_records',
            ['id' => $mid]
        );
        // Don't forget the underscore!
        // Page description text.
        $this->_form->addElement(
            'html',
            '<p><a href="module_manager.php" class="btn btn-default"><img src="icons/back.png" alt=""> '.get_string(
                'back',
                'block_ckc_requests_manager'
            ).'</a></p>'
        );

        $rec            = $GLOBALS['DB']->get_recordset_select(
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

        $this->_form->addElement('html', ''.$displayModHTML.'');
        $this->_form->addElement('html', '<p></p>&nbsp;');
        $whereQuery = "instanceid = '".$mid."' ORDER BY id DESC";
        $modRecords = $GLOBALS['DB']->get_recordset_select(
            'block_ckc_requests_manager_comments',
            $whereQuery
        );
        $htmlOutput = '';

        foreach ($modRecords as $record) {
            $username = $GLOBALS['DB']->get_field(
                'user',
                'username',
                ['id' => $record->createdbyid]
            );

            $htmlOutput .= '	<tr>';
            $htmlOutput .= ' <td>'.$record->dt.'</td>';
            $htmlOutput .= ' <td>'.$record->message.'</td>';
            $htmlOutput .= ' <td>'.$username.'</td>';
            $htmlOutput .= ' </tr>';
        }

        $this->_form->addElement('html', '<h2>'.get_string('comments').'</h2>');
        $this->_form->addElement(
            'html',
            '
            <table class="table-striped w-75">
                <tr>
                    <th>'.get_string('comments_date', 'block_ckc_requests_manager').'</td>
                    <th>'.get_string('comments_message', 'block_ckc_requests_manager').'</td>
                    <th>'.get_string('comments_from', 'block_ckc_requests_manager').'</td>
                </tr>
                '.$htmlOutput.'
            </table>
        '
        );

    }//end definition()


}//end class

$this->_form = new block_ckc_requests_manager_view_summary_form();

if (false === $this->_form->is_cancelled()
    && empty($this->_form->get_data()) === true
) {
        $this->_form->focus();
        $this->_form->set_data($this->_form);
        $this->_form->display();
         echo $OUTPUT->footer();
}
