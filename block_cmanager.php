<?php
// This file is part of Course Request Manager for Moodle - http://moodle.org/
//
// Course Request Manager is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Course Request Manager is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Contains block_ckc_requests_manager
 *
 * @package   block_ckc_requests_manager
 * @copyright 2012-2018 Kyle Goslin, Daniel McSweeney (Institute of Technology Blanchardstown)
 * @copyright 2021-2022 TNG Consulting Inc.
 * @author    Kyle Goslin, Daniel McSweeney
 * @author    Michael Milette
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

 /**
  * A block which displays the Course Request Manager
  *
  * @package   block_ckc_requests_manager
  * @copyright 2022 TNG Consulting Inc.
  * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
  */
class block_ckc_requests_manager extends block_list
{


    /**
     * Initialize the block.
     *
     * @return void
     */
    function init()
    {
        $this->title = get_string('plugindesc', 'block_ckc_requests_manager');

    }//end init()


    /**
     * Get the content displayed in the block.
     *
     * @return object Contains the content and footer for the block.
     */
    function get_content()
    {
        if ($this->content !== null) {
            return $this->content;
        }

        $this->content         = new stdClass;
        $this->content->items  = [];
        $this->content->icons  = [];
        $this->content->footer = '';

        if (isloggedin() and !isguestuser()) {
            // Show the block if logged-in.
            global $GLOBALS['DB'], $GLOBALS['CFG'];
            $context  = context_system::instance();
            $requests = $GLOBALS['DB']->count_records('block_ckc_requests_manager_records', ['status' => 'PENDING']);

            // For regular users.
            // Make a request.
            $this->content->items[] = $this->builditem('block_request', 'course_request.php', ['mode' => '1'], 'makereq.png');
            // Manage your requests.
            $this->content->items[] = $this->builditem('block_manage', 'module_manager.php', [], 'man_req.png');
            // My archived requests.
            $this->content->items[] = $this->builditem('myarchivedrequests', 'module_manager_history.php', [], 'arch_req.png');

            // For administrators.
            if (has_capability('block/cmanager:approverecord', $context)) {
                // Request queue.
                $this->content->items[] = $this->builditem('block_admin', 'cmanager_admin.php', [], 'queue.png', "[$requests]");
                // Configuration.
                $this->content->items[] = $this->builditem('block_config', 'cmanager_confighome.php', [], 'config.png');
                // All archived requests.
                $this->content->items[] = $this->builditem('allarchivedrequests', 'cmanager_admin_arch.php', [], 'all_arch.png');
            }
        }//end if

        return $this->content;

    }//end get_content()


    /**
     * Allow the block to be placed on any page.
     *
     * @return void
     */
    function applicable_formats()
    {
        return ['all' => true];

    }//end applicable_formats()


    /**
     * Disable ability to add multiple instances to a page.
     *
     * @return boolean false
     */
    function instance_allow_multiple()
    {
        return false;

    }//end instance_allow_multiple()


    /**
     * Enable plugin settings.php.
     *
     * @return boolean true
     */
    function has_config()
    {
        return true;

    }//end has_config()


    /**
     * Enable block instance's settings.
     *
     * @return boolean true
     */
    function instance_allow_config()
    {
        return true;

    }//end instance_allow_config()


    function builditem($identifier, $url, $query=[], $icon='', $identifierparam='')
    {
        global $GLOBALS['CFG'];

        $string = get_string($identifier, 'block_ckc_requests_manager').rtrim(' '.$identifierparam);
        $icon   = html_writer::empty_tag(
            'img',
            [
                'src'   => $GLOBALS['CFG']->wwwroot.'/blocks/ckc_requests_manager/icons/'.$icon,
                'alt'   => '',
                'class' => 'icon',
            ]
        );

        return html_writer::link(new moodle_url('/blocks/ckc_requests_manager/'.$url, $query), $icon.$string);

    }//end builditem()


}//end class

 // End class.
