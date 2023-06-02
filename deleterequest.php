<?php

/**
 * Delete request.php
 *
 * This page is called through AJAX to delete a specific
 * request and all associated comments.
 *
 * @category  Block
 * @package   RequestsManager
 * @author    Marcin Zbiegień <m.zbiegien@uw.edu.pl>
 * @copyright 2023 UW
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @link      https://uw.edu.pl
 */

require_once '../../config.php';

$context = context_system::instance();

if (false === has_capability('block/cmanager:deleterecord', $context)) {
    print_error(get_string('cannotdelete', 'block_ckc_requests_manager'));
}

$deleteId = required_param('id', PARAM_INT);
$type     = optional_param('t', '', PARAM_TEXT);

// Delete the record.
$GLOBALS['DB']->delete_records(
    'block_ckc_requests_manager_records',
    ['id' => $deleteId]
);

// Delete associated comments.
$result = $GLOBALS['DB']->delete_records(
    'block_ckc_requests_manager_comments',
    ['instanceid' => $deleteId]
);

if ($result === true) {
    $event = \block_ckc_requests_manager\event\course_deleted::create(
        [
            'objectid' => '',
            'other'    => get_string(
                'courserecorddeleted',
                'block_ckc_requests_manager'
            ).'ID:'.$deleteId,
            'context'  => $context,
        ]
    );
    $event->trigger();
}

// Redirect the browser back when finished deleting.
if ($type === 'a') {
    echo "<script>window.location='cmanager_admin.php';</script>";
} else if ($type === 'adminarch') {
    echo "<script>window.location='cmanager_admin_arch.php';</script>";
} else {
    echo "<script>window.location='module_manager.php';</script>";
}
