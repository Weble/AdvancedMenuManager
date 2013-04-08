<?php
defined('_JEXEC') or die;

$user		= JFactory::getUser();
$app		= JFactory::getApplication();
$assoc 		= isset($app->item_associations) ? $app->item_associations : 0;

?>
<?php //Set up the filter bar. ?>
<form action="<?php echo JRoute::_('index.php?option=com_advancedmenus&view=items');?>" method="post" name="adminForm" id="adminForm">
	<table class="table table-striped" id="itemList">
		<thead>
			<tr>
				<th width="1%" class="hidden-phone">
					<?php echo JHtml::_('grid.sort', '<i class="icon-menu-2"></i>', 'a.ordering', $this->lists->order_Dir, $this->lists->order, null, 'asc', 'JGRID_HEADING_ORDERING'); ?>
				</th>
				<th width="1%" class="hidden-phone">
					<input type="checkbox" name="checkall-toggle" value="" title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)" />
				</th>
				<th width="1%" class="nowrap center">
					<?php echo JHtml::_('grid.sort', 'JSTATUS', 'a.published', $this->lists->order_Dir, $this->lists->order); ?>
				</th>
				<th class="title">
					<?php echo JHtml::_('grid.sort', 'JGLOBAL_TITLE', 'a.title', $this->lists->order_Dir, $this->lists->order); ?>
				</th>
				<th width="5%" class="nowrap hidden-phone">
					<?php echo JHtml::_('grid.sort', 'COM_ADVANCEDMENUS_HEADING_HOME', 'a.home', $this->lists->order_Dir, $this->lists->order); ?>
				</th>
			<th width="10%" class="nowrap hidden-phone">
				<?php echo JHtml::_('grid.sort',  'JGRID_HEADING_ACCESS', 'a.access', $this->lists->order_Dir, $this->lists->order); ?>
			</th>
			<?php if ($assoc) : ?>
			<th width="5%" class="nowrap hidden-phone">
				<?php echo JHtml::_('grid.sort', 'COM_ADVANCEDMENUS_HEADING_ASSOCIATION', 'association', $this->lists->order_Dir, $this->lists->order); ?>
			</th>
			<?php endif; ?>
			<th width="5%" class="nowrap hidden-phone">
				<?php echo JHtml::_('grid.sort', 'JGRID_HEADING_LANGUAGE', 'language', $this->lists->order_Dir, $this->lists->order); ?>
			</th>
			<th width="1%" class="nowrap hidden-phone">
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
		<?php
		$originalOrders = array();
		foreach ($this->items as $i => $item) :
			$canCreate  = $user->authorise('core.create',     'com_advancedmenus');
			$canEdit    = $user->authorise('core.edit',       'com_advancedmenus');
			$canCheckin = $user->authorise('core.manage',     'com_checkin') || $item->checked_out == $user->get('id')|| $item->checked_out == 0;
			$canChange  = $user->authorise('core.edit.state', 'com_advancedmenus') && $canCheckin;
			
			// Get the parents of item for sorting
			if ($item->level > 1)
			{
				$parentsStr = "";
				$_currentParentId = $item->parent_id;
				$parentsStr = " ".$_currentParentId;
				for ($j = 0; $j < $item->level; $j++)
				{
					foreach ($this->ordering as $k => $v)
					{
						$v = implode("-", $v);
						$v = "-" . $v . "-";
						if (strpos($v, "-" . $_currentParentId . "-") !== false)
						{
							$parentsStr .= " " . $k;
							$_currentParentId = $k;
							break;
						}
					}
				}
			}
			else
			{
				$parentsStr = "";
			}
			?>
			<tr class="row<?php echo $i % 2; ?>" sortable-group-id="<?php echo $item->parent_id;?>" item-id="<?php echo $item->id?>" parents="<?php echo $parentsStr?>" level="<?php echo $item->level?>">
				<td class="order nowrap center hidden-phone">
				<?php if ($canChange) :
					$disableClassName = '';
					$disabledLabel	  = '';
					?>
					<span class="sortable-handler hasTooltip <?php echo $disableClassName?>" title="<?php echo $disabledLabel?>">
						<i class="icon-menu"></i>
					</span>
				<?php else : ?>
					<span class="sortable-handler inactive">
						<i class="icon-menu"></i>
					</span>
				<?php endif; ?>
				<input type="text" style="display:none" name="order[]" size="5" value="<?php echo $orderkey + 1;?>" />
				</td>
				<td class="center hidden-phone">
					<?php echo JHtml::_('grid.id', $i, $item->id); ?>
				</td>
				<td class="center">
					<?php echo JHtml::_('MenusHtml.Menus.state', $item->published, $i, $canChange, 'cb'); ?>
				</td>
				<td>
					<?php echo str_repeat('<span class="gi">|&mdash;</span>', $item->level - 1) ?>
					<?php if ($item->checked_out) : ?>
						<?php echo JHtml::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'items.', $canCheckin); ?>
					<?php endif; ?>
					<?php if ($canEdit) : ?>
						<a href="<?php echo JRoute::_('index.php?option=com_advancedmenus&task=item.edit&id='.(int) $item->id);?>">
							<?php echo $this->escape($item->title); ?></a>
					<?php else : ?>
						<?php echo $this->escape($item->title); ?>
					<?php endif; ?>
					<span class="small">
					<?php if ($item->type != 'url') : ?>
						<?php if (empty($item->note)) : ?>
							<?php echo JText::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($item->alias));?>
						<?php else : ?>
							<?php echo JText::sprintf('JGLOBAL_LIST_ALIAS_NOTE', $this->escape($item->alias), $this->escape($item->note));?>
						<?php endif; ?>
					<?php elseif ($item->type == 'url' && $item->note) : ?>
						<?php echo JText::sprintf('JGLOBAL_LIST_NOTE', $this->escape($item->note));?>
					<?php endif; ?>
					</span>
					<div class="small" title="<?php echo $this->escape($item->path);?>">
						<?php echo str_repeat('<span class="gtr">&mdash;</span>', $item->level - 1) ?>
						<span title="<?php echo isset($item->item_type_desc) ? htmlspecialchars($this->escape($item->item_type_desc), ENT_COMPAT, 'UTF-8') : ''; ?>">
							<?php echo $this->escape($item->item_type); ?></span>
					</div>
				</td>
				<td class="center hidden-phone">
					<?php if ($item->type == 'component') : ?>
						<?php if ($item->language == '*' || $item->home == '0'):?>
							<?php echo JHtml::_('jgrid.isdefault', $item->home, $i, 'items.', ($item->language != '*' || !$item->home) && $canChange);?>
						<?php elseif ($canChange):?>
							<a href="<?php echo JRoute::_('index.php?option=com_advancedmenus&task=items.unsetDefault&cid[]='.$item->id.'&'.JSession::getFormToken().'=1');?>">
								<?php echo JHtml::_('image', 'mod_languages/' . $item->image . '.gif', $item->language_title, array('title' => JText::sprintf('COM_ADVANCEDMENUS_GRID_UNSET_LANGUAGE', $item->language_title)), true);?>
							</a>
						<?php else:?>
							<?php echo JHtml::_('image', 'mod_languages/' . $item->image . '.gif', $item->language_title, array('title' => $item->language_title), true);?>
						<?php endif;?>
					<?php endif; ?>
				</td>
			<td class="small hidden-phone">
				<?php echo $this->escape($item->access_level); ?>
			</td>
			<?php if ($assoc) : ?>
			<td class="small hidden-phone">
			<?php if ($item->association):?>
				<?php echo JHtml::_('MenusHtml.Menus.association', $item->id);?>
			<?php endif;?>
			</td>
			<?php endif; ?>
			<td class="small hidden-phone">
				<?php if ($item->language == ''):?>
					<?php echo JText::_('JDEFAULT'); ?>
				<?php elseif ($item->language == '*'):?>
					<?php echo JText::alt('JALL', 'language'); ?>
				<?php else:?>
					<?php echo $item->language_title ? $this->escape($item->language_title) : JText::_('JUNDEFINED'); ?>
				<?php endif;?>
			</td>
			<td class="center hidden-phone">
				<span title="<?php echo sprintf('%d-%d', $item->lft, $item->rgt);?>">
					<?php echo (int) $item->id; ?></span>
			</td>
		</tr>
		<?php endforeach; ?>
		</tbody>
	</table>

	<input type="hidden" name="option" value="com_advancedmenus" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="filter_order" value="<?php echo $this->lists->order; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists->order_Dir; ?>" />
	<?php echo JHtml::_('form.token'); ?>
</form>
