<?php 
/**
 * @package     Sven.Bluege
 * @subpackage  com_eventgallery
 *
 * @copyright   Copyright (C) 2005 - 2019 Sven Bluege All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

/** @noinspection PhpUndefinedClassInspection */
class AnchorViewSearch extends JViewLegacy
{

    protected $folders;
    public $data;


    function display($tpl = null)
	{

        $this->assignRef( 'data', $this->data );


		parent::display($tpl);

	}


}

