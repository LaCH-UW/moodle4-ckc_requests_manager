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

require_login();

$admins = get_admins();

if (false === empty($admins)) {
    $loginIsValid = false;

    foreach ($admins as $admin) {
        if (intval($admin->id) === intval($GLOBALS['USER']->id)) {
            $loginIsValid = true;
        }
    }

    if ($loginIsValid !== true) {
        echo "<script>window.location = '".$GLOBALS['CFG']->wwwroot."';</script>";
        die;
    }
}
