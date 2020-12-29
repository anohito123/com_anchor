<?php

/**
 * @package     Sven.Bluege
 * @subpackage  com_eventgallery
 *
 * @copyright   Copyright (C) 2005 - 2019 Sven Bluege All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

?>
<div style="margin: 0 auto; margin-top:20px; width: 1100px; text-align: center;">
<h1>
    <?php echo JText::_('锚文本关键词统计'); ?>
</h1>


<form action="<?php echo JRoute::_('index.php?option=com_anchor&view=links&tmpl=component'); ?>" method="post" name="adminFormImport" id="adminFormImport" enctype="multipart/form-data">

	<input type="hidden" name="option" value="com_anchor" />
	<input type="hidden" name="task" value="links.search_keys" />

	<fieldset>  

		<div class="control-group">
			<div class="controls">
				<input type="text" name="keys" id="keys" value="<?php if($this->data) echo  $this->data[1] ?>">
				<button type="submit" name="task" value="links.search_keys" style="margin-top:-8px"><?php echo JText::_( '搜索' ); ?></button>
			</div>
		</div>

	</fieldset>
    <?php  if($this->data) echo $this->data[2]; ?>
	<?php echo JHtml::_('form.token'); ?>
</form>

        <div style="position: absolute;left: 40%;text-align:left;padding-bottom:80px">
            <?php if($this->data) echo $this->data[0]; ?>
        </div>



</div>

