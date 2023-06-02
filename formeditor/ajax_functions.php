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

$context = context_system::instance();
if (has_capability('block/cmanager:approverecord', $context)) {
} else {
    print_error(get_string('cannotviewconfig', 'block_ckc_requests_manager'));
}

// check the type of ajax call
// that has been made to this page and redirect
// to that function.
$type = required_param('type', PARAM_TEXT);

if ($type == 'add') {
    block_ckc_requests_manager_add_new_item();
} else if ($type == 'save') {
    block_ckc_requests_manager_save_changes();
} else if ($type == 'page2addfield') {
    block_ckc_requests_manager_add_field();
} else if ($type == 'updatefield') {
    block_ckc_requests_manager_update_field();
} else if ($type == 'addvaluetodropdown') {
    block_ckc_requests_manager_add_value_to_dropdown();
} else if ($type == 'getdropdownvalues') {
    block_ckc_requests_manager_get_dropdown_values();
} else if ($type == 'addnewform') {
    block_ckc_requests_manager_add_new_form();
} else if ($type == 'saveselectedform') {
    block_ckc_requests_manager_save_selected_form();
} else if ($type == 'saveoptionalvalue') {
    block_ckc_requests_manager_save_optional_value();
}//end if


/**
 * Save a selected form
 */
function block_ckc_requests_manager_save_selected_form()
{
    global $GLOBALS['DB'];
    // echo 'saving form';
    $value = required_param('value', PARAM_TEXT);
    $rowId = $GLOBALS['DB']->get_field_select('block_ckc_requests_manager_config', 'id', "varname = 'current_active_form_id'");

    $dataobject->id    = $rowId;
    $dataobject->value = addslashes($value);
    $GLOBALS['DB']->update_record('block_ckc_requests_manager_config', $dataobject);

}//end block_ckc_requests_manager_save_selected_form()


/**
 * Add a new form
 */
function block_ckc_requests_manager_add_new_form()
{
    global $GLOBALS['DB'];

    $formName = required_param('value', PARAM_TEXT);

    $object->id      = '';
    $object->varname = 'page2form';
    $object->value   = $formName;

    $id = $GLOBALS['DB']->insert_record('block_ckc_requests_manager_config', $object, true);

}//end block_ckc_requests_manager_add_new_form()


/**
 * Add a value to a dropdown menu
 */
function block_ckc_requests_manager_add_value_to_dropdown()
{
    global $GLOBALS['DB'];

    $id    = required_param('id', PARAM_INT);
    $value = required_param('value', PARAM_TEXT);

    $object->id      = '';
    $object->fieldid = $id;
    $object->value   = $value;

    $id = $GLOBALS['DB']->insert_record('block_ckc_requests_manager_form_data', $object, true);

}//end block_ckc_requests_manager_add_value_to_dropdown()


/**
 * Update a field
 */
function block_ckc_requests_manager_update_field()
{
    global $GLOBALS['CFG'], $GLOBALS['DB'];
    echo $elementId = required_param('id', PARAM_INT);
    echo $value     = required_param('value', PARAM_TEXT);

    $dataobject->id       = $elementId;
    $dataobject->lefttext = addslashes($value);
    $GLOBALS['DB']->update_record('block_ckc_requests_manager_formfields', $dataobject);

}//end block_ckc_requests_manager_update_field()


/**
 * Add a new field
 */
function block_ckc_requests_manager_add_field()
{
    global $GLOBALS['CFG'], $GLOBALS['DB'];

       $fieldType = required_param('fieldtype', PARAM_TEXT);
    $formId       = required_param('formid', PARAM_TEXT);

    $query  = 'SELECT * FROM '.$GLOBALS['CFG']->prefix."block_ckc_requests_manager_formfields where formid = $formId ORDER BY position DESC";
    $record = $GLOBALS['DB']->get_record_sql($query, null, IGNORE_MISSING);

    // if no record exists, just start of with 1000 and
    // then add one on to the numbering
    $pos = 1000;
    if ($record) {
        $pos = $record->position;
    }

    $pos++;

    if ($fieldType == 'textfield') {
        $object           = new stdClass();
        $object->id       = '';
        $object->type     = 'textfield';
        $object->position = $pos;
        $object->formid   = $formId;
        $object->reqfield = '1';

        $id = $GLOBALS['DB']->insert_record('block_ckc_requests_manager_formfields', $object, true);
        echo $id;
    } else if ($fieldType == 'textarea') {
        $object           = new stdClass();
        $object->id       = '';
        $object->type     = 'textarea';
        $object->position = $pos;
        $object->formid   = $formId;
        $object->reqfield = '1';
        $id               = $GLOBALS['DB']->insert_record('block_ckc_requests_manager_formfields', $object, true);

        echo $id;
    } else if ($fieldType == 'dropdown') {
        $object           = new stdClass();
        $object->id       = '';
        $object->type     = 'dropdown';
        $object->position = $pos;
        $object->formid   = $formId;
        $object->reqfield = '1';
        $id               = $GLOBALS['DB']->insert_record('block_ckc_requests_manager_formfields', $object, true);

        echo $id;
    } else if ($fieldType == 'radio') {
        $object           = new stdClass();
        $object->id       = '';
        $object->type     = 'radio';
        $object->position = $pos;
        $object->formid   = $formId;
        $object->reqfield = '1';
        $id               = $GLOBALS['DB']->insert_record('block_ckc_requests_manager_formfields', $object, true);

        echo $id;
    }//end if

}//end block_ckc_requests_manager_add_field()


/**
 * Get a collection of dropdown menu values
 */
function block_ckc_requests_manager_get_dropdown_values()
{
    $id = required_param('id', PARAM_INT);
    global $GLOBALS['DB'];
    $field3ItemsHTML = '';
    $selectQuery     = "fieldid = '$id'";
    $formid          = $_SESSION['formid'];
    $field3Items     = $GLOBALS['DB']->get_recordset_select('block_ckc_requests_manager_form_data', $select = $selectQuery);

    if ($field3Items->valid()) {
        foreach ($field3Items as $item) {
            $field3ItemsHTML .= '<div class="row">';
            $field3ItemsHTML .= '<div class="col-sm-2">'.format_string($item->value, true, ['context' => context_system::instance()]).'</div>';
            $field3ItemsHTML .= '<div class="col-sm-1"><a href="page2.php?id='.$formid.'&t=dropitem&fid='.$id.'&del='.$item->id.'"><i class="icon fa fa-trash fa-fw " title="'.get_string('delete').'" aria-label="'.get_string('delete').'"></i></a></div>';
            $field3ItemsHTML .= '</div>';
        }
    }

    echo $field3ItemsHTML;

}//end block_ckc_requests_manager_get_dropdown_values()


/**
 * Save changes that have been made
 */
function block_ckc_requests_manager_save_changes()
{
    global $GLOBALS['CFG'];
    global $GLOBALS['DB'];

    $f1t   = required_param('f1t', PARAM_TEXT);
    $f1d   = required_param('f1d', PARAM_TEXT);
    $f2t   = required_param('f2t', PARAM_TEXT);
    $f2d   = required_param('f2d', PARAM_TEXT);
    $f3d   = required_param('f3d', PARAM_TEXT);
    $dStat = required_param('dstat', PARAM_TEXT);

    $field1title_id = $GLOBALS['DB']->get_field_select('block_ckc_requests_manager_config', 'id', "varname = 'page1_fieldname1'");
    $field1desc_id  = $GLOBALS['DB']->get_field_select('block_ckc_requests_manager_config', 'id', "varname = 'page1_fielddesc1'");
    $field2title_id = $GLOBALS['DB']->get_field_select('block_ckc_requests_manager_config', 'id', "varname = 'page1_fieldname2'");
    $field2desc_id  = $GLOBALS['DB']->get_field_select('block_ckc_requests_manager_config', 'id', "varname = 'page1_fielddesc2'");
    $field3desc_id  = $GLOBALS['DB']->get_field_select('block_ckc_requests_manager_config', 'id', "varname = 'page1_fielddesc3'");

    $statusField_id = $GLOBALS['DB']->get_field_select('block_ckc_requests_manager_config', 'id', "varname = 'page1_field3status'");

    $dataobject->id = $field1title_id;
    $dataobject->varname['page1_fieldname1'];
    $dataobject->value = $f1t;
    $GLOBALS['DB']->update_record('block_ckc_requests_manager_config', $dataobject);

    $dataobject->id = $field1desc_id;
    $dataobject->varname['page1_fielddesc1'];
    $dataobject->value = $f1d;
    $GLOBALS['DB']->update_record('block_ckc_requests_manager_config', $dataobject);

    $dataobject->id = $field2title_id;
    $dataobject->varname['page1_fieldname2'];
    $dataobject->value = $f2t;
    $GLOBALS['DB']->update_record('block_ckc_requests_manager_config', $dataobject);

    $dataobject->id = $field2desc_id;
    $dataobject->varname['page1_fielddesc2'];
    $dataobject->value = $f2d;
    $GLOBALS['DB']->update_record('block_ckc_requests_manager_config', $dataobject);

    $dataobject->id = $field3desc_id;
    $dataobject->varname['page1_fielddesc3'];
    $dataobject->value = $f3d;
    $GLOBALS['DB']->update_record('block_ckc_requests_manager_config', $dataobject);

    $dataobject->id = $statusField_id;
    $dataobject->varname['page1_field3status'];
    $dataobject->value = $dStat;
    $GLOBALS['DB']->update_record('block_ckc_requests_manager_config', $dataobject);

}//end block_ckc_requests_manager_save_changes()


/**
 * Add a new item
 */
function block_ckc_requests_manager_add_new_item()
{
    global $GLOBALS['CFG'], $GLOBALS['DB'];

    $newValue = required_param('valuetoadd', PARAM_TEXT);

    $object;
    $object->varname = 'page1_field3value';
    $object->value   = addslashes($newValue);
    $GLOBALS['DB']->insert_record('block_ckc_requests_manager_config', $object, false, $primarykey = 'id');

}//end block_ckc_requests_manager_add_new_item()


/**
 * Save an optional value
 */
function block_ckc_requests_manager_save_optional_value()
{
    global $GLOBALS['CFG'], $GLOBALS['DB'];

    $id    = required_param('id', PARAM_INT);
    $value = required_param('value', PARAM_TEXT);

    $dataobject           = new stdClass();
    $dataobject->id       = $id;
    $dataobject->reqfield = addslashes($value);

    $GLOBALS['DB']->update_record('block_ckc_requests_manager_formfields', $dataobject);

}//end block_ckc_requests_manager_save_optional_value()
