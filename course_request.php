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
require_once '../../course/lib.php';
// @QUESTION why??
if ($GLOBALS['CFG']->branch < 36) {
    include_once $GLOBALS['CFG']->libdir.'/coursecatlib.php';
}

require_login();

// Stop guests from making requests!
if (isguestuser() === true) {
    echo error(get_string('guestsarenotallowed', 'error'));
    die;
}

$currentMode = optional_param('mode', '', PARAM_INT);

$pageTitle = 'modrequestfacility';

if ($currentMode === 1) {
    // Make a new request.
    $pageTitle = 'block_request';
} else if ($currentMode === 2) {
    // Editing mode.
    $pageTitle = 'requestReview_AlterRequest';
}

// Navigation Bar..
$PAGE->navbar->ignore_active();
$moduleMgrUrl = new moodle_url('/blocks/ckc_requests_manager/module_manager.php');
$PAGE->navbar->add(
    get_string('cmanagerDisplay', 'block_ckc_requests_manager'),
    $moduleMgrUrl
);
$PAGE->navbar->add(get_string($pageTitle, 'block_ckc_requests_manager'));
$PAGE->set_url('/blocks/ckc_requests_manager/course_request.php');
$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_heading(get_string($pageTitle, 'block_ckc_requests_manager'));
$PAGE->set_title(get_string($pageTitle, 'block_ckc_requests_manager'));

echo $OUTPUT->header();

// Main variable for storing the current session id.
$currentSess = '00';

if ($currentMode === 1) {
    // Make a new request.
    if (has_capability('block/cmanager:addrecord', $context) === true) {
        $_SESSION['cmanager_addedmods'] = '';
        $_SESSION['editingmode']        = 'false';

        $newRecord              = new stdClass();
        $newRecord->modname     = '';
        $newRecord->createdbyid = $GLOBALS['USER']->id;
        $newRecord->createdate  = date('d/m/y H:i:s');
        $newRecord->formid      = $GLOBALS['DB']->get_field(
            'block_ckc_requests_manager_config',
            'value',
            ['varname' => 'current_active_form_id']
        );

        $currentSess                  = $GLOBALS['DB']->insert_record(
            'block_ckc_requests_manager_records',
            $newRecord,
            true
        );
        $_SESSION['cmanager_session'] = $currentSess;
    } else {
        print_error(get_string('cannotrequestcourse', 'block_ckc_requests_manager'));
    }//end if
} else if ($currentMode === 2) {
    // Editing mode.
    if (has_capability('block/cmanager:editrecord', $context) === true) {
        $_SESSION['editingmode']      = 'true';
        $currentSess                  = optional_param('edit', '0', PARAM_INT);
        $_SESSION['cmanager_session'] = $currentSess;
        $_SESSION['cmanagermode']     = 'admin';
    } else {
        print_error(get_string('cannoteditrequest', 'block_ckc_requests_manager'));
    }
} else {
    // If a session has already been started.
    $currentSess = $_SESSION['cmanager_session'];
}//end if

$currentRecord = $GLOBALS['DB']->get_record(
    'block_ckc_requests_manager_records',
    ['id' => $currentSess],
    '*',
    IGNORE_MULTIPLE
);

/**
 * Course request form
 *
 * Main course request form
 */
class block_ckc_requests_manager_courserequest_form extends moodleform
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
        global $currentRecord;

        $field1desc = $GLOBALS['DB']->get_field(
            'block_ckc_requests_manager_config',
            'value',
            ['varname' => 'page1_fielddesc1'],
            IGNORE_MULTIPLE
        );
        $field2desc = $GLOBALS['DB']->get_field(
            'block_ckc_requests_manager_config',
            'value',
            ['varname' => 'page1_fielddesc2'],
            IGNORE_MULTIPLE
        );
        // Get the field values.
        $field1title = $GLOBALS['DB']->get_field(
            'block_ckc_requests_manager_config',
            'value',
            ['varname' => 'page1_fieldname1'],
            IGNORE_MULTIPLE
        );
        $field2title = $GLOBALS['DB']->get_field(
            'block_ckc_requests_manager_config',
            'value',
            ['varname' => 'page1_fieldname2'],
            IGNORE_MULTIPLE
        );
        $field3desc  = $GLOBALS['DB']->get_field(
            'block_ckc_requests_manager_config',
            'value',
            ['varname' => 'page1_fielddesc3'],
            IGNORE_MULTIPLE
        );
        $field4title = $GLOBALS['DB']->get_field(
            'block_ckc_requests_manager_config',
            'value',
            ['varname' => 'page1_fieldname4'],
            IGNORE_MULTIPLE
        );
        $field4desc  = $GLOBALS['DB']->get_field(
            'block_ckc_requests_manager_config',
            'value',
            ['varname' => 'page1_fielddesc4'],
            IGNORE_MULTIPLE
        );
        // Get field 3 status.
        $field3status = $GLOBALS['DB']->get_field(
            'block_ckc_requests_manager_config',
            'value',
            ['varname' => 'page1_field3status'],
            IGNORE_MULTIPLE
        );
        // Get the value for autokey - the config variable that determines enrolment key auto or prompt.
        $autoKey = $GLOBALS['DB']->get_field_select(
            'block_ckc_requests_manager_config',
            'value',
            "varname = 'autoKey'"
        );
        // Get the value for category selection - the config variable that determines if user can choose a category.
        $selfCat = $GLOBALS['DB']->get_field_select(
            'block_ckc_requests_manager_config',
            'value',
            "varname = 'selfcat'"
        );
        // Page description text.
        $this->_form->addElement(
            'html',
            '<p>'.get_string('courserequestline1', 'block_ckc_requests_manager').'</p>'
        );
        $this->_form->addElement(
            'html',
            '<h2>'.get_string('step1text', 'block_ckc_requests_manager').'</h2>'
        );
        // Course shortname.
        $attributes          = [];
        $attributes['value'] = $currentRecord->modcode;
        $this->_form->addElement(
            'text',
            'programmecode',
            format_string($field1title),
            $attributes,
            ''
        );
        $this->_form->addHelpButton('programmecode', 'shortnamecourse');
        $this->_form->addRule(
            'programmecode',
            get_string('request_rule1', 'block_ckc_requests_manager'),
            'required',
            null,
            'server',
            false,
            false
        );
        $this->_form->setType('programmecode', PARAM_TEXT);
        $this->_form->addElement('static', 'description', '', format_string($field1desc));
        // Course fullname.
        $attributes          = [];
        $attributes['value'] = $currentRecord->modname;
        $this->_form->addElement(
            'text',
            'programmetitle',
            format_string($field2title),
            $attributes
        );
        $this->_form->addHelpButton(
            'programmetitle',
            'fullnamecourse'
        );
        $this->_form->addRule(
            'programmetitle',
            get_string('request_rule1', 'block_ckc_requests_manager'),
            'required',
            null,
            'server',
            false,
            false
        );
        $this->_form->setType(
            'programmetitle',
            PARAM_TEXT
        );
        $this->_form->addElement(
            'static',
            'description',
            '',
            format_string($field2desc)
        );

        // Optional Dropdown.
        if ($field3status === 'enabled') {
            $options     = [];
            $selectQuery = "varname = 'page1_field3value'";
            $field3Items = $GLOBALS['DB']->get_recordset_select(
                'block_ckc_requests_manager_config',
                $selectQuery
            );

            foreach ($field3Items as $item) {
                if ($item->value !== '') {
                    $options[$item->value] = format_string($item->value);
                }
            }

            $this->_form->addElement(
                'select',
                'programmemode',
                format_string($field3desc),
                $options
            );
            $this->_form->addRule(
                'programmemode',
                get_string('request_rule2', 'block_ckc_requests_manager'),
                'required',
                null,
                'server',
                false,
                false
            );
            $this->_form->setDefault(
                'programmemode',
                $currentRecord->modmode
            );
            $this->_form->setType('programmemode', PARAM_TEXT);
        }//end if

        // If enabled, give the user the option to select a category location for the course.
        if ($selfCat === 'yes') {
            $class = 'coursecat';

            if ($GLOBALS['CFG']->branch > 35) {
                $class = 'core_course_category';
            }

            $options = $class::make_categories_list();

            $this->_form->addElement(
                'select',
                'menucategory',
                get_string('requestForm_category', 'block_ckc_requests_manager'),
                $options
            );
            $this->_form->addHelpButton('menucategory', 'coursecategory');

            $menuCategoryDefault = $GLOBALS['CFG']->defaultrequestcategory;

            if ($_SESSION['editingmode'] === 'true') {
                $menuCategoryDefault = $currentRecord->cate;
            }

            $this->_form->setDefault(
                'menucategory',
                $menuCategoryDefault
            );
        }//end if

        // Enrolment key.
        if (false === empty($autoKey)) {
            $attributes          = [];
            $attributes['value'] = $currentRecord->modkey;
            $this->_form->addElement(
                'text',
                'enrolkey',
                $field4title,
                $attributes
            );
            $this->_form->addRule(
                'enrolkey',
                get_string('request_rule3', 'block_ckc_requests_manager'),
                'required',
                null,
                'server',
                false,
                false
            );
            $this->_form->setType('enrolkey', PARAM_TEXT);
        }

        // Hidden form element to pass the key.
        if (isset($_GET['edit']) === true) {
            $this->_form->addElement('hidden', 'editingmode', $currentSess);
            $this->_form->setType('editingmode', PARAM_TEXT);
        }

        // Submit / Cancel buttons.
        $buttonArray   = [];
        $buttonArray[] = &$this->_form->createElement(
            'submit',
            'submitbutton',
            get_string('Continue', 'block_ckc_requests_manager')
        );
        $buttonArray[] = &$this->_form->createElement(
            'cancel',
            'cancel',
            get_string('cancel', 'block_ckc_requests_manager')
        );
        $this->_form->addGroup($buttonArray, 'buttonar', '', [' '], false);

    }//end definition()


}//end class

$mForm    = new block_ckc_requests_manager_courserequest_form();
$fromData = $mForm->get_data();

if ($mForm->is_cancelled() === true) {
    echo '<script>window.location="module_manager.php";</script>';
    die;
} else if (false === empty($fromData)) {
    $newRecord     = new stdClass();
    $newRecord->id = $currentSess;

    $newRecord->modname = $fromData->programmetitle;
    $newRecord->modcode = $fromData->programmecode;

    if (false === empty($fromData->menucategory)) {
        $newRecord->cate = $fromData->menucategory;
    }

    if (false === empty($fromData->enrolkey)) {
        $newRecord->modkey = $fromData->enrolkey;
    }

    if (false === empty($fromData->programmemode)) {
        $newRecord->modmode = $fromData->programmemode;
    }

    $GLOBALS['DB']->update_record('block_ckc_requests_manager_records', $newRecord);


    $postCode = $fromData->programmecode;

    $postMode = '';
    if (false === empty($fromData->programmemode)) {
        $postMode = $fromData->programmemode;
    }

    // Find which records are similar to the one which we are currently looking for.
    $spaceCheck = substr($postCode, 0, 4).' '.substr($postCode, 4, strlen($postCode));

    if (strpos($spaceCheck, '?') !== false) {
        $spaceCheck = str_replace('?', '', $spaceCheck);
    }

    if (strpos($postMode, '?') !== false) {
        $postMode = str_replace('?', '', $postMode);
    }

    if (strpos($postCode, '?') !== false) {
        $postCode = str_replace('?', '', $postCode);
    }

    // If we are in editing mode move to editing.
    $editingMode = $_SESSION['editingmode'];

    if ($editingMode === 'true') {
        echo "<script>window.location='course_new.php?mode=2&edit=".$_SESSION['cmanager_session']."';</script>";
        die;
    }

    // If we are not in editing mode, continue search or creation.
    $selectQuery = "shortname LIKE '%".addslashes($postCode)."%'
                    OR (shortname LIKE '%".addslashes($spaceCheck)."%'
                    AND shortname LIKE '%".addslashes($postMode)."%')
                    OR shortname LIKE '%".addslashes($spaceCheck)."%' ";

    $recordsExist = boolval($GLOBALS['DB']->record_exists_select('course', $selectQuery));

    if ($recordsExist === true) {
        echo "<script>window.location='course_exists.php';</script>";
    } else {
        echo "<script>window.location='course_new.php';</script>";
    }

    die;
}//end if

$mForm->focus();
$mForm->set_data($mForm);
$mForm->display();

if (false === empty($currentRecord->cate)) {
    echo '<script> document.getElementById("menucategory").value = '.$currentRecord->cate.'; </script> ';
}

echo $OUTPUT->footer();
