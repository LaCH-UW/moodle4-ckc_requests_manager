<?php

/**
 * Settings for Course Request Manager.
 *
 * @category  Block
 * @package   RequestsManager
 * @author    Marcin Zbiegień <m.zbiegien@uw.edu.pl>
 * @copyright 2023 UW
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @link      https://uw.edu.pl
 */

if (false === defined('MOODLE_INTERNAL')) {
    die();
}

if (false === empty($ADMIN->fulltree)) {
    $noControlCheckbox = new admin_setting_configcheckbox(
        'block_ckc_requests_manager/norequestcontrol',
        get_string('norequestcontrol', 'block_ckc_requests_manager'),
        get_string('norequestcontrol_desc', 'block_ckc_requests_manager'),
        0
        // Default.
    );
    $settings->add($noControlCheckbox);
}
