<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_redirect
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Include the HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

JHtml::_('behavior.formvalidator');
JHtml::_('behavior.keepalive');
JHtml::_('formbehavior.chosen', 'select');

JFactory::getDocument()->addScriptDeclaration("
	Joomla.submitbutton = function(task)
	{
		if (task == 'link.cancel' || document.formvalidator.isValid(document.getElementById('link-form')))
		{
			Joomla.submitform(task, document.getElementById('link-form'));
		}
	};
");
?>

<form action="<?php echo JRoute::_('index.php?option=com_anchor&anchor_id=' . (int) $this->item->anchor_id); ?>" method="post" name="adminForm" id="link-form" class="form-validate form-horizontal">
	<fieldset>
		<?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'basic')); ?>

			<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'basic', empty($this->item->anchor_id) ? JText::_('新建') : JText::sprintf('编辑', $this->item->anchor_id)); ?>
				<?php echo $this->form->renderField('inner_url'); ?>
                <?php echo $this->form->renderField('keyword'); ?>
                <?php echo $this->form->renderField('new_keyword'); ?>
                <?php echo $this->form->renderField('target_url'); ?>
				<?php echo $this->form->renderField('published'); ?>
				<?php echo $this->form->renderField('remark'); ?>
				<?php echo $this->form->renderField('id'); ?>
				<?php echo $this->form->renderField('created_date'); ?>
				<?php echo $this->form->renderField('modified_date'); ?>
				<?php if (JComponentHelper::getParams('com_anchor')->get('mode')) : ?>
					<?php echo $this->form->renderFieldset('advanced'); ?>
				<?php endif; ?>
			<?php echo JHtml::_('bootstrap.endTab'); ?>
		<?php echo JHtml::_('bootstrap.endTabSet'); ?>

		<input type="hidden" name="task" value="" />
		<?php echo JHtml::_('form.token'); ?>
	</fieldset>
</form>
