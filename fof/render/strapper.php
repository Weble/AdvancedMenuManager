<?php
/**
 * @package    FrameworkOnFramework
 * @copyright  Copyright (C) 2010 - 2012 Akeeba Ltd. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

/**
 * Akeeba Strapper view renderer class.
 */
class FOFRenderStrapper extends FOFRenderAbstract
{

	/**
	 * Public constructor. Determines the priority of this class and if it should be enabled
	 */
	public function __construct()
	{
		$this->priority = 60;
		$this->enabled = class_exists('AkeebaStrapper');
	}

	/**
	 * Echoes any HTML to show before the view template
	 *
	 * @param   string  $view   The current view
	 * @param   string  $task   The current task
	 * @param   array   $input  The input array (request parameters)
	 */
	public function preRender($view, $task, $input, $config = array())
	{
		$format = $input->getCmd('format', 'html');
		if (empty($format))
			$format = 'html';
		if ($format != 'html')
			return;

		list($isCli, ) = FOFDispatcher::isCliAdmin();
		if(!$isCli)
		{
			// Wrap output in a Joomla-versioned div
			$version = new JVersion;
			$version = str_replace('.', '', $version->RELEASE);
			echo "<div class=\"joomla-version-$version\">\n";

			// Wrap output in an akeeba-bootstrap class div
			echo "<div class=\"akeeba-bootstrap\">\n";
		}
		$this->renderButtons($view, $task, $input, $config);
		$this->renderLinkbar($view, $task, $input, $config);

		if (!$isCli && version_compare(JVERSION, '3.0.0', 'ge'))
		{
			$sidebarEntries = JHtmlSidebar::getEntries();
			if (!empty($sidebarEntries))
			{
				$html = '<div id="j-sidebar-container" class="span2">' . "\n";
				$html .= "\t" . JHtmlSidebar::render() ."\n";
				$html .= "</div>\n";
				$html .= '<div id="j-main-container" class="span10">' . "\n";
				echo $html;
			}
		}
	}

	/**
	 * Echoes any HTML to show after the view template
	 *
	 * @param   string  $view   The current view
	 * @param   string  $task   The current task
	 * @param   array   $input  The input array (request parameters)
	 */
	public function postRender($view, $task, $input, $config = array())
	{
		list($isCli, ) = FOFDispatcher::isCliAdmin();
		$format = $input->getCmd('format', 'html');
		if ($format != 'html' || $isCli)
			return;

		if (!$isCli && version_compare(JVERSION, '3.0.0', 'ge'))
		{
			$sidebarEntries = JHtmlSidebar::getEntries();
			if (!empty($sidebarEntries))
			{
				echo '</div>';
			}
		}

		echo "</div>\n";
		echo "</div>\n";
	}

	/**
     * Loads the validation script for edit form
     *
     * @return void
     */
	protected function loadValidationScript(FOFForm &$form)
	{
		$message = $form->getView()->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED'));

		$js = <<<ENDJAVASCRIPT
		Joomla.submitbutton = function(task)
        {
            if (task == 'cancel' || document.formvalidator.isValid(document.id('adminForm'))) {
                Joomla.submitform(task, document.getElementById('adminForm'));
            } else {
                alert('$message');
            }
        }
ENDJAVASCRIPT;

		JFactory::getDocument()->addScriptDeclaration($js);
	}

	/**
	 * Renders the submenu (link bar)
	 *
	 * @param   string    $view    The active view name
	 * @param   string    $task    The current task
	 * @param   FOFInput  $input   The input object
	 * @param   array     $config  Extra configuration variables for the toolbar
	 */
	protected function renderLinkbar($view, $task, $input, $config = array())
	{
		$style = 'classic';

		if(array_key_exists('linkbar_style', $config))
		{
			$style = $config['linkbar_style'];
		}

		if (!version_compare(JVERSION, '3.0.0', 'ge'))
		{
			$style = 'classic';
		}

		switch ($style)
		{
			case 'joomla':
				$this->renderLinkbar_joomla($view, $task, $input);
				break;

			case 'classic':
			default:
				$this->renderLinkbar_classic($view, $task, $input);
				break;
		}
	}

	/**
	 * Renders the submenu (link bar)
	 *
	 * @param   string    $view    The active view name
	 * @param   string    $task    The current task
	 * @param   FOFInput  $input   The input object
	 * @param   array     $config  Extra configuration variables for the toolbar
	 */
	protected function renderLinkbar_classic($view, $task, $input, $config = array())
	{
		list($isCli, ) = FOFDispatcher::isCliAdmin();
		if($isCli)
		{
			return;
		}

		// Do not render a submenu unless we are in the the admin area
		$toolbar = FOFToolbar::getAnInstance($input->getCmd('option', 'com_foobar'), $config);
		$renderFrontendSubmenu = $toolbar->getRenderFrontendSubmenu();

		list($isCli, $isAdmin) = FOFDispatcher::isCliAdmin();
		if (!$isAdmin && !$renderFrontendSubmenu)
			return;

		$links = $toolbar->getLinks();
		if (!empty($links))
		{
			echo "<ul class=\"nav nav-tabs\">\n";
			foreach ($links as $link)
			{
				$dropdown = false;
				if (array_key_exists('dropdown', $link))
				{
					$dropdown = $link['dropdown'];
				}

				if ($dropdown)
				{
					echo "<li";
					$class = 'dropdown';
					if ($link['active'])
						$class .= ' active';
					echo ' class="' . $class . '">';

					echo '<a class="dropdown-toggle" data-toggle="dropdown" href="#">';
					if ($link['icon'])
					{
						echo "<i class=\"icon icon-" . $link['icon'] . "\"></i>";
					}
					echo $link['name'];
					echo '<b class="caret"></b>';
					echo '</a>';

					echo "\n<ul class=\"dropdown-menu\">";
					foreach ($link['items'] as $item)
					{

						echo "<li";
						if ($item['active'])
							echo ' class="active"';
						echo ">";
						if ($item['icon'])
						{
							echo "<i class=\"icon icon-" . $item['icon'] . "\"></i>";
						}
						if ($item['link'])
						{
							echo "<a tabindex=\"-1\" href=\"" . $item['link'] . "\">" . $item['name'] . "</a>";
						}
						else
						{
							echo $item['name'];
						}
						echo "</li>";
					}
					echo "</ul>\n";
				}
				else
				{
					echo "<li";
					if ($link['active'])
						echo ' class="active"';
					echo ">";
					if ($link['icon'])
					{
						echo "<i class=\"icon icon-" . $link['icon'] . "\"></i>";
					}
					if ($link['link'])
					{
						echo "<a href=\"" . $link['link'] . "\">" . $link['name'] . "</a>";
					}
					else
					{
						echo $link['name'];
					}
				}

				echo "</li>\n";
			}
			echo "</ul>\n";
		}
	}

	/**
	 * Renders the submenu (link bar) using Joomla!'s style
	 *
	 * @param   string    $view    The active view name
	 * @param   string    $task    The current task
	 * @param   FOFInput  $input   The input object
	 * @param   array     $config  Extra configuration variables for the toolbar
	 */
	protected function renderLinkbar_joomla($view, $task, $input, $config = array())
	{
		list($isCli, $isAdmin) = FOFDispatcher::isCliAdmin();

		// On command line don't do anything
		if($isCli)
		{
			return;
		}

		// Do not render a submenu unless we are in the the admin area
		$toolbar = FOFToolbar::getAnInstance($input->getCmd('option', 'com_foobar'), $config);
		$renderFrontendSubmenu = $toolbar->getRenderFrontendSubmenu();

		if (!$isAdmin && !$renderFrontendSubmenu)
			return;

		$links = $toolbar->getLinks();
		if (!empty($links))
		{
			foreach ($links as $link)
			{
				JHtmlSidebar::addEntry($link['name'], $link['link'], $link['active']);
			}
		}
	}

	/**
	 * Renders the toolbar buttons
	 *
	 * @param   string    $view    The active view name
	 * @param   string    $task    The current task
	 * @param   FOFInput  $input   The input object
	 * @param   array     $config  Extra configuration variables for the toolbar
	 */
	protected function renderButtons($view, $task, $input, $config = array())
	{
		list($isCli, ) = FOFDispatcher::isCliAdmin();
		if($isCli)
		{
			return;
		}
		// Do not render buttons unless we are in the the frontend area and we are asked to do so
		$toolbar = FOFToolbar::getAnInstance($input->getCmd('option', 'com_foobar'), $config);
		$renderFrontendButtons = $toolbar->getRenderFrontendButtons();

		list($isCli, $isAdmin) = FOFDispatcher::isCliAdmin();
		if ($isAdmin || !$renderFrontendButtons)
			return;

		$bar = JToolBar::getInstance('toolbar');
		$items = $bar->getItems();

		$substitutions = array(
			'icon-32-new'		 => 'icon-plus',
			'icon-32-edit'		 => 'icon-pencil',
			'icon-32-publish'	 => 'icon-eye-open',
			'icon-32-unpublish'	 => 'icon-eye-close',
			'icon-32-delete'	 => 'icon-trash',
			'icon-32-edit'		 => 'icon-edit',
			'icon-32-copy'		 => 'icon-th-large',
			'icon-32-cancel'	 => 'icon-remove',
			'icon-32-back'		 => 'icon-circle-arrow-left',
			'icon-32-apply'		 => 'icon-ok',
			'icon-32-save'		 => 'icon-hdd',
			'icon-32-save-new'	 => 'icon-repeat',
		);

		$html = array();
		$html[] = '<div class="well" id="' . $bar->getName() . '">';
		foreach ($items as $node)
		{
			$type = $node[0];
			$button = $bar->loadButtonType($type);
			if ($button !== false)
			{
				if (method_exists($button, 'fetchId'))
				{
					$id = call_user_func_array(array(&$button, 'fetchId'), $node);
				}
				else
				{
					$id = null;
				}
				$action = call_user_func_array(array(&$button, 'fetchButton'), $node);
				$action = str_replace('class="toolbar"', 'class="toolbar btn"', $action);
				$action = str_replace('<span ', '<i ', $action);
				$action = str_replace('</span>', '</i>', $action);
				$action = str_replace(array_keys($substitutions), array_values($substitutions), $action);
				$html[] = $action;
			}
		}
		$html[] = '</div>';

		echo implode("\n", $html);
	}

	/**
	 * Renders a FOFForm for a Browse view and returns the corresponding HTML
	 *
	 * @param   FOFForm   $form      The form to render
	 * @param   FOFModel  $model     The model providing our data
	 * @param   FOFInput  $input     The input object
	 *
	 * @return  string    The HTML rendering of the form
	 */
	protected function renderFormBrowse(FOFForm &$form, FOFModel $model, FOFInput $input)
	{
		$html = '';

		// Joomla! 3.0+ support
		if (version_compare(JVERSION, '3.0', 'ge'))
		{
			JHtml::_('bootstrap.tooltip');
			JHtml::_('behavior.multiselect');
			JHtml::_('dropdown.init');
			JHtml::_('formbehavior.chosen', 'select');
			$view = $form->getView();
			$order = $view->escape($view->getLists()->order);
			$html .= <<<ENDJS
<script type="text/javascript">
	Joomla.orderTable = function() {
		table = document.getElementById("sortTable");
		direction = document.getElementById("directionTable");
		order = table.options[table.selectedIndex].value;
		if (order != '$order') {
			dirn = 'asc';
		} else {
			dirn = direction.options[direction.selectedIndex].value;
		}
		Joomla.tableOrdering(order, dirn, '');
	}
</script>

ENDJS;
		}

		// Getting all header row elements
		$headerFields = $form->getHeaderset();

		// Get form parameters
		$show_header = $form->getAttribute('show_header', 1);
		$show_filters = $form->getAttribute('show_filters', 1);
		$show_pagination = $form->getAttribute('show_pagination', 1);
		$norows_placeholder = $form->getAttribute('norows_placeholder', '');

		// Joomla! 3.0 sidebar support
		if (version_compare(JVERSION, '3.0', 'gt') && $show_filters)
		{
			JHtmlSidebar::setAction("index.php?option=" .
				$input->getCmd('option') . "&view=" .
				FOFInflector::pluralize($input->getCmd('view'))
			);
		}

		// Pre-render the header and filter rows
		$header_html = '';
		$filter_html = '';
		$sortFields = array();

		if ($show_header || $show_filters)
		{
			foreach ($headerFields as $headerField)
			{
				$header = $headerField->header;
				$filter = $headerField->filter;
				$buttons = $headerField->buttons;
				$options = $headerField->options;
				$sortable = $headerField->sortable;
				$tdwidth = $headerField->tdwidth;

				// Under Joomla! < 3.0 we can't have filter-only fields
				if (version_compare(JVERSION, '3.0', 'lt') && empty($header))
				{
					continue;
				}

				// If it's a sortable field, add to the list of sortable fields
				if ($sortable)
				{
					$sortFields[$headerField->name] = JText::_($headerField->label);
				}

				// Get the table data width, if set
				if (!empty($tdwidth))
				{
					$tdwidth = 'width="' . $tdwidth . '"';
				}
				else
				{
					$tdwidth = '';
				}

				$header_html .= "\t\t\t\t\t<th $tdwidth>" . PHP_EOL;
				$header_html .= "\t\t\t\t\t\t" . $header;
				$header_html .= "\t\t\t\t\t</th>" . PHP_EOL;

				if (version_compare(JVERSION, '3.0', 'ge'))
				{
					// Joomla! 3.0 or later
					if (!empty($filter))
					{
						$filter_html .= '<div class="filter-search btn-group pull-left">' . "\n";
						$filter_html .= "\t" . '<label for="title" class="element-invisible">';
						$filter_html .= $headerField->label;
						$filter_html .= "</label>\n";
						$filter_html .= "\t$filter\n";
						$filter_html .= "</div>\n";

						if (!empty($buttons))
						{
							$filter_html .= '<div class="btn-group pull-left hidden-phone">' . "\n";
							$filter_html .= "\t$buttons\n";
							$filter_html .= '</div>' . "\n";
						}
					}
					elseif (!empty($options))
					{
						$label = $headerField->label;

						JHtmlSidebar::addFilter(
							'- ' . JText::_($label) . ' -', (string) $headerField->name, JHtml::_('select.options', $options, 'value', 'text', $form->getModel()->getState($headerField->name, ''), true)
						);
					}
				}
				else
				{
					// Joomla! 2.5
					$filter_html .= "\t\t\t\t\t<td>" . PHP_EOL;
					if (!empty($filter))
					{
						$filter_html .= "\t\t\t\t\t\t$filter" . PHP_EOL;
						if (!empty($buttons))
						{
							$filter_html .= "\t\t\t\t\t\t<nobr>$buttons</nobr>" . PHP_EOL;
						}
					}
					elseif (!empty($options))
					{
						$label = $headerField->label;
						$emptyOption = JHtml::_('select.option', '', '- ' . JText::_($label) . ' -');
						array_unshift($options, $emptyOption);
						$attribs = array(
							'onchange'	 => 'document.adminForm.submit();'
						);
						$filter = JHtml::_('select.genericlist', $options, $headerField->name, $attribs, 'value', 'text', $headerField->value, false, true);
						$filter_html .= "\t\t\t\t\t\t$filter" . PHP_EOL;
					}
					$filter_html .= "\t\t\t\t\t</td>" . PHP_EOL;
				}
			}
		}

		// Start the form
		$filter_order = $form->getView()->getLists()->order;
		$filter_order_Dir = $form->getView()->getLists()->order_Dir;

		$html .= '<form action="index.php" method="post" name="adminForm" id="adminForm">' . PHP_EOL;
		$html .= "\t" . '<input type="hidden" name="option" value="' . $input->getCmd('option') . '" />' . PHP_EOL;
		$html .= "\t" . '<input type="hidden" name="view" value="' . FOFInflector::pluralize($input->getCmd('view')) . '" />' . PHP_EOL;
		$html .= "\t" . '<input type="hidden" name="task" value="' . $input->getCmd('task', 'browse') . '" />' . PHP_EOL;
		$html .= "\t" . '<input type="hidden" name="boxchecked" value="" />' . PHP_EOL;
		$html .= "\t" . '<input type="hidden" name="hidemainmenu" value="" />' . PHP_EOL;
		$html .= "\t" . '<input type="hidden" name="filter_order" value="' . $filter_order . '" />' . PHP_EOL;
		$html .= "\t" . '<input type="hidden" name="filter_order_Dir" value="' . $filter_order_Dir . '" />' . PHP_EOL;
		$html .= "\t" . '<input type="hidden" name="' . JFactory::getSession()->getFormToken() . '" value="1" />' . PHP_EOL;

		if (version_compare(JVERSION, '3.0', 'ge'))
		{
			// Joomla! 3.0+
			// Get and output the sidebar, if present
			$sidebar = JHtmlSidebar::render();
			if ($show_filters && !empty($sidebar))
			{
				$html .= '<div id="j-sidebar-container" class="span2">' . "\n";
				$html .= "\t$sidebar\n";
				$html .= "</div>\n";
				$html .= '<div id="j-main-container" class="span10">' . "\n";
			}
			else
			{
				$html .= '<div id="j-main-container">' . "\n";
			}

			// Render header search fields, if the header is enabled
			if ($show_header)
			{
				$html .= "\t" . '<div id="filter-bar" class="btn-toolbar">' . "\n";
				$html .= "$filter_html\n";

				if ($show_pagination)
				{
					// Render the pagination rows per page selection box, if the pagination is enabled
					$html .= "\t" . '<div class="btn-group pull-right hidden-phone">' . "\n";
					$html .= "\t\t" . '<label for="limit" class="element-invisible">' . JText::_('JFIELD_PLG_SEARCH_SEARCHLIMIT_DESC') . '</label>' . "\n";
					$html .= "\t\t" . $model->getPagination()->getLimitBox() . "\n";
					$html .= "\t" . '</div>' . "\n";
				}

				if (!empty($sortFields))
				{
					// Display the field sort order
					$asc_sel = ($view->getLists()->order_Dir == 'asc') ? 'selected="selected"' : '';
					$desc_sel = ($view->getLists()->order_Dir == 'desc') ? 'selected="selected"' : '';
					$html .= "\t" . '<div class="btn-group pull-right hidden-phone">' . "\n";
					$html .= "\t\t" . '<label for="directionTable" class="element-invisible">' . JText::_('JFIELD_ORDERING_DESC') . '</label>' . "\n";
					$html .= "\t\t" . '<select name="directionTable" id="directionTable" class="input-medium" onchange="Joomla.orderTable()">' . "\n";
					$html .= "\t\t\t" . '<option value="">' . JText::_('JFIELD_ORDERING_DESC') . '</option>' . "\n";
					$html .= "\t\t\t" . '<option value="asc" ' . $asc_sel . '>' . JText::_('JGLOBAL_ORDER_ASCENDING') . '</option>' . "\n";
					$html .= "\t\t\t" . '<option value="desc" ' . $desc_sel . '>' . JText::_('JGLOBAL_ORDER_DESCENDING') . '</option>' . "\n";
					$html .= "\t\t" . '</select>' . "\n";
					$html .= "\t" . '</div>' . "\n\n";

					// Display the sort fields
					$html .= "\t" . '<div class="btn-group pull-right">' . "\n";
					$html .= "\t\t" . '<label for="sortTable" class="element-invisible">' . JText::_('JGLOBAL_SORT_BY') . '</label>' . "\n";
					$html .= "\t\t" . '<select name="sortTable" id="sortTable" class="input-medium" onchange="Joomla.orderTable()">' . "\n";
					$html .= "\t\t\t" . '<option value="">' . JText::_('JGLOBAL_SORT_BY') . '</option>' . "\n";
					$html .= "\t\t\t" . JHtml::_('select.options', $sortFields, 'value', 'text', $view->getLists()->order) . "\n";
					$html .= "\t\t" . '</select>' . "\n";
					$html .= "\t" . '</div>' . "\n";
				}

				$html .= "\t</div>\n\n";
				$html .= "\t" . '<div class="clearfix"> </div>' . "\n\n";
			}
		}

		// Start the table output
		$html .= "\t\t" . '<table class="table table-striped" id="itemsList">' . PHP_EOL;

		// Open the table header region if required
		if ($show_header || ($show_filters && version_compare(JVERSION, '3.0', 'lt')))
		{
			$html .= "\t\t\t<thead>" . PHP_EOL;
		}

		// Render the header row, if enabled
		if ($show_header)
		{
			$html .= "\t\t\t\t<tr>" . PHP_EOL;
			$html .= $header_html;
			$html .= "\t\t\t\t</tr>" . PHP_EOL;
		}

		// Render filter row if enabled
		if ($show_filters && version_compare(JVERSION, '3.0', 'lt'))
		{
			$html .= "\t\t\t\t<tr>";
			$html .= $filter_html;
			$html .= "\t\t\t\t</tr>";
		}

		// Close the table header region if required
		if ($show_header || ($show_filters && version_compare(JVERSION, '3.0', 'lt')))
		{
			$html .= "\t\t\t</thead>" . PHP_EOL;
		}

		// Loop through rows and fields, or show placeholder for no rows
		$html .= "\t\t\t<tbody>" . PHP_EOL;
		$fields = $form->getFieldset('items');
		$num_columns = count($fields);
		$items = $form->getModel()->getItemList();
		if ($count = count($items))
		{
			$m = 1;
			foreach ($items as $i => $item)
			{
				$table_item = $form->getModel()->getTable();
				$table_item->bind($item);

				$form->bind($item);

				$m = 1 - $m;
				$class = 'row' . $m;

				$html .= "\t\t\t\t<tr class=\"$class\">" . PHP_EOL;

				$fields = $form->getFieldset('items');
				foreach ($fields as $field)
				{
					$field->rowid = $i;
					$field->item = $table_item;
					$class = $field->labelClass ? 'class ="' . $field->labelClass . '"' : '';
					$html .= "\t\t\t\t\t<td $class>" . $field->getRepeatable() . '</td>' . PHP_EOL;
				}

				$html .= "\t\t\t\t</tr>" . PHP_EOL;
			}
		}
		elseif ($norows_placeholder)
		{
			$html .= "\t\t\t\t<tr><td colspan=\"$num_columns\">";
			$html .= JText::_($norows_placeholder);
			$html .= "</td></tr>\n";
		}
		$html .= "\t\t\t</tbody>" . PHP_EOL;

		// Render the pagination bar, if enabled, on J! 2.5
		if ($show_pagination && version_compare(JVERSION, '3.0', 'lt'))
		{
			$pagination = $form->getModel()->getPagination();
			$html .= "\t\t\t<tfoot>" . PHP_EOL;
			$html .= "\t\t\t\t<tr><td colspan=\"$num_columns\">";
			if (($pagination->total > 0))
			{
				$html .= $pagination->getListFooter();
			}
			$html .= "</td></tr>\n";
			$html .= "\t\t\t</tfoot>" . PHP_EOL;
		}

		// End the table output
		$html .= "\t\t" . '</table>' . PHP_EOL;

		// Render the pagination bar, if enabled, on J! 3.0+
		if ($show_pagination && version_compare(JVERSION, '3.0', 'ge'))
		{
			$html .= $model->getPagination()->getListFooter();
			;
		}

		// Close the wrapper element div on Joomla! 3.0+
		if (version_compare(JVERSION, '3.0', 'ge'))
		{
			$html .= "</div>\n";
		}

		// End the form
		$html .= '</form>' . PHP_EOL;

		return $html;
	}

	/**
	 * Renders a FOFForm for a Browse view and returns the corresponding HTML
	 *
	 * @param   FOFForm   $form      The form to render
	 * @param   FOFModel  $model     The model providing our data
	 * @param   FOFInput  $input     The input object
	 *
	 * @return  string    The HTML rendering of the form
	 */
	protected function renderFormRead(FOFForm &$form, FOFModel $model, FOFInput $input)
	{
		// Get the key for this model's table
		$key = $model->getTable()->getKeyName();
		$keyValue = $model->getId();

		$html = '';

		foreach ($form->getFieldsets() as $fieldset)
		{
			$fields = $form->getFieldset($fieldset->name);

			if (isset($fieldset->class))
			{
				$class = 'class="' . $fieldset->class . '"';
			}
			else
			{
				$class = '';
			}

			$html .= "\t" . '<div id="' . $fieldset->name . '" ' . $class . '>' . PHP_EOL;

			if (isset($fieldset->label) && !empty($fieldset->label))
			{
				$html .= "\t\t" . '<h3>' . JText::_($fieldset->label) . '</h3>' . PHP_EOL;
			}

			foreach ($fields as $field)
			{
				$title = $field->title;
				$required = $field->required;
				$labelClass = $field->labelClass;
				$description = $field->description;

				$input = $field->static;

				if (empty($title))
				{
					$html .= "\t\t\t" . $input . PHP_EOL;
					if (!empty($description))
					{
						$html .= "\t\t\t\t" . '<span class="help-block">';
						$html .= JText::_($description) . '</span>' . PHP_EOL;
					}
				}
				else
				{
					$html .= "\t\t\t" . '<div class="control-group">' . PHP_EOL;
					$html .= "\t\t\t\t" . '<label class="control-label ' . $labelClass . '" for="' . $field->id . '">' . PHP_EOL;
					$html .= "\t\t\t\t" . JText::_($title) . PHP_EOL;
					if ($required)
					{
						$html .= ' *';
					}
					$html .= "\t\t\t\t" . '</label>' . PHP_EOL;
					$html .= "\t\t\t\t" . '<div class="controls">' . PHP_EOL;
					$html .= "\t\t\t\t" . $input . PHP_EOL;
					if (!empty($description))
					{
						$html .= "\t\t\t\t" . '<span class="help-block">';
						$html .= JText::_($description) . '</span>' . PHP_EOL;
					}
					$html .= "\t\t\t\t" . '</div>' . PHP_EOL;
					$html .= "\t\t\t" . '</div>' . PHP_EOL;
				}
			}

			$html .= "\t" . '</div>' . PHP_EOL;
		}

		return $html;
	}

	/**
	 * Renders a FOFForm for a Browse view and returns the corresponding HTML
	 *
	 * @param   FOFForm   $form      The form to render
	 * @param   FOFModel  $model     The model providing our data
	 * @param   FOFInput  $input     The input object
	 *
	 * @return  string    The HTML rendering of the form
	 */
	protected function renderFormEdit(FOFForm &$form, FOFModel $model, FOFInput $input)
	{
		// Get the key for this model's table
		$key = $model->getTable()->getKeyName();
		$keyValue = $model->getId();

		$html = '';

		if ($validate = $form->getAttribute('validate'))
		{
			JHTML::_('behavior.formvalidation');
			$class = ' form-validate';
			$this->loadValidationScript($form);
		}
		else
		{
			$class = '';
		}

		$html .= '<form action="index.php" method="post" name="adminForm" id="adminForm" class="form-horizontal' . $class . '">' . PHP_EOL;
		$html .= "\t" . '<input type="hidden" name="option" value="' . $input->getCmd('option') . '" />' . PHP_EOL;
		$html .= "\t" . '<input type="hidden" name="view" value="' . $input->getCmd('view', 'edit') . '" />' . PHP_EOL;
		$html .= "\t" . '<input type="hidden" name="task" value="" />' . PHP_EOL;

		$html .= "\t" . '<input type="hidden" name="' . $key . '" value="' . $keyValue . '" />' . PHP_EOL;
		$html .= "\t" . '<input type="hidden" name="' . JFactory::getSession()->getFormToken() . '" value="1" />' . PHP_EOL;

		foreach ($form->getFieldsets() as $fieldset)
		{
			$fields = $form->getFieldset($fieldset->name);

			if (isset($fieldset->class))
			{
				$class = 'class="' . $fieldset->class . '"';
			}
			else
			{
				$class = '';
			}

			$html .= "\t" . '<div id="' . $fieldset->name . '" ' . $class . '>' . PHP_EOL;

			if (isset($fieldset->label) && !empty($fieldset->label))
			{
				$html .= "\t\t" . '<h3>' . JText::_($fieldset->label) . '</h3>' . PHP_EOL;
			}

			foreach ($fields as $field)
			{
				$title = $field->title;
				$required = $field->required;
				$labelClass = $field->labelClass;
				$description = $field->description;

				$input = $field->input;

				$html .= "\t\t\t" . '<div class="control-group">' . PHP_EOL;
				$html .= "\t\t\t\t" . '<label class="control-label ' . $labelClass . '" for="' . $field->id . '">' . PHP_EOL;
				$html .= "\t\t\t\t" . JText::_($title) . PHP_EOL;
				if ($required)
				{
					$html .= ' *';
				}
				$html .= "\t\t\t\t" . '</label>' . PHP_EOL;
				$html .= "\t\t\t\t" . '<div class="controls">' . PHP_EOL;
				$html .= "\t\t\t\t" . $input . PHP_EOL;
				if (!empty($description))
				{
					$html .= "\t\t\t\t" . '<span class="help-block">';
					$html .= JText::_($description) . '</span>' . PHP_EOL;
				}
				$html .= "\t\t\t\t" . '</div>' . PHP_EOL;
				$html .= "\t\t\t" . '</div>' . PHP_EOL;
			}

			$html .= "\t" . '</div>' . PHP_EOL;
		}

		$html .= '</form>';

		return $html;
	}
}