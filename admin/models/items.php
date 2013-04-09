<?php

class AdvancedmenusModelItems extends FOFModel {

	/**
	 * Original credits to Joomla com_advancedmenus
	 */
	public function &getItemList($overrideLimits = false, $group = '')
	{

		$items  = parent::getItemList($overrideLimits, $group);
		$lang 	= JFactory::getLanguage();
		
		// Preprocess the list of items to find ordering divisions.
		foreach ($items as &$item)
		{
			// item type text
			switch ($item->type)
			{
				case 'url':
					$value = JText::_('COM_ADVANCEDMENUS_TYPE_EXTERNAL_URL');
					break;

				case 'alias':
					$value = JText::_('COM_ADVANCEDMENUS_TYPE_ALIAS');
					break;

				case 'separator':
					$value = JText::_('COM_ADVANCEDMENUS_TYPE_SEPARATOR');
					break;

				case 'heading':
					$value = JText::_('COM_ADVANCEDMENUS_TYPE_HEADING');
					break;

				case 'component':
				default:
					// load language
						$lang->load($item->componentname.'.sys', JPATH_ADMINISTRATOR, null, false, false)
					||	$lang->load($item->componentname.'.sys', JPATH_ADMINISTRATOR.'/components/'.$item->componentname, null, false, false)
					||	$lang->load($item->componentname.'.sys', JPATH_ADMINISTRATOR, $lang->getDefault(), false, false)
					||	$lang->load($item->componentname.'.sys', JPATH_ADMINISTRATOR.'/components/'.$item->componentname, $lang->getDefault(), false, false);

					if (!empty($item->componentname))
					{
						$value	= JText::_($item->componentname);
						$vars	= null;

						parse_str($item->link, $vars);
						if (isset($vars['view']))
						{
							// Attempt to load the view xml file.
							$file = JPATH_SITE.'/components/'.$item->componentname.'/views/'.$vars['view'].'/metadata.xml';
							if (is_file($file) && $xml = simplexml_load_file($file))
							{
								// Look for the first view node off of the root node.
								if ($view = $xml->xpath('view[1]'))
								{
									if (!empty($view[0]['title']))
									{
										$vars['layout'] = isset($vars['layout']) ? $vars['layout'] : 'default';

										// Attempt to load the layout xml file.
										// If Alternative Menu Item, get template folder for layout file
										if (strpos($vars['layout'], ':') > 0)
										{
											// Use template folder for layout file
											$temp = explode(':', $vars['layout']);
											$file = JPATH_SITE.'/templates/'.$temp[0].'/html/'.$item->componentname.'/'.$vars['view'].'/'.$temp[1].'.xml';
											// Load template language file
											$lang->load('tpl_'.$temp[0].'.sys', JPATH_SITE, null, false, false)
											||	$lang->load('tpl_'.$temp[0].'.sys', JPATH_SITE.'/templates/'.$temp[0], null, false, false)
											||	$lang->load('tpl_'.$temp[0].'.sys', JPATH_SITE, $lang->getDefault(), false, false)
											||	$lang->load('tpl_'.$temp[0].'.sys', JPATH_SITE.'/templates/'.$temp[0], $lang->getDefault(), false, false);

										}
										else
										{
											// Get XML file from component folder for standard layouts
											$file = JPATH_SITE.'/components/'.$item->componentname.'/views/'.$vars['view'].'/tmpl/'.$vars['layout'].'.xml';
										}
										if (is_file($file) && $xml = simplexml_load_file($file))
										{
											// Look for the first view node off of the root node.
											if ($layout = $xml->xpath('layout[1]'))
											{
												if (!empty($layout[0]['title']))
												{
													$value .= ' » ' . JText::_(trim((string) $layout[0]['title']));
												}
											}
											if (!empty($layout[0]->message[0]))
											{
												$item->item_type_desc = JText::_(trim((string) $layout[0]->message[0]));
											}
										}
									}
								}
								unset($xml);
							}
							else {
								// Special case for absent views
								$value .= ' » ' . JText::_($item->componentname.'_'.$vars['view'].'_VIEW_DEFAULT_TITLE');
							}
						}
					}
					else {
						if (preg_match("/^index.php\?option=([a-zA-Z\-0-9_]*)/", $item->link, $result))
						{
							$value = JText::sprintf('COM_ADVANCEDMENUS_TYPE_UNEXISTING', $result[1]);
						}
						else {
							$value = JText::_('COM_ADVANCEDMENUS_TYPE_UNKNOWN');
						}
					}
					break;
			}
			$item->item_type = $value;
		}

		return $items;
	}

	public function buildQuery($overrideLimits = false) {

		$table = $this->getTable();
		$tableName = $table->getTableName();
		$tableKey = $table->getKeyName();
		$db = $this->getDBO();

		$query = $db->getQuery(true);
		// Select all fields from the table.
		$query->select(
			$db->quoteName(
				array('a.id', 'a.menutype', 'a.title', 'a.alias', 'a.note', 'a.path', 'a.link', 'a.type', 'a.parent_id', 'a.level', 'a.published', 'a.component_id', 'a.checked_out', 'a.checked_out_time', 'a.browserNav', 'a.access', 'a.img', 'a.template_style_id', 'a.params', 'a.lft', 'a.rgt', 'a.home', 'a.language', 'a.client_id'),
				array(null, null, null, null, null, null, null, null, null, null, 'apublished', null, null, null, null, null, null, null, null, null, null, null, null, null)
			)
		);
		$query->from($db->qn($tableName) . ' AS a');

		$where = array();

		if (version_compare(JVERSION, '3.0', 'ge'))
		{
			$fields = $db->getTableColumns($tableName, true);
		}
		else
		{
			$fieldsArray = $db->getTableFields($tableName, true);
			$fields = array_shift($fieldsArray);
		}

		foreach ($fields as $fieldname => $fieldtype)
		{
			$filterName = ($fieldname == $tableKey) ? 'id' : $fieldname;
			$filterState = $this->getState($filterName, null);

			if ($filterName == $table->getColumnAlias('enabled'))
			{
				if (!is_null($filterState) && ($filterState !== ''))
				{
					$query->where($db->qn($fieldname) . ' = ' . $db->q((int) $filterState));
				}
			}
			elseif (!empty($filterState) || ($filterState === '0'))
			{
				if ($filterName != 'published') {
					$query->where('(' . $db->qn($fieldname) . '=' . $db->q($filterState) . ')');
				}
			}
		}

		if (!$overrideLimits)
		{
			$order = $this->getState('filter_order', null, 'cmd');

			if (!in_array($order, array_keys($this->getTable()->getData())))
			{
				$order = "a." . $tableKey;
			}

			$dir = $this->getState('filter_order_Dir', 'ASC', 'cmd');
			$query->order($db->qn($order) . ' ' . $dir);
		}

		$query->select('CASE a.type' .
			' WHEN ' . $db->quote('component') . ' THEN a.published+2*(e.enabled-1) ' .
			' WHEN ' . $db->quote('url') . ' THEN a.published+2 ' .
			' WHEN ' . $db->quote('alias') . ' THEN a.published+4 ' .
			' WHEN ' . $db->quote('separator') . ' THEN a.published+6 ' .
			' WHEN ' . $db->quote('heading') . ' THEN a.published+8 ' .
			' END AS published');

		// Join over the language
		$query->select('l.title AS language_title, l.image as image');
		$query->join('LEFT', $db->quoteName('#__languages').' AS l ON l.lang_code = a.language');

		// Join over the users.
		$query->select('u.name AS editor');
		$query->join('LEFT', $db->quoteName('#__users').' AS u ON u.id = a.checked_out');

		//Join over components
		$query->select('c.element AS componentname');
		$query->join('LEFT', $db->quoteName('#__extensions').' AS c ON c.extension_id = a.component_id');

		// Join over the asset groups.
		$query->select('vl.title AS access_level');
		$query->join('LEFT', $db->quoteName('#__viewlevels') . ' AS vl ON vl.id = a.access');

		// Join over the associations.
		$assoc = isset($app->item_associations) ? $app->item_associations : 0;
		if ($assoc)
		{
			$query->select('COUNT(asso2.id)>1 as association');
			$query->join('LEFT', $db->quoteName('#__associations') .' AS asso ON asso.id = a.id AND asso.context='.$db->quote('com_menus.item'));
			$query->join('LEFT', $db->quoteName('#__associations') .' AS asso2 ON asso2.key = asso.key');
			$query->group('a.id');
		}

		// Join over the extensions
		$query->select('e.name AS name');
		$query->join('LEFT', $db->quoteName('#__extensions') .' AS e ON e.extension_id = a.component_id');

		// Exclude the root category.
		$query->where('a.id > 1');
		$query->where('a.client_id = 0');

		// Filter on the published state.
		$published = $this->getState('published', '', 'int');
		if (is_numeric($published))
		{
			$query->where('a.published = '.(int) $published);
		} 
		elseif ($published === '')
		{
			$query->where('(a.published IN (0, 1))');
		}

		// Filter by search in title, alias or id
		if ($search = trim($this->getState('search')))
		{
			if (stripos($search, 'id:') === 0)
			{
				$query->where('a.id = '.(int) substr($search, 3));
			} elseif (stripos($search, 'link:') === 0)
			{
				if ($search = substr($search, 5))
				{
					$search = $db->Quote('%'.$db->escape($search, true).'%');
					$query->where('a.link LIKE '.$search);
				}
			} else {
				$search = $db->Quote('%'.$db->escape($search, true).'%');
				$query->where('('.'a.title LIKE '.$search.' OR a.alias LIKE '.$search.' OR a.note LIKE '.$search.')');
			}
		}

		//echo nl2br(str_replace("#__", "fqu0o_", $query));

		return $query;
	}

}