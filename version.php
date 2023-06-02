<?php

/**
 * Version information for Course Request Manager
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

$plugin->version = 2023060100;
// The current module version (Date: YYYYMMDDXX).
$plugin->requires = 20230526;
// Requires Moodle 4.1.
$plugin->component = 'block_ckc_requests_manager';
