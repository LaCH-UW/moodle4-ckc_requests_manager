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
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once '../../config.php';
global $GLOBALS['CFG'], $GLOBALS['DB'];



$type = required_param('type', PARAM_TEXT);

if ($type == 'del') {
    $values = $_POST['values'];
    foreach ($values as $id) {
        if ($id != 'null') {
            $GLOBALS['DB']->delete_records('block_ckc_requests_manager_records', ['id' => $id]);
            // Delete associated comments
            $GLOBALS['DB']->delete_records('block_ckc_requests_manager_comments', ['instanceid' => $id]);
        }
    }
}


/*
 * Update the values for emails.
 */
if ($type == 'updatefield') {
    $post_value = required_param('value', PARAM_TEXT);
    $post_id    = required_param('id', PARAM_TEXT);

    $selectQuery    = "varname = '$post_id'";
      $recordExists = $GLOBALS['DB']->record_exists_select('block_ckc_requests_manager_config', $selectQuery);


    if ($recordExists) {
        // If the record exists
        $current_record  = $GLOBALS['DB']->get_record('block_ckc_requests_manager_config', ['varname' => $post_id]);
        $newrec          = new stdClass();
        $newrec->id      = $current_record->id;
        $newrec->varname = $post_id;
        $newrec->value   = $post_value;
        $GLOBALS['DB']->update_record('block_ckc_requests_manager_config', $newrec);

        echo 'updated';
    } else {
        $newrec          = new stdClass();
        $newrec->varname = $post_id;
        $newrec->value   = $post_value;
         $GLOBALS['DB']->insert_record('block_ckc_requests_manager_config', $newrec);
         echo 'inserted';
    }
}//end if

if ($type == 'updatecategory') {
      $value        = required_param('value', PARAM_TEXT);
      $recId        = required_param('recId', PARAM_TEXT);
      $newrec       = new stdClass();
      $newrec->id   = $recId;
      $newrec->cate = $value;
      $GLOBALS['DB']->update_record('block_ckc_requests_manager_records', $newrec);
}
