<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_redirect
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('formbehavior.chosen', 'select');

$user      = JFactory::getUser();
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
?>

<form action="<?php echo JRoute::_('index.php?option=com_anchor&view=links'); ?>" method="post" name="adminForm" id="adminForm">
	<div id="j-main-container">
		<?php echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>
		<?php if ($this->redirectPluginId) : ?>
			<?php $link = JRoute::_('index.php?option=com_plugins&client_id=0&task=plugin.edit&extension_id=' . $this->redirectPluginId . '&tmpl=component&layout=modal'); ?>
			<?php echo JHtml::_(
				'bootstrap.renderModal',
				'plugin' . $this->redirectPluginId . 'Modal',
				array(
					'url'         => $link,
					'title'       => JText::_('COM_REDIRECT1_EDIT_PLUGIN_SETTINGS'),
					'height'      => '400px',
					'width'       => '800px',
					'bodyHeight'  => '70',
					'modalWidth'  => '80',
					'closeButton' => false,
					'backdrop'    => 'static',
					'keyboard'    => false,
					'footer'      => '<button type="button" class="btn" data-dismiss="modal"'
						. ' onclick="jQuery(\'#plugin' . $this->redirectPluginId . 'Modal iframe\').contents().find(\'#closeBtn\').click();">'
						. JText::_('JLIB_HTML_BEHAVIOR_CLOSE') . '</button>'
						. '<button type="button" class="btn btn-primary" data-dismiss="modal" onclick="jQuery(\'#plugin' . $this->redirectPluginId . 'Modal iframe\').contents().find(\'#saveBtn\').click();">'
						. JText::_("JSAVE") . '</button>'
						. '<button type="button" class="btn btn-success" onclick="jQuery(\'#plugin' . $this->redirectPluginId . 'Modal iframe\').contents().find(\'#applyBtn\').click(); return false;">'
						. JText::_("JAPPLY") . '</button>'
				)
			); ?>
		<?php endif; ?>

		<?php if (empty($this->items)) : ?>
		<div class="alert alert-no-items">
			<?php echo JText::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
		</div>
		<?php else : ?>
			<table class="table table-striped">
				<thead>
					<tr>
						<th  class="center nowrap">
							<?php echo JHtml::_('grid.checkall'); ?>
						</th>
						<th  class="center nowrap">
							<?php echo JHtml::_('searchtools.sort', 'JSTATUS', 'a.published', $listDirn, $listOrder); ?>
						</th>
						<th class="nowrap title">
							<?php echo JHtml::_('searchtools.sort', '当前链接', 'a.inner_url', $listDirn, $listOrder); ?>
						</th>
                        <th class="nowrap hidden-phone">
                            <?php echo JHtml::_('searchtools.sort', '关键词', 'a.keyword', $listDirn, $listOrder); ?>
                        </th>
                        <th  class="nowrap hidden-phone">
                            <?php echo JHtml::_('searchtools.sort', '替换关键词', 'a.new_keyword', $listDirn, $listOrder); ?>
                        </th>

						<th  class="nowrap">
							<?php echo JHtml::_('searchtools.sort', '目标链接', 'a.target_url', $listDirn, $listOrder); ?>
						</th>
						<th class="nowrap hidden-phone hidden-tablet">
							<?php echo JHtml::_('searchtools.sort', '备注', 'a.remark', $listDirn, $listOrder); ?>
						</th>
                        <th class="nowrap hidden-phone hidden-tablet">
                            <?php echo JHtml::_('searchtools.sort', '匹配状态', 'a.match_state', $listDirn, $listOrder); ?>
                        </th>
						<th  class="nowrap hidden-phone hidden-tablet">
							<?php echo JHtml::_('searchtools.sort', '创建时间', 'a.created_date', $listDirn, $listOrder); ?>
						</th>
                        <th  class="nowrap hidden-phone hidden-tablet">
                            <?php echo JHtml::_('searchtools.sort', '修改时间', 'a.modified_date', $listDirn, $listOrder); ?>
                        </th>

						<th  class="nowrap hidden-phone">
							<?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.anchor_id', $listDirn, $listOrder); ?>
						</th>
					</tr>
				</thead>
				<tfoot>
					<tr>
						<td colspan="10">
							<?php echo $this->pagination->getListFooter(); ?>
						</td>
					</tr>
				</tfoot>
				<tbody>
				<?php foreach ($this->items as $i => $item) :
					$canEdit   = true;
					$canChange = true;
					?>
					<tr class="row<?php echo $i % 2; ?>">
						<td class="center">
							<?php echo JHtml::_('grid.id', $i, $item->anchor_id); ?>
						</td>
						<td class="center">
							<div class="btn-group">
								<?php echo JHtml::_('anchor.published', $item->published, $i); ?>
								<?php // Create dropdown items and render the dropdown list.
								if ($canChange)
								{
									JHtml::_('actionsdropdown.' . ((int) $item->published === 2 ? 'un' : '') . 'archive', 'cb' . $i, 'links');
									JHtml::_('actionsdropdown.' . ((int) $item->published === -2 ? 'un' : '') . 'trash', 'cb' . $i, 'links');
									echo JHtml::_('actionsdropdown.render', $this->escape($item->inner_url));
								}
								?>
							</div>
						</td>
						<td class="break-word">
                            <a href="<?php echo JRoute::_('index.php?option=com_anchor&task=link.edit&anchor_id=' . $item->anchor_id); ?>" title="<?php echo $this->escape($item->inner_url); ?>">
                                <?php echo $this->escape(str_replace(JUri::root(), '', rawurldecode($item->inner_url))); ?></a>

                            <a href="<?php echo $this->escape($item->inner_url); ?>" target="_blank" ><span class="icon-out-2 small"></span></a>
						</td>
                        <td class="hidden-phone">
                            <?php echo  $item->keyword; ?>
                        </td>
                        <td class="hidden-phone">
                            <?php echo $item->new_keyword; ?>
                        </td>
						<td class="small break-word">
							<?php echo $this->escape(rawurldecode($item->target_url)); ?>
						</td>
						<td class="small break-word hidden-phone hidden-tablet">
							<?php echo $this->escape($item->remark); ?>
						</td>
                        <td class="small break-word hidden-phone hidden-tablet">
                            <?php
                                if($item->match_state==1){
                                    echo '匹配成功';
                                }elseif($item->match_state==2){
                                    echo '匹配失败';
                                }else{
                                    echo '未匹配';
                                }
                            ?>
                        </td>
						<td class="small hidden-phone hidden-tablet">
							<?php echo $item->created_date; ?>
						</td>
                        <td class="small hidden-phone hidden-tablet">
                            <?php echo $item->modified_date; ?>
                        </td>

						<td class="hidden-phone">
							<?php echo (int) $item->anchor_id; ?>
						</td>
					</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>

		<?php if (!empty($this->items)) : ?>
			<?php echo $this->loadTemplate('addform'); ?>
		<?php endif; ?>
		<?php // Load the batch processing form if user is allowed ?>

				<?php echo JHtml::_(
					'bootstrap.renderModal',
					'collapseModal',
					array(
						'title'  => JText::_('COM_REDIRECT1_BATCH_OPTIONS'),
						'footer' => $this->loadTemplate('batch_footer'),
					),
					$this->loadTemplate('batch_body')
				); ?>


		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>

