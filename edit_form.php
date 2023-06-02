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
 * Class.
 */
class block_ckc_requests_manager_edit_form extends block_edit_form
{


    /**
     * Set form elements specific to the given object.
     *
     * @param object $mForm Form object.
     *
     * @return void
     *
     * @throws coding_exception On errors.
     */
    protected function specific_definition($mForm)
    {
        // Section header title according to language file.
        $mForm->addElement(
            'header',
            'configheader',
            get_string('blocksettings', 'block')
        );
        $mForm->addElement(
            'html',
            '<a href="'.$GLOBALS['CFG']->wwwroot.'/blocks/ckc_requests_manager/cmanager_confighome.php"> '.get_string('configurecoursemanagersettings', 'block_ckc_requests_manager').'<a/>'
        );

        $mForm->setDefault('config_text', 'default value');
        $mForm->setType('config_text', PARAM_MULTILANG);

    }//end specific_definition()


}//end class
