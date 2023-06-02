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
 * @package    block_ckc_requests_manager
 * @copyright  2018 Kyle Goslin, Daniel McSweeney
 * @copyright  2021-2022 Michael Milette (TNG Consulting Inc.), Daniel Keaman
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/* ------------------------------------------------------------------------
 *  displayLists.php is used to display the various different
 *  representations of queues etc.
 * ------------------------------------------------------------------------
 */
if ($GLOBALS['CFG']->branch < 36) {
    require_once($GLOBALS['CFG']->libdir.'/coursecatlib.php');
}

/**
 * Display a list of pending modules for the Admin
 *
 */
function block_ckc_requests_manager_display_admin_list($pendinglist, $includerightpanel, $includeleftcheckbox,
                                           $editcatavailable, $rightpaneltype) {

    global $GLOBALS['CFG'], $GLOBALS['DB'];

    $outputhtml = '';

    $page1_fieldname1 = $GLOBALS['DB']->get_field_select('block_ckc_requests_manager_config', 'value', "varname='page1_fieldname1'");
    $page1_fieldname2 = $GLOBALS['DB']->get_field_select('block_ckc_requests_manager_config', 'value', "varname='page1_fieldname2'");

    $counter = 1;

    foreach ($pendinglist as $rec) {
        // Get a list of all the lecturers
        $lecturerhtml = '';
        $req_values = $rec->req_values;

        if (!empty($req_values)) {
            if (!$course = $GLOBALS['DB']->get_record("course", array("id" => $req_values))) {
                // If the course doesn't exist anymore, just let the process continue..
            } else { // Otherwise, start the process
                $context = context_course::instance($course->id);
                if ($managerroles = get_config('', 'coursemanager')) {
                    $coursemanagerroles = explode(',', $managerroles);
                    foreach ($coursemanagerroles as $roleid) {
                        $role = $GLOBALS['DB']->get_record('role', array('id' => $roleid));
                        $canseehidden = has_capability('moodle/role:viewhiddenassigns', $context);
                        $roleid = (int) $roleid;
                        $namesarray = null;
                        if ($GLOBALS['USER']s = get_role_users($roleid, $context, true, '', 'u.lastname ASC', $canseehidden)) {

                            foreach ($GLOBALS['USER']s as $teacher) {
                                $fullname = fullname($teacher, has_capability('moodle/site:viewfullnames', $context));
                                $namesarray[] = '<a href="' . $GLOBALS['CFG']->wwwroot . '/user/view.php?id=' .
                                        $teacher->id . '&amp;course=' . SITEID . '">' . $fullname . '</a>';
                            }
                        }
                    }
                    if (!empty($namesarray)) {
                        $lecturerhtml = implode(', ', $namesarray);

                    }
                }
            }
        } else {
            // Get the id from who created the record, and get their username

            $fullname = $GLOBALS['DB']->get_field('user', 'username', array('id' => $rec->createdbyid));

            $lecturerhtml = '<a href="' . $GLOBALS['CFG']->wwwroot . '/user/view.php?id=' .
                    $rec->createdbyid . '&amp;course=' . SITEID . '" id="namelink">' . $fullname . '</a>';
        }

        //Get the latest comment
        $latestComment = '';
        $currentmodid = $rec->id;

        $wherequery = "instanceid = '$currentmodid'";
        $modrecords = $GLOBALS['DB']->get_recordset_select('block_ckc_requests_manager_comments', $wherequery);

        foreach ($modrecords as $record) {

            $latestComment = $record->message;
            if (strlen($latestComment) > 55) {
                $latestComment = substr($latestComment, 0, 55);
                $pagename = basename($_SERVER['PHP_SELF']);

                if ($pagename == 'module_manager.php') {
                    $latestComment .= '... <a href="comment.php?type=userq&id=' . $currentmodid . '">[' .
                            get_string('viewmore', 'block_ckc_requests_manager') . ']</a>';

                } else {
                    $latestComment .= '... <a href="comment.php?type=adminq&id=' . $currentmodid . '">[' .
                            get_string('viewmore', 'block_ckc_requests_manager') . ']</a>';
                }
            }
        }

        // Check if shortname exists
        $shortnameexists = $GLOBALS['DB']->record_exists('course', array('shortname' => $rec->modcode));
        $shortnameexistsmode = $GLOBALS['DB']->record_exists('course', array('shortname' => $rec->modcode . ' - ' . $rec->modmode));

        $disabledhtml = '';
        if ($shortnameexists == 1 || $shortnameexistsmode == 1) {
            $disabledhtml = 'disabled="disabled"';
        }

        $outputhtml .= '<div id="existingrequest">';

        if ($includeleftcheckbox == true) {
            $outputhtml .= '
                <div class="custom-control custom-checkbox mr-1">
                    <h2 class="border-top mt-5">
                        <input type="checkbox" id="' . $rec->id .
                            '" class="bulk-action-checkbox custom-control-input mt-3" name="groupedcheck" onClick="addIdToList(' . $rec->id .
                            ')" value="' . $rec->id . '" ' . $disabledhtml . '/>
                        <label for="' . $rec->id . '" class="custom-control-label">' . get_string('Request', 'block_ckc_requests_manager') . ' ID #' . $rec->id . ': ' . format_string($rec->modcode) . '</label>
                    </h2>
                </div>';
        } else {
            $outputhtml .= '
            <h2 class="border-top mt-5">' . get_string('Request', 'block_ckc_requests_manager') . ' ID #' . $rec->id . ': ' . format_string($rec->modcode) . '</h2>
            </div>';
        }
            // ---------- Additional Controls -------------------------------
            if ($includerightpanel == true) {
                // For cmanager_admin.php.
                if ($rightpaneltype == 'admin_queue') {
                    $outputhtml .= '
						<div class="btn-group" padding-bottom:6.5px" id="existingrequesticons">
                            <a class="btn btn-default" href="#" onclick="quickApproveConfirm(' . $rec->id . ',\'' . get_string('quickapprove_desc', 'block_ckc_requests_manager') . '\')"><img src="icons/list/quick.png"/>&nbsp;' . get_string('quickapprove', 'block_ckc_requests_manager') . '</a>
                            <a class="btn btn-default" href="admin/approve_course.php?id=' . $rec->id . '"><img src="icons/list/approve.png">&nbsp;' . get_string('approve', 'block_ckc_requests_manager') . '</a>
                            <a class="btn btn-default" href="admin/deny_course.php?id=' . $rec->id . '"><img src="icons/list/deny.png"/>&nbsp;' . get_string('deny', 'block_ckc_requests_manager') . '</a>
                            <a class="btn btn-default" href="course_request.php?mode=2&edit=' . $rec->id . '"><img src="icons/list/edit.png"/>&nbsp;' . get_string('edit', 'block_ckc_requests_manager') . '</a>
                            <a class="btn btn-default" href="#" onclick="cancelConfirm(' . $rec->id . ',\'' . get_string('configure_delete', 'block_ckc_requests_manager') . '\')" href="#"><img src="icons/list/delete.png"/>&nbsp;' . get_string('delete', 'block_ckc_requests_manager') . '</a>
                            <a class="btn btn-default" href="comment.php?type=adminq&id=' . $rec->id . '"><img src="icons/list/comment.png"/>&nbsp;' . get_string('addviewcomments', 'block_ckc_requests_manager') . '</a>
						</div>
                    ';

                } // For cmanager_admin_arch.php.
                else if ($rightpaneltype == 'admin_arch') {
                    $outputhtml .= '
					<div class="btn-group" padding-bottom:6.5px">
                        <a class="btn btn-default" onclick="cancelConfirm(' . $rec->id . ', \'delete\')" href="#"><img src="icons/list/delete.png"/>&nbsp;' . get_string('delete', 'block_ckc_requests_manager') . '</a>
                        <a class="btn btn-default" href="comment.php?type=adminarch&id=' . $rec->id . '"><img src="icons/list/comment.png"/>&nbsp;' . get_string('addviewcomments', 'block_ckc_requests_manager') . '</a>
					</div>
				';
                } // For module_manager.php.
                else if ($rightpaneltype == 'user_manager') {

                    $outputhtml .= '
    			        <div class="btn-group" padding-bottom:6.5px" id="existingrequesticons">
                            <a class="btn btn-default" href="view_summary.php?id=' . $rec->id . '"><img src="icons/list/open.png"/>&nbsp;' . get_string('view', 'block_ckc_requests_manager') . '</a>
                            <a class="btn btn-default" href="course_request.php?mode=2&edit=' . $rec->id . '"><img src="icons/list/edit.png"/>&nbsp;' . get_string('edit', 'block_ckc_requests_manager') . '</a>
                            <a class="btn btn-default" onclick="cancelConfirm(' . $rec->id . ',\'' . get_string('cmanagerConfirmCancel', 'block_ckc_requests_manager') . '\')" href="#"><img src="icons/list/deny.png"/>&nbsp;' . get_string('cancel', 'block_ckc_requests_manager') . '</a>
                            <a class="btn btn-default" href="comment.php?type=userq&id=' . $rec->id . '"><img src="icons/list/comment.png"/>&nbsp;' . get_string('addviewcomments', 'block_ckc_requests_manager') . '</a>
            			</div>';
                } // For module_manager_history.php.
                else if ($rightpaneltype == 'user_history') {
                    $outputhtml .= '
        				<div class="btn-group" padding-bottom:5px">
    						<a class="btn btn-default" href="view_summary.php?id=' . $rec->id . '"><img src="icons/list/open.png"/>&nbsp;' . get_string('view', 'block_ckc_requests_manager') . '</a>
    						<a class="btn btn-default" href="comment.php?type=userarch&id=' . $rec->id . '"><img src="icons/list/comment.png"/>&nbsp;' . get_string('addviewcomments', 'block_ckc_requests_manager') . '</a>
        				</div>';
                }
            }
            // ------------------ END admin controls ----------------------

            $outputhtml .= '
			 <table class="table-striped mt-2" style="min-width:600px;">
				<tr>
					<th style="width:25%;">' . get_string('status', 'block_ckc_requests_manager') . ':</td>
                    <td>' . get_string('requestReview_' . str_replace(' ', '_', $rec->status), 'block_ckc_requests_manager') . '</td>
				</tr>';

            // Check if shortname exists
            if ($rightpaneltype == 'admin_queue' && $shortnameexists == 1 || $shortnameexistsmode == 1) {
                $outputhtml .= '
                    <tr>
                        <th style="color:red">' . get_string('displayListWarningTitle', 'block_ckc_requests_manager') . ':</td>
                        <td><span style="color:red">' . get_string('displayListWarningSideText', 'block_ckc_requests_manager') . '</span></td>
                    </tr>
                ';
            }

            $outputhtml .= '
				<tr>
					<th>' . get_string('creationdate', 'block_ckc_requests_manager') . ':</th>
					<td>' . $rec->createdate . '</td>
				</tr>

				<tr>
					<th>' . get_string('requesttype', 'block_ckc_requests_manager') . ':</th>
					<td>' . get_string('course_new_mod_create', 'block_ckc_requests_manager') . '</td>
				</tr>

				<tr>
					<th>' . format_string($page1_fieldname1) . ':</th>
					<td>' . format_string($rec->modcode) . '</td>
				</tr>

				<tr>
					<th>' . format_string($page1_fieldname2) . ':</th>
					<td>' . format_string($rec->modname) . '</td>
				</tr>';

            if (isset($rec->modmode)) {
                $selectedmodname = $GLOBALS['DB']->get_field_select('block_ckc_requests_manager_config', 'value', "varname = 'page1_fielddesc3'");

                $outputhtml .= '
					<tr>
					    <th>' . format_string($selectedmodname) . ': </th>
						<td>' . format_string($rec->modmode) . '</td>
					</tr>';
            }

            $catlisthtml = '';
            if ($editcatavailable == true) {
                $movetocategories = array();
                $notused = array();
                if ($GLOBALS['CFG']->branch > 35) {
                    $movetocategories += core_course_category::make_categories_list();
                } else {
                    $movetocategories += coursecat::make_categories_list();
                }

                $cateDrop = html_writer::select($movetocategories, 'cat' . $rec->id, $rec->cate, null);
                $catlisthtml .= '<div id="catname" class="catname">' . $cateDrop . '';
                $catlisthtml .= '<input class="btn btn-default" id="clickMe" type="button" value="' .
                        get_string('update', 'block_ckc_requests_manager') . '" onclick="saveChangedCategory(\'' . $rec->id . '\')" /></div>';

            } else {

                if (!empty($rec->cate)) {
                    $catlisthtml .= format_string($GLOBALS['DB']->get_field_select('course_categories', 'name', "id =" . $rec->cate));

                } else {
                    $catlisthtml = '<em>' . get_string('noneselected', 'block_ckc_requests_manager') . '</em>';
                }

            }
            $outputhtml .= '
                <tr>
					<th> ' . get_string('selectedcategory', 'block_ckc_requests_manager') . ': </th>
					<td>' . $catlisthtml . '</td>
				</tr>';

            if (isset($rec->modkey)) {

                $outputhtml .= '
                    <tr>
                        <th> ' . get_string('configure_EnrolmentKey', 'block_ckc_requests_manager') . ':</th>
                        <td>' . $rec->modkey . '</td>
                    </tr>';

            }

            $outputhtml .= '
				' . block_ckc_requests_manager_generate_summary($rec->id, $rec->formid) . '

				<tr>
					<th>' . get_string('originator', 'block_ckc_requests_manager') . ':</th>
					<td>' . $lecturerhtml . '</td>
				</tr>

				<tr>
					<th>' . get_string('comments', 'block_ckc_requests_manager') . ':</th>
					<td>' . $latestComment . '</td>
				</tr>
			 </table>
			 ';

            $counter++;
        }

        return $outputhtml;
    }

    /**
     * Generate a summary
     */
    function block_ckc_requests_manager_generate_summary($recordid, $formid): string {

        global $GLOBALS['CFG'], $GLOBALS['DB'];

        $generatedhtml = '';

        // Get the form fields from the database.
        $wherequery = "formid = '$formid'";

        $modrecords = $GLOBALS['DB']->get_records('block_ckc_requests_manager_formfields', array('formid' => $formid), $sort = 'position ASC');

        $counter = 1;

        foreach ($modrecords as $record) {
            $fieldidname = 'c' . $counter;

            $generatedhtml .= '<tr>';
            $generatedhtml .= '  <td>';
            $generatedhtml .= '  <strong>' . format_string($record->lefttext) . ': </strong>';
            $generatedhtml .= ' </td>';
            $generatedhtml .= '	<td>';
            $customfield = $GLOBALS['DB']->get_field('block_ckc_requests_manager_records', $fieldidname, array('id' => $recordid));
            $generatedhtml .= format_string($customfield);
            $generatedhtml .= '	</td>';
            $generatedhtml .= '</tr>';

            $counter++;
        }
        return $generatedhtml;
    }




