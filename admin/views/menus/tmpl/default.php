<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_advancedmenus
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$user = JFactory::getUser();
$uri = JUri::getInstance();
$return = base64_encode($uri);
?>

<form action="<?php echo JRoute::_('index.php?option=com_advancedmenus&view=menus');?>" method="post" name="adminForm" id="adminForm">
	<table class="table table-striped">
		<thead>
			<tr>
				<th width="1%">
					<input type="checkbox" name="checkall-toggle" value="" title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)" />
				</th>
				<th>
					<?php echo JHtml::_('grid.sort', 'JGLOBAL_TITLE', 'a.title', $this->lists->order_Dir, $this->lists->order); ?>
				</th>
				<th width="10%" class="nowrap center hidden-phone">
					<?php echo JText::_('COM_ADVANCEDMENUS_HEADING_PUBLISHED_ITEMS'); ?>
				</th>
				<th width="10%" class="nowrap center hidden-phone">
					<?php echo JText::_('COM_ADVANCEDMENUS_HEADING_UNPUBLISHED_ITEMS'); ?>
				</th>
				<th width="10%" class="nowrap center hidden-phone">
					<?php echo JText::_('COM_ADVANCEDMENUS_HEADING_TRASHED_ITEMS'); ?>
				</th>
				<th width="20%" class="nowrap hidden-phone">
					<?php echo JText::_('COM_ADVANCEDMENUS_HEADING_LINKED_MODULES'); ?>
				</th>
				<th width="1%" class="center nowrap">
					<?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ID', 'a.id', $this->lists->order_Dir, $this->lists->order); ?>
				</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="15">
					<?php echo $this->pagination->getListFooter(); ?>
				</td>
			</tr>
		</tfoot>
		<tbody>
		<?php foreach ($this->items as $i => $item) :
			$canCreate = $user->authorise('core.create',     'com_advancedmenus');
			$canEdit   = $user->authorise('core.edit',       'com_advancedmenus');
			$canChange = $user->authorise('core.edit.state', 'com_advancedmenus');
		?>
			<tr class="row<?php echo $i % 2; ?>">
				<td class="center">
					<?php echo JHtml::_('grid.id', $i, $item->id); ?>
				</td>
				<td>
					<a href="<?php echo JRoute::_('index.php?option=com_advancedmenus&view=items&menutype='.$item->menutype) ?> ">
						<?php echo $this->escape($item->title); ?>123</a>
					<p class="small">(<span><?php echo JText::_('COM_ADVANCEDMENUS_MENU_MENUTYPE_LABEL') ?></span>
						<?php if ($canEdit) : ?>
							<?php echo '<a href="'.JRoute::_('index.php?option=com_advancedmenus&task=menu.edit&id='.$item->id).' title='.$this->escape($item->description).'">'.
							$this->escape($item->menutype).'</a>'; ?>)
						<?php else : ?>
							<?php echo $this->escape($item->menutype)?>)
						<?php endif; ?>
					</p>
				</td>
				<td class="center btns">
					<a class="badge badge-success" href="<?php echo JRoute::_('index.php?option=com_advancedmenus&view=items&menutype='.$item->menutype.'&filter_published=1');?>">
						<?php echo $item->count_published; ?></a>
				</td>
				<td class="center btns">
					<a class="badge" href="<?php echo JRoute::_('index.php?option=com_advancedmenus&view=items&menutype='.$item->menutype.'&filter_published=0');?>">
						<?php echo $item->count_unpublished; ?></a>
				</td>
				<td class="center btns">
					<a class="badge badge-error" href="<?php echo JRoute::_('index.php?option=com_advancedmenus&view=items&menutype='.$item->menutype.'&filter_published=-2');?>">
						<?php echo $item->count_trashed; ?></a>
				</td>
				<td class="left">
					<?php if (isset($this->modules[$item->menutype])) : ?>
						<div class="btn-group">
							<a href="#" class="btn btn-small dropdown-toggle" data-toggle="dropdown">
								<?php echo JText::_('COM_ADVANCEDMENUS_MODULES') ?>
								<b class="caret"></b>
							</a>
							<ul class="dropdown-menu">
								<?php foreach ($this->modules[$item->menutype] as &$module) : ?>
									<li>
										<?php if ($canEdit) : ?>
											<a class="small modal" href="<?php echo JRoute::_('index.php?option=com_modules&task=module.edit&id='.$module->id.'&return='.$return.'&tmpl=component&layout=modal');?>" rel="{handler: 'iframe', size: {x: 1024, y: 450}, onClose: function() {window.location.reload()}}" title="<?php echo JText::_('COM_ADVANCEDMENUS_EDIT_MODULE_SETTINGS');?>">
											<?php echo JText::sprintf('COM_ADVANCEDMENUS_MODULE_ACCESS_POSITION', $this->escape($module->title), $this->escape($module->access_title), $this->escape($module->position)); ?></a>
										<?php else :?>
											<?php echo JText::sprintf('COM_ADVANCEDMENUS_MODULE_ACCESS_POSITION', $this->escape($module->title), $this->escape($module->access_title), $this->escape($module->position)); ?>
										<?php endif; ?>
									</li>
								<?php endforeach; ?>
							</ul>
						 </div>
					<?php elseif ($this->modMenuId) : ?>
					<a href="<?php echo JRoute::_('index.php?option=com_modules&task=module.add&eid=' . $this->modMenuId . '&params[menutype]='.$item->menutype); ?>">
						<?php echo JText::_('COM_ADVANCEDMENUS_ADD_MENU_MODULE'); ?></a>
					<?php endif; ?>
				</td>
				<td class="center">
					<?php echo $item->id; ?>
				</td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>

	<input type="hidden" name="option" value="com_advancedmenus" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
	<?php echo JHtml::_('form.token'); ?>
</form>
