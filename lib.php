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


/**
 * Return HTML displaying the names of lecturers linked to email addresses.
 *
 * @param integer $courseId Course id.
 *
 * @return string HTML.
 */
function block_ckc_requests_manager_get_lecturers($courseId)
{
    $course = $GLOBALS['DB']->get_record('course', ['id' => $courseId]);

    if (empty($course) === true) {
        echo get_string('lib_error_invalid_c', 'block_ckc_requests_manager');
        // die();
        return '';
    }

    $contextId = $GLOBALS['DB']->get_field(
        'context',
        'id',
        [
            'instanceid'   => $courseId,
            'contextlevel' => 50,
        ],
        IGNORE_MULTIPLE
    );
    $userIds   = $GLOBALS['DB']->get_records(
        'role_assignments',
        [
            'roleid'    => '3',
            'contextid' => $contextId,
        ]
    );

    $lecturerHtml = '';

    foreach ($userIds as $singleUser) {
        $GLOBALS['USER'] = $GLOBALS['DB']->get_record(
            'user',
            ['id' => $singleUser->userid],
            '*',
            IGNORE_MULTIPLE
        );
        $lecturerHtml   .= '<i class="fa fa-envelope-o" aria-hidden="true"></i>';
        $lecturerHtml   .= '<a href="mailto:';
        $lecturerHtml   .= $GLOBALS['USER']->email;
        $lecturerHtml   .= '">';
        $lecturerHtml   .= $GLOBALS['USER']->firstname;
        $lecturerHtml   .= ' ';
        $lecturerHtml   .= $GLOBALS['USER']->lastname;
        $lecturerHtml   .= '</a><br>';
    }

    return $lecturerHtml;

}//end block_ckc_requests_manager_get_lecturers()


/**
 * Get a collection of teacher ids (role 3) for a specific course, separated by spaces.
 *
 * @param integer $courseId Course id.
 *
 * @return string HTML.
 */
function block_ckc_requests_manager_get_lecturer_ids_space_sep($courseId)
{
    $course = $GLOBALS['DB']->get_record('course', ['id' => $courseId]);

    if (empty($course) === true) {
        echo get_string('lib_error_invalid_c', 'block_ckc_requests_manager');
        // die();
        return '';
    }

    $contextId = $GLOBALS['DB']->get_field(
        'context',
        'id',
        [
            'instanceid'   => $courseId,
            'contextlevel' => 50,
        ],
        IGNORE_MULTIPLE
    );
    $userIds   = $GLOBALS['DB']->get_records(
        'role_assignments',
        [
            'roleid'    => '3',
            'contextid' => $contextId,
        ]
    );

    $lecturers = [];

    foreach ($userIds as $singleuser) {
        $userRecord  = $GLOBALS['DB']->get_record(
            'user',
            ['id' => $singleuser->userid],
            '*',
            IGNORE_MULTIPLE
        );
        $lecturers[] = $userRecord->id;
    }

    return join(' ', $lecturers);

}//end block_ckc_requests_manager_get_lecturer_ids_space_sep()
