<?php
/**
 * @package     Sven.Bluege
 * @subpackage  com_eventgallery
 *
 * @copyright   Copyright (C) 2005 - 2019 Sven Bluege All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;


class AnchorControllerGoex extends JControllerForm
{

    protected $default_view = 'goex';



	public function cancel($key = null) {
		$this->setRedirect( 'index.php?option=com_anchor');
	}

    /**
     * Imports data into the database based on an uploaded zip file.
     */
    public function import() {
        JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

       var_dump("泥嚎");
    }

    /**
     * Exports the event gallery database content
     */
   
    /**
     * determines all Event Gallery tables and returns their names in the format #__eventgallery_[name}
     *
     * @return array
     */

}
