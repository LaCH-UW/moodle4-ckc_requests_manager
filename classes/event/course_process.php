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
namespace block_ckc_requests_manager\event;
defined('MOODLE_INTERNAL') || die();


class course_process extends \core\event\base
{


    protected function init()
    {
        $this->data['crud'] = 'c';
        // c(reate), r(ead), u(pdate), d(elete)
        $this->data['edulevel']    = self::LEVEL_OTHER;
        $this->data['objecttable'] = '';

    }//end init()


    public static function get_name()
    {
        return get_string('requestprocessing', 'block_ckc_requests_manager');

    }//end get_name()


    public function get_description()
    {
        return "user {$this->userid} :".$this->other;

    }//end get_description()


    public function get_url()
    {
        return new \moodle_url('/blocks/ckc_requests_manager/cmanager_admin.php');

    }//end get_url()


    public function get_legacy_logdata()
    {

    }//end get_legacy_logdata()


    public static function get_legacy_eventname()
    {

    }//end get_legacy_eventname()


    protected function get_legacy_eventdata()
    {

    }//end get_legacy_eventdata()


}//end class
