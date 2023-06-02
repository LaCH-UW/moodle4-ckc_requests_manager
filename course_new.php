<?php

/**
 * COURSE REQUEST MANAGER
 *
 * @package   block_ckc_requests_manager
 * @copyright 2018 Kyle Goslin, Daniel McSweeney
 * @copyright 2021-2022 Michael Milette (TNG Consulting Inc.), Daniel Keaman
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once '../../config.php';
require_once $GLOBALS['CFG']->libdir.'/formslib.php';

// Navigation Bar.
$PAGE->navbar->ignore_active();
$moduleMgrUrl = new moodle_url('/blocks/ckc_requests_manager/module_manager.php');
$PAGE->navbar->add(
    get_string('cmanagerDisplay', 'block_ckc_requests_manager'),
    $moduleMgrUrl
);
$blockRequestUrl = new moodle_url('/blocks/ckc_requests_manager/course_request.php');
$PAGE->navbar->add(
    get_string('block_request', 'block_ckc_requests_manager'),
    $blockRequestUrl
);
$PAGE->navbar->add(get_string('formBuilder_step2', 'block_ckc_requests_manager'));
$PAGE->set_url('/blocks/ckc_requests_manager/course_new.php');
$PAGE->set_context(context_system::instance());

$PAGE->set_title(get_string('formBuilder_step2', 'block_ckc_requests_manager'));
$PAGE->set_heading(get_string('formBuilder_step2', 'block_ckc_requests_manager'));

$context = context_system::instance();

if (false === has_capability('block/cmanager:addrecord', $context)) {
    print_error(get_string('cannotrequestcourse', 'block_ckc_requests_manager'));
}

// Get the session var to take the record from the database
// which we will populate this form with.
$inEditingMode = false;

$editId      = optional_param('edit', '0', PARAM_INT);
$currentSess = $_SESSION['cmanager_session'];

if (false === empty($editId)) {
    $inEditingMode = true;
    $currentSess   = $editId;
}

/**
 * Course requests manager new course form.
 *
 * Form fields for additional data during the process of requesting a new course.
 */
class block_ckc_requests_manager_new_course_form extends moodleform
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
        global $currentSess;
        global $inEditingMode;

        $currentRecord = $GLOBALS['DB']->get_record(
            'block_ckc_requests_manager_records',
            ['id' => $currentSess]
        );
        // Page description text.
        $this->_form->addElement(
            'html',
            '<p>'.get_string(
                'formBuilder_previewInstructions1',
                'block_ckc_requests_manager'
            ).'</p>'
        );
        // Dynamically generate the form from the pre-designed selected form.
        $formId     = $GLOBALS['DB']->get_field_select(
            'block_ckc_requests_manager_config',
            'value',
            "varname = 'current_active_form_id'"
        );
        $formFields = $GLOBALS['DB']->get_records(
            'block_ckc_requests_manager_formfields',
            ['formid' => $formId],
            'position ASC'
        );

        $fieldNameCounter = 1;

        foreach ($formFields as $field) {
            $fieldname  = 'f'.$fieldNameCounter;
            $fieldValue = '';
            $fieldId    = 0;

            if ($inEditingMode === true) {
                $fname      = 'c'.$fieldNameCounter;
                $fieldValue = $currentRecord->$fname;
            }

            $fieldType = '';
            // Give each field an incremented fieldname.
            switch ($field->type) {
                case 'textfield':
                    $fieldType = 'text_field';
                break;

                case 'textarea':
                    $fieldType = 'text_area';
                break;

                case 'dropdown':
                    $fieldType = 'dropdown';
                break;

                case 'radio':
                    $fieldType = 'radio';
                break;

                default:
                continue;
                break;
            }//end switch

            $fieldBuilderFunction = 'block_ckc_requests_manager_create_'.$fieldType;
            $fieldBuilderFunction(
                stripslashes(format_string($field->lefttext)),
                $this->_form,
                $fieldname,
                $fieldValue,
                $field->reqfield,
                $field->id
            );
            $fieldNameCounter++;
        }//end foreach

        $this->_form->addElement('html', '<p></p>&nbsp<p></p>');
        $buttonArray   = [];
        $buttonArray[] = &$this->_form->createElement(
            'submit',
            'submitbutton',
            get_string('Continue', 'block_ckc_requests_manager')
        );
        $buttonArray[] = &$this->_form->createElement(
            'cancel',
            'cancel',
            get_string('requestReview_CancelRequest', 'block_ckc_requests_manager')
        );
        $this->_form->addGroup($buttonArray, 'buttonar', '', [' '], false);
        $this->_form->addElement('html', '<p></p>&nbsp<p></p>');

    }//end definition()


}//end class

$mForm    = new block_ckc_requests_manager_new_course_form();
$fromData = $mForm->get_data();

if ($mForm->is_cancelled() === true) {
    echo '<script>window.location="module_manager.php";</script>';
    die;
} else if (false === empty($fromData)) {
    // Update all the information in the database record.
    $newRecord     = new stdClass();
    $newRecord->id = $currentSess;

    if (false === empty($fromData->f1)) {
        $newRecord->c1 = $fromData->f1;
    }

    if (false === empty($fromData->f2)) {
        $newRecord->c2 = $fromData->f2;
    }

    if (false === empty($fromData->f3)) {
        $newRecord->c3 = $fromData->f3;
    }

    if (false === empty($fromData->f4)) {
        $newRecord->c4 = $fromData->f4;
    }

    if (false === empty($fromData->f5)) {
        $newRecord->c5 = $fromData->f5;
    }

    if (false === empty($fromData->f6)) {
        $newRecord->c6 = $fromData->f6;
    }

    if (false === empty($fromData->f7)) {
        $newRecord->c7 = $fromData->f7;
    }

    if (false === empty($fromData->f8)) {
        $newRecord->c8 = $fromData->f8;
    }

    if (false === empty($fromData->f9)) {
        $newRecord->c9 = $fromData->f9;
    }

    if (false === empty($fromData->f10)) {
        $newRecord->c10 = $fromData->f10;
    }

    if (false === empty($fromData->f11)) {
        $newRecord->c11 = $fromData->f11;
    }

    if (false === empty($fromData->f12)) {
        $newRecord->c12 = $fromData->f12;
    }

    if (false === empty($fromData->f13)) {
        $newRecord->c13 = $fromData->f13;
    }

    if (false === empty($fromData->f14)) {
        $newRecord->c14 = $fromData->f14;
    }

    if (false === empty($fromData->f15)) {
        $newRecord->c15 = $fromData->f15;
    }

    // Tag the module as new.
    $newRecord->status = 'PENDING';
    $GLOBALS['DB']->update_record('block_ckc_requests_manager_records', $newRecord);

    echo "<script>window.location='review_request.php?id=".$currentSess."';</script>";
    die;
    // @FIXME
}//end if


/**
 * Dynamic text field creation.
 *
 * @param mixed $leftText   Label.
 * @param mixed $form       Form object.
 * @param mixed $fieldName  Name of the form field.
 * @param mixed $fieldValue Field value.
 * @param mixed $reqField   Is field required.
 *
 * @return void
 */
function block_ckc_requests_manager_create_text_field($leftText, $form, $fieldName, $fieldValue, $reqField)
{
    $attributes          = [];
    $attributes['value'] = $fieldValue;
    $form->addElement('text', $fieldName, $leftText, $attributes);
    $form->setType($fieldName, PARAM_TEXT);

    if (intval($reqField) === 1) {
        $form->addRule($fieldName, '', 'required', null, 'server', false, false);
    }

}//end block_ckc_requests_manager_create_text_field()


/**
 * Dynamic text area creation.
 *
 * @param mixed $leftText   Label.
 * @param mixed $form       Form object.
 * @param mixed $fieldName  Name of the form field.
 * @param mixed $fieldValue Field value.
 * @param mixed $reqField   Is field required.
 *
 * @return void
 */
function block_ckc_requests_manager_create_text_area($leftText, $form, $fieldName, $fieldValue, $reqField)
{
    $attributes         = [];
    $attributes['wrap'] = 'virtual';
    $attributes['rows'] = '5';
    $attributes['cols'] = '60';

    $form->addElement('textarea', $fieldName, $leftText, $attributes);
    $form->setDefault($fieldName, $fieldValue);
    $form->setType($fieldName, PARAM_TEXT);

    if (intval($reqField) === 1) {
        $form->addRule($fieldName, '', 'required', null, 'server', false, false);
    }

}//end block_ckc_requests_manager_create_text_area()


/**
 * Dynamic radio button creation.
 *
 * @param mixed $leftText      Label.
 * @param mixed $form          Form object.
 * @param mixed $fieldName     Name of the form field.
 * @param mixed $selectedValue Field value.
 * @param mixed $reqField      Is field required.
 * @param mixed $id            Field id.
 *
 * @return void
 */
function block_ckc_requests_manager_create_radio($leftText, $form, $fieldName, $selectedValue, $reqField, $id)
{
    $field3Items = $GLOBALS['DB']->get_recordset_select(
        'block_ckc_requests_manager_form_data',
        'fieldid = :fieldid',
        ['fieldid' => $id]
    );

    $attributes = '';
    $radioArray = [];

    foreach ($field3Items as $item) {
        $radioArray[] =& $form->createElement(
            'radio',
            $fieldName,
            '',
            $item->value,
            $item->value,
            $attributes
        );
    }

    $separator = '';

    if (count($radioArray) > 1) {
        $separator = '<br>';
    }

    $form->addGroup($radioArray, $fieldName, $leftText, [$separator], false);

    if (intval($reqField) === 1) {
        $form->addRule($fieldName, '', 'required', null, 'server', false, false);
    }

    $form->setDefault($fieldName, $selectedValue);
    $form->setType($fieldName, PARAM_TEXT);

}//end block_ckc_requests_manager_create_radio()


/**
 * Dynamically create a drop down select menu.
 *
 * @param mixed $leftText      Label.
 * @param mixed $form          Form object.
 * @param mixed $fieldName     Name of the form field.
 * @param mixed $selectedValue Field value.
 * @param mixed $reqField      Is field required.
 * @param mixed $id            Field id.
 *
 * @return void
 */
function block_ckc_requests_manager_create_dropdown($leftText, $form, $fieldName, $selectedValue, $reqField, $id)
{
    $options     = [];
    $field3Items = $GLOBALS['DB']->get_recordset_select(
        'block_ckc_requests_manager_form_data',
        'fieldid = :fieldid',
        ['fieldid' => $id]
    );

    foreach ($field3Items as $item) {
        if ($item->value !== '') {
            $options[$item->value] = format_string($item->value);
        }
    }

    $form->addElement('select', $fieldName, $leftText, $options);
    $form->setDefault($fieldName, $selectedValue);

    if (intval($reqField) === 1) {
        $form->addRule($fieldName, '', 'required', null, 'server', false, false);
    }

    $form->setType($fieldName, PARAM_TEXT);

}//end block_ckc_requests_manager_create_dropdown()


echo $OUTPUT->header();

$mForm->focus();
$mForm->set_data($mForm);
$mForm->display();

echo $OUTPUT->footer();
