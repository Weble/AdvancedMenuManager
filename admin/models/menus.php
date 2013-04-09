<?php

class AdvancedmenusModelMenus extends FOFModel {

	public function getModuleId() 
	{
		$db = $this->getDbo();
		$query = $db->getQuery(true);

		$query->select('e.extension_id')
			->from('#__extensions AS e')
			->where('e.type = ' . $db->quote('module'))
			->where('e.element = ' . $db->quote('mod_menu'))
			->where('e.client_id = 0');
		$db->setQuery($query);

		return $db->loadResult();
	}

	public function &getModules()
	{
		$db = $this->getDbo();

		$query = $db->getQuery(true);
		$query->from('#__modules as a');
		$query->select('a.id, a.title, a.params, a.position');
		$query->where('module = '.$db->quote('mod_menu'));
		$query->select('ag.title AS access_title');
		$query->join('LEFT', '#__viewlevels AS ag ON ag.id = a.access');
		$db->setQuery($query);

		$modules = $db->loadObjectList();

		$result = array();

		foreach ($modules as &$module)
		{
			$params = new JRegistry;
			$params->loadString($module->params);

			$menuType = $params->get('menutype');
			if (!isset($result[$menuType]))
			{
				$result[$menuType] = array();
			}
			$result[$menuType][] = &$module;
		}

		return $result;
	}

	/**
	 * Original credits to Joomla com_advancedmenus
	 */
	public function &getItemList($overrideLimits = false, $group = '')
	{
		// Load the list items.
		$items = parent::getItemList($overrideLimits = false, $group = '');

		// If emtpy or an error, just return.
		if (empty($items))
		{
			return array();
		}

		// Getting the following metric by joins is WAY TOO SLOW.
		// Faster to do three queries for very large menu trees.

		// Get the menu types of menus in the list.
		$db = $this->getDbo();
		$menuTypes = JArrayHelper::getColumn($items, 'menutype');

		// Quote the strings.
		$menuTypes = implode(
			',',
			array_map(array($db, 'quote'), $menuTypes)
		);

		// Get the published menu counts.
		$query = $db->getQuery(true)
			->select('m.menutype, COUNT(DISTINCT m.id) AS count_published')
			->from('#__menu AS m')
			->where('m.published = 1')
			->where('m.menutype IN ('.$menuTypes.')')
			->group('m.menutype');

		$db->setQuery($query);

		try
		{
			$countPublished = $db->loadAssocList('menutype', 'count_published');
		}
		catch (RuntimeException $e)
		{
			$this->setError($e->getMessage());
			return false;
		}

		// Get the unpublished menu counts.
		$query->clear('where')
			->where('m.published = 0')
			->where('m.menutype IN ('.$menuTypes.')');
		$db->setQuery($query);

		try
		{
			$countUnpublished = $db->loadAssocList('menutype', 'count_published');
		}
		catch (RuntimeException $e)
		{
			$this->setError($e->getMessage());
			return false;
		}

		// Get the trashed menu counts.
		$query->clear('where')
			->where('m.published = -2')
			->where('m.menutype IN ('.$menuTypes.')');
		$db->setQuery($query);

		try
		{
			$countTrashed = $db->loadAssocList('menutype', 'count_published');
		}
		catch (RuntimeException $e)
		{
			$this->setError($e->getMessage);
			return false;
		}

		// Inject the values back into the array.
		foreach ($items as &$item)
		{
			$item->count_published = isset($countPublished[$item->menutype]) ? $countPublished[$item->menutype] : 0;
			$item->count_unpublished = isset($countUnpublished[$item->menutype]) ? $countUnpublished[$item->menutype] : 0;
			$item->count_trashed = isset($countTrashed[$item->menutype]) ? $countTrashed[$item->menutype] : 0;
		}

		return $items;
	}
}