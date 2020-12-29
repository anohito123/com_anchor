<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_redirect
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * View to edit a redirect link.
 *
 * @since  1.6
 */
class AnchorViewLink extends JViewLegacy
{
	protected $item;

	protected $form;

	protected $state;

	/**
	 * Display the view.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  False if unsuccessful, otherwise void.
	 *
	 * @since   1.6
	 */
	public function display($tpl = null)
	{
		$this->form  = $this->get('Form');
		$this->item  = $this->get('Item');
		$this->state = $this->get('State');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode("\n", $errors), 500);
		}

		$this->addToolbar();
		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	protected function addToolbar()
	{
		JFactory::getApplication()->input->set('hidemainmenu', true);

		$isNew = ($this->item->anchor_id == 0);
		//$canDo = JHelperContent::getActions('com_redirect1');

		JToolbarHelper::title($isNew ? JText::_('新建') : JText::_('编辑'), 'refresh anchor');

		// If not checked out, can save the item.

			JToolbarHelper::apply('link.apply');
			JToolbarHelper::save('link.save');


		/**
		 * This component does not support Save as Copy due to uniqueness checks.
		 * While it can be done, it causes too much confusion if the user does
		 * not change the Old URL.
		 */
		if (1)
		{
			JToolbarHelper::save2new('link.save2new');
		}

		if (empty($this->item->anchor_id))
		{
			JToolbarHelper::cancel('link.cancel');
		}
		else
		{
			JToolbarHelper::cancel('link.cancel', 'JTOOLBAR_CLOSE');
		}

	}
}
