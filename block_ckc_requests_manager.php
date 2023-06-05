<?php

/**
 * Contains block_ckc_requests_manager
 *
 * @category  Block
 * @package   RequestsManager
 * @author    Marcin Zbiegień <m.zbiegien@uw.edu.pl>
 * @copyright 2023 UW
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @link      https://uw.edu.pl
 */

 /**
  * A block which displays the Course Request Manager
  */
class block_ckc_requests_manager extends block_list
{


    /**
     * Initialize the block.
     *
     * @return void
     */
    public function init()
    {
        $this->title = get_string('plugindesc', 'block_ckc_requests_manager');

    }//end init()


    /**
     * Get the content displayed in the block.
     *
     * @return object Contains the content and footer for the block.
     */
    public function get_content()
    {
        if ($this->content !== null) {
            return $this->content;
        }

        $this->content         = new \stdClass;
        $this->content->items  = [];
        $this->content->icons  = [];
        $this->content->footer = '';

        if (isloggedin() === true && false === isguestuser()) {
            // Show the block if logged-in.
            $context  = context_system::instance();
            $requests = $GLOBALS['DB']->count_records(
                'block_ckc_requests_manager_records',
                ['status' => 'PENDING']
            );

            // For regular users.
            // Make a request.
            $this->content->items[] = $this->builditem(
                'block_request',
                'course_request.php',
                ['mode' => '1'],
                'makereq.png'
            );
            // Manage your requests.
            $this->content->items[] = $this->builditem(
                'block_manage',
                'module_manager.php',
                [],
                'man_req.png'
            );
            // My archived requests.
            $this->content->items[] = $this->builditem(
                'myarchivedrequests',
                'module_manager_history.php',
                [],
                'arch_req.png'
            );

            // For administrators.
            if (has_capability('block/cmanager:approverecord', $context)) {
                // Request queue.
                $this->content->items[] = $this->builditem(
                    'block_admin',
                    'cmanager_admin.php',
                    [],
                    'queue.png',
                    "[$requests]"
                );
                // Configuration.
                $this->content->items[] = $this->builditem(
                    'block_config',
                    'cmanager_confighome.php',
                    [],
                    'config.png'
                );
                // All archived requests.
                $this->content->items[] = $this->builditem(
                    'allarchivedrequests',
                    'cmanager_admin_arch.php',
                    [],
                    'all_arch.png'
                );
            }//end if
        }//end if

        return $this->content;

    }//end get_content()


    /**
     * Allow the block to be placed on any page.
     *
     * @return void
     */
    public function applicable_formats()
    {
        return [
            'admin'       => true,
            'site-index'  => false,
            'course-view' => true,
            'mod'         => false,
            'my'          => false,
        ];

    }//end applicable_formats()


    /**
     * Disable ability to add multiple instances to a page.
     *
     * @return boolean false
     */
    public function instance_allow_multiple()
    {
        return false;

    }//end instance_allow_multiple()


    /**
     * Enable plugin settings.php.
     *
     * @return boolean true
     */
    public function has_config()
    {
        return true;

    }//end has_config()


    /**
     * Enable block instance's settings.
     *
     * @return boolean true
     */
    public function instance_allow_config()
    {
        return false;

    }//end instance_allow_config()


    public function builditem($identifier, $url, $query=[], $icon='', $identifierparam='')
    {
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
