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
global $GLOBALS['CFG'], $GLOBALS['DB'];

require_once $GLOBALS['CFG']->libdir.'/formslib.php';

require_login();

require_once '../../course/lib.php';
require_once 'lib/displayLists.php';
require_once 'lib/boot.php';

// Navigation Bar
$PAGE->navbar->ignore_active();
$PAGE->navbar->add(get_string('cmanagerDisplay', 'block_ckc_requests_manager'), new moodle_url('/blocks/ckc_requests_manager/module_manager.php'));
$PAGE->navbar->add(get_string('currentrequests', 'block_ckc_requests_manager'));

$PAGE->set_url('/blocks/ckc_requests_manager/cmanager_admin.php');
$PAGE->set_context(context_system::instance());
$PAGE->set_heading(get_string('currentrequests', 'block_ckc_requests_manager'));
$PAGE->set_title(get_string('currentrequests', 'block_ckc_requests_manager'));
echo $OUTPUT->header();


$_SESSION['CRMisAdmin'] = true;


$context = context_system::instance();
if (!has_capability('block/cmanager:approverecord', $context)) {
    print_error(get_string('cannotviewconfig', 'block_ckc_requests_manager'));
}

?>


<link rel="stylesheet" type="text/css" href="js/jquery/jquery-ui.css"></script>
<script src="js/jquery/jquery-3.3.1.min.js"></script>
<script src="js/jquery/jquery-ui.js"></script>

<?php
/**
 * Admin console
 *
 * Admin console interface
 *
 * @package   block_ckc_requests_manager
 * @copyright 2018 Kyle Goslin, Daniel McSweeney
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_ckc_requests_manager_admin_form extends moodleform
{


    function definition()
    {
        global $GLOBALS['CFG'];
        global $GLOBALS['USER'], $GLOBALS['DB'];
        $mform =& $this->_form;
        // Don't forget the underscore!
        $selectQuery = "status = 'PENDING' ORDER BY id ASC";

        // If search is enabled then use the
        // search parameters
        if ($_POST && isset($_POST['search'])) {
            $searchText = required_param('searchtext', PARAM_TEXT);
            // if nothing was entered for the search string
            // send them back with a warning message.
            if ($searchText == '') {
                echo generateGenericPop('genpop1', get_string('alert', 'block_ckc_requests_manager'), get_string('cmanager_admin_enterstring', 'block_ckc_requests_manager'), get_string('ok', 'block_ckc_requests_manager'));
                echo '<script>$("#genpop1").modal();

            $("#genpop1").click(function(){

             window.location="cmanager_admin.php";
            });

            </script>';

                die;
            }

            $searchType = required_param('searchtype', PARAM_TEXT);

            if (!empty($searchText) && !empty($searchType)) {
                if ($searchType == 'code') {
                    $selectQuery = "`modcode` LIKE '%{$searchText}%'";
                } else if ($searchType == 'title') {
                    $selectQuery = "`modname` LIKE '%{$searchText}%'";
                } else if ($searchType == 'requester') {
                    $selectQuery = '`createdbyid` IN (Select id from '.$GLOBALS['CFG']->prefix."user where `firstname` LIKE '%{$searchText}%' OR `lastname`
            LIKE '%{$searchText}%' OR `username` LIKE '%{$searchText}%')";
                }
            }

            // if ($searchType != 'requester'){
            $selectQuery .= " AND status = 'PENDING' ORDER BY id ASC";
            // }
        }//end if

        // Get the list of records
        $pendingList = $GLOBALS['DB']->get_recordset_select('block_ckc_requests_manager_records', $select = $selectQuery);
        $outputHTML  = block_ckc_requests_manager_display_admin_list($pendingList, true, true, true, 'admin_queue');

        $page1_fieldname1 = $GLOBALS['DB']->get_field_select('block_ckc_requests_manager_config', 'value', "varname='page1_fieldname1'");
        $page1_fieldname2 = $GLOBALS['DB']->get_field_select('block_ckc_requests_manager_config', 'value', "varname='page1_fieldname2'");

        $searchHTML = '
    <h2 class="h4">'.get_string('filter').'</h2>
    <form action="cmanager_admin.php?search=1" method="post">
	 	<label for="searchtype">'.get_string('search_side_text', 'block_ckc_requests_manager').'</label>
	 	<select name="searchtype" id="searchtype">
            <option value="code">'.format_string($page1_fieldname1).'</option>
            <option value="title">'.format_string($page1_fieldname2).'</option>
            <option value="requester">'.get_string('searchAuthor', 'block_ckc_requests_manager').'</option>
		</select>
        <label for="searchtext">'.get_string('for').'</label>
        <input type="text" name="searchtext" id="searchtext">
		<input class="btn btn-primary" type="submit" value="'.get_string('searchbuttontext', 'block_ckc_requests_manager').'" name="search">
';
        if ($_POST && isset($_POST['search'])) {
            $searchHTML .= '<a class="btn btn-default" href="cmanager_admin.php">['.get_string('clearsearch', 'block_ckc_requests_manager').']</a>';
        }

        $searchHTML .= '</form>';

        $bulkActions = '
    <h2 class="h4">'.get_string('bulkactions', 'block_ckc_requests_manager').'</h2>
    <form>
        <input id="bulk-action-checkbox" class="bulk-action-checkbox" type="checkbox" onClick="toggle(this)">&nbsp;<label for="bulk-action-checkbox">Select All</label>
        <select id="bulk" onchange="bulkaction();">
            <option></option>
            <option value ="Approve">'.get_string('bulkapprove', 'block_ckc_requests_manager').'</option>
            <option value="Deny">'.get_string('deny', 'block_ckc_requests_manager').'</option>
            <option value ="Delete">'.get_string('delete', 'block_ckc_requests_manager').'</option>
        </select>
    </form>
';

        $mainBody = '
    <div class="row border" style="background-color: #eee;">
        <div class="col-md-12 col-lg-7 col-xl-6">
            '.$searchHTML.'
        </div>
        <div class="col-md-12 col-lg-4">
            '.$bulkActions.'
        </div>
    </div>
    <div>
        '.$outputHTML.'
    </div>
';

        $mform->addElement('html', $mainBody);

    }//end definition()


    // Close the function
}//end class

  // Close the class
echo '<script></script>';


$mform = new block_ckc_requests_manager_admin_form();

if ($mform->is_cancelled()) {
} else if ($fromform = $mform->get_data()) {
} else {
}


$mform->focus();
$mform->display();
echo $OUTPUT->footer();



if ($_POST && isset($_POST['search'])) {
    $searchText = required_param('searchtext', PARAM_TEXT);
    $searchType = required_param('searchtype', PARAM_TEXT);

    echo "<script>document.getElementById('searchtext').value = '$searchText'; ";
    echo "
    var desiredValue = '$searchType';
    var el = document.getElementById('searchtype');
    for(var i=0; i<el.options.length; i++) {
      if ( el.options[i].value == desiredValue ) {
        el.selectedIndex = i;
        break;
      }
    }
    </script>
    ";
}

// Modal for deleting requests
echo generateGenericConfirm(
    'delete_modal',
    get_string('alert', 'block_ckc_requests_manager'),
    get_string('configure_delete', 'block_ckc_requests_manager'),
    get_string('yesDeleteRecords', 'block_ckc_requests_manager')
);

// Modal for quick approve
echo generateGenericConfirm(
    'quick_approve',
    get_string('alert', 'block_ckc_requests_manager'),
    get_string('quickapprove_desc', 'block_ckc_requests_manager'),
    get_string('quickapprove', 'block_ckc_requests_manager')
);
?>





<script>
var deleteRec = 0;
var quickApp = 0;
// quick approve ok button click handler
$("#okquick_approve").click(function(){

   window.location = "admin/bulk_approve.php?mul=" + quickApp;
});



// delete request ok  button click handler
$("#okdelete_modal").click(function(){


   window.location = "deleterequest.php?t=a&&id=" + deleteRec;
});



// Ask the user do they really want to delete
// the request using a dialog.
function cancelConfirm(id,langString) {
    deleteRec = id;
    $("#popup_text").html(langString);
    $("#delete_modal").modal();




}

function quickApproveConfirm(id,langString) {
    quickApp = id;
    window.onbeforeunload = null;
    $("#popup_quick_text").html(langString);
    $("#quick_approve").modal();


}


var checkedIds  = ['null'];


// List of currently selected Ids for use
// with the bulk actions
function addIdToList(id) {
    var i = checkedIds.length;
    var found = false;

    while (i--) {
        if (checkedIds[i] === id) {
              checkedIds[i] = 'null';
            found = true;
        }
    }

    if (found === false) {
        checkedIds.push(id);
    }
}


/**
 * This function is used to save the text from the
 * categories when they are changed.
 */
function saveChangedCategory(recordId) {

   var fieldvalue = document.getElementById('menucat' + recordId).value;


    $.post("ajax_functions.php", { type: 'updatecategory', value: fieldvalue, recId: recordId },
               function(data) {
                 //
               });


}


// When the select all option is picked in the bulk actions
// this is the function that is run.
function toggle(source) {
  checkboxes = document.getElementsByName('groupedcheck');
  for(var i=0, n=checkboxes.length;i<n;i++) {
    checkboxes[i].checked = source.checked;
        addIdToList(checkboxes[i].id);
  }
}


//
// List of the different bulk actions that can be performed
// on different requests in the queue.
//
function bulkaction(){

    var cur = document.getElementById('bulk');

    if(cur.value == 'Delete'){

    $.post("ajax_functions.php", { type: "del", values: checkedIds},
           function(data) {
                    window.location='cmanager_admin.php';

           });

    }


    if(cur.value == 'Deny'){
        window.location='admin/bulk_deny.php?mul=' + checkedIds;
    }


    if(cur.value == 'Approve'){
        window.location='admin/bulk_approve.php?mul=' + checkedIds;
    }

}
</script>




