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
global $GLOBALS['CFG'], $GLOBALS['DB'];

require_login();
require_once '../validate_admin.php';
require_once '../lib/boot.php';

// Navigation Bar
$PAGE->navbar->ignore_active();
$PAGE->navbar->add(get_string('cmanagerDisplay', 'block_ckc_requests_manager'), new moodle_url('/blocks/ckc_requests_manager/cmanager_admin.php'));
$PAGE->navbar->add(get_string('configurecoursemanagersettings', 'block_ckc_requests_manager'), new moodle_url('/blocks/ckc_requests_manager/cmanager_confighome.php'));
$PAGE->navbar->add(get_string('formpage2builder', 'block_ckc_requests_manager'));
$PAGE->set_url('/blocks/ckc_requests_manager/formeditor/form_builder.php');
$PAGE->set_context(context_system::instance());
$PAGE->set_heading(get_string('informationform', 'block_ckc_requests_manager'));
$formPath = "$GLOBALS['CFG']->libdir/formslib.php";
require_once $formPath;
$PAGE->set_title(get_string('informationform', 'block_ckc_requests_manager'));
echo $OUTPUT->header();


$context = context_system::instance();
if (has_capability('block/cmanager:viewconfig', $context)) {
} else {
    print_error(get_string('cannotviewrecords', 'block_ckc_requests_manager'));
}
?>

<script>
    // From the dropdown menu of different forms that are avilable
    // save the one the user has just selected.
    function saveSelectedForm(){
        window.onbeforeunload = null;
        var value = document.getElementById('selectform').value;


        $.ajaxSetup({async:false});
        $.post("ajax_functions.php", { type: 'saveselectedform', value: value},
                function(data) {
                    window.location = 'form_builder.php';
                // alert(data);
            });
    }

    // Delete a selected from from the list of available forms.
    var formId = 0;
    function deleteSelectedForm(confirmMsg,form){
        formId = form;
        $("#delete_modal").modal();

    }

    // After a user has entered the name for a new form page
    // this function is called when the submit button is clicked.
    function addNewField(){
        window.onbeforeunload = null;
        var value = document.getElementById('newformname').value;
        if(value != ''){
            //$.ajaxSetup({async:false});
            $.post("ajax_functions.php", { type: 'addnewform', value: value},
                function(data) {
                    window.location = 'form_builder.php';
                });
        }
    }
</script>

<?php
if (isset($_GET['del'])) {
    $delId = required_param('del', PARAM_INT);
    $GLOBALS['DB']->delete_records_select('block_ckc_requests_manager_config', "id = $delId");
    echo " <script>window.location = 'form_builder.php';</script> ";
}

class block_ckc_requests_manager_builder_form extends moodleform
{


    function definition()
    {
        global $GLOBALS['CFG'], $GLOBALS['USER'], $GLOBALS['DB'];
        $mform =& $this->_form;
        // Don't forget the underscore!
        $mform->addElement('html', '<p><a href="../cmanager_confighome.php" class="btn btn-default"><img src="../icons/back.png" alt=""> '.get_string('back', 'block_ckc_requests_manager').'</a></p>');

        // Page description text
        $mform->addElement('html', get_string('formBuilder_instructions', 'block_ckc_requests_manager').'<ul><li>'.get_string('formBuilder_instructions1', 'block_ckc_requests_manager').'</li><li>'.get_string('formBuilder_instructions2', 'block_ckc_requests_manager').'</li><li>'.get_string('formBuilder_instructions3', 'block_ckc_requests_manager').'</li><li>'.get_string('formBuilder_instructions4', 'block_ckc_requests_manager').'</li><li>'.get_string('formBuilder_instructions5', 'block_ckc_requests_manager').'</li><li>'.get_string('formBuilder_instructions6', 'block_ckc_requests_manager').'</li></ul>');

        $mform->addElement('html', '<h2 class="mt-3">'.get_string('formBuilder_currentActiveForm', 'block_ckc_requests_manager').'</h2>');
        $mform->addElement('html', '<div>'.get_string('formBuilder_currentActiveFormInstructions', 'block_ckc_requests_manager').'</div>');

        $currentSelectedForm = $GLOBALS['DB']->get_field_select('block_ckc_requests_manager_config', 'value', "varname = 'current_active_form_id'");

        $whereQuery = "varname = 'page2form'";
        $formrows   = $GLOBALS['DB']->get_recordset_select('block_ckc_requests_manager_config', $whereQuery);

        $selectHTML = get_string('formBuilder_selectDescription', 'block_ckc_requests_manager').' <select onchange="saveSelectedForm()" id="selectform">';
        foreach ($formrows as $row) {
            $selected = '';
            if ($currentSelectedForm == $row->id) {
                $selected = 'selected';
            }

            $selectHTML .= '    <option '.$selected.' value="'.$row->id.'">'.$row->value.'</option>';
            $selected    = '';
        }

        $selectHTML .= '</select>';
        $mform->addElement('html', $selectHTML);

        $whereQuery  = "varname = 'page2form'";
        $formRecords = $GLOBALS['DB']->get_recordset_select('block_ckc_requests_manager_config', $whereQuery);

        // Modal for deleting requests.
        $pop = generateGenericConfirm(
            'delete_modal',
            get_string('alert', 'block_ckc_requests_manager'),
            get_string('formBuilder_confirmDelete', 'block_ckc_requests_manager'),
            get_string('formBuilder_deleteForm', 'block_ckc_requests_manager')
        );
        // Button click handler.
        $js = '<script>
            // Delete request ok  button click handler.
            $("#okdelete_modal").click(function(){
                window.location = window.location = "form_builder.php?del="+formId;
            });
        </script>';

        $formsItemsHTML = $pop.$js;
        foreach ($formRecords as $rec) {
            $formsItemsHTML .= '<div class="row">';
            $formsItemsHTML .= '<div class="col-6 col-sm-5 col-md-4 col-xl-3">'.$rec->value.'</div>';
            $formsItemsHTML .= '<div class="col-6 col-sm-4 col-md-3 col-xl-2">';
            $formsItemsHTML .= '<a title="'.get_string('formBuilder_editForm', 'block_ckc_requests_manager').'" aria-label="'.get_string('formBuilder_editForm', 'block_ckc_requests_manager').'" href="page2.php?id='.$rec->id.'&name='.urlencode($rec->value).'"><i class="icon fa fa-cog fa-fw" aria-hidden="true"></i></a>';
            $formsItemsHTML .= ' <a title="'.get_string('formBuilder_previewForm', 'block_ckc_requests_manager').'" aria-label="'.get_string('formBuilder_previewForm', 'block_ckc_requests_manager').'" href="preview.php?id='.$rec->id.'"><i class="icon fa fa-search-plus fa-fw" aria-hidden="true"></i></a>';
            // Do not offer option to delete active form.
            if ($currentSelectedForm != $rec->id) {
                $formsItemsHTML .= ' <a title="'.get_string('formBuilder_deleteForm', 'block_ckc_requests_manager').'" aria-label="'.get_string('formBuilder_deleteForm', 'block_ckc_requests_manager').'" href="#" onclick="javascript:deleteSelectedForm(\''.get_string('formBuilder_confirmDelete', 'block_ckc_requests_manager').'\','.$rec->id.');"><i class="icon fa fa-trash fa-fw" aria-hidden="true"></i></a>';
            }

            $formsItemsHTML .= '</div>';
            $formsItemsHTML .= '</div>';
        }

        $mform->addElement('html', '<h2 class="mt-3">'.get_string('formBuilder_manageFormsText', 'block_ckc_requests_manager').'</h2>');

        $mform->addElement(
            'html',
            '<p>'.get_string('formBuilder_selectAny', 'block_ckc_requests_manager').'</p>'.$formsItemsHTML.'
            <input type="text" id = "newformname" size="20"> <input class="btn btn-default" type="button" value = "'.get_string('formBuilder_createNewText', 'block_ckc_requests_manager').'" onclick="addNewField()">'
        );

    }//end definition()


}//end class

$mform = new block_ckc_requests_manager_builder_form();
// name of the form you defined in file above.
if ($mform->is_cancelled()) {
} else if ($fromform = $mform->get_data()) {
} else {
}

$mform->focus();
$mform->display();
echo $OUTPUT->footer();
