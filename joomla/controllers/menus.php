<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_menus
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * The Menu List Controller
 *
 * @package     Joomla.Administrator
 * @subpackage  com_menus
 * @since       1.6
 */
class AdvancedmenusControllerMenus extends FOFController
{
	/**
	 * Rebuild the menu tree.
	 *
	 * @return  bool	False on failure or error, true on success.
	 */
	public function rebuild()
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$this->setRedirect('index.php?option=com_menus&view=menus');

		$model = $this->getModel('Item');

		if ($model->rebuild())
		{
			// Reorder succeeded.
			$this->setMessage(JText::_('JTOOLBAR_REBUILD_SUCCESS'));
			return true;
		}
		else
		{
			// Rebuild failed.
			$this->setMessage(JText::sprintf('JTOOLBAR_REBUILD_FAILED', $model->getMessage()));
			return false;
		}
	}

	/**
	 * Temporary method. This should go into the 1.5 to 1.6 upgrade routines.
	 */
	public function resync()
	{
		$db = JFactory::getDbo();
		$parts = null;

		try
		{
			// Load a lookup table of all the component id's.
			$components = $db->setQuery(
				'SELECT element, extension_id' .
				' FROM #__extensions' .
				' WHERE type = '.$db->quote('component')
			)->loadAssocList('element', 'extension_id');
		}
		catch (RuntimeException $e)
		{
			return JError::raiseWarning(500, $e->getMessage());
		}

		try
		{
			// Load all the component menu links
			$items = $db->setQuery(
				'SELECT id, link, component_id' .
				' FROM #__menu' .
				' WHERE type = '.$db->quote('component')
			)->loadObjectList();
		}
		catch (RuntimeException $e)
		{
			return JError::raiseWarning(500, $e->getMessage());
		}

		foreach ($items as $item)
		{
			// Parse the link.
			parse_str(parse_url($item->link, PHP_URL_QUERY), $parts);

			// Tease out the option.
			if (isset($parts['option']))
			{
				$option = $parts['option'];

				// Lookup the component ID
				if (isset($components[$option]))
				{
					$componentId = $components[$option];
				} else {
					// Mismatch. Needs human intervention.
					$componentId = -1;
				}

				// Check for mis-matched component id's in the menu link.
				if ($item->component_id != $componentId)
				{
					// Update the menu table.
					$log = "Link $item->id refers to $item->component_id, converting to $componentId ($item->link)";
					echo "<br/>$log";

					try
					{
						$db->setQuery(
							'UPDATE #__menu' .
							' SET component_id = '.$componentId.
							' WHERE id = '.$item->id
						)->execute();
					}
					catch (RuntimeException $e)
					{
						return JError::raiseWarning(500, $e->getMessage());
					}
					//echo "<br>".$db->getQuery();
				}
			}
		}
	}
}
