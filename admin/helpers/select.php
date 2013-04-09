<?php
/**
 *  @package AkeebaSubs
 *  @copyright Copyright (c)2010-2013 Nicholas K. Dionysopoulos
 *  @license GNU General Public License version 3, or later
 */

defined('_JEXEC') or die();

class AdvancedmenusHelperSelect
{
	protected static function genericlist($list, $name, $attribs, $selected, $idTag)
	{
		if(empty($attribs))
		{
			$attribs = null;
		}
		else
		{
			$temp = '';
			foreach($attribs as $key=>$value)
			{
				$temp .= $key.' = "'.$value.'"';
			}
			$attribs = $temp;
		}

		return JHTML::_('select.genericlist', $list, $name, $attribs, 'value', 'text', $selected, $idTag);
	}

	protected static function genericradiolist($list, $name, $attribs, $selected, $idTag)
	{
		if(empty($attribs))
		{
			$attribs = null;
		}
		else
		{
			$temp = '';
			foreach($attribs as $key=>$value)
			{
				$temp .= $key.' = "'.$value.'"';
			}
			$attribs = $temp;
		}

		return JHTML::_('select.radiolist', $list, $name, $attribs, 'value', 'text', $selected, $idTag);
	}

	public static function booleanlist( $name, $attribs = null, $selected = null )
	{
		$options = array(
			JHTML::_('select.option','','---'),
			JHTML::_('select.option',  '0', JText::_( 'JNo' ) ),
			JHTML::_('select.option',  '1', JText::_( 'JYes' ) )
		);
		return self::genericlist($options, $name, $attribs, $selected, $name);
	}

	public static function published($selected = null, $name = 'published', $attribs = array())
	{
		$options = array();
		$options[] = JHTML::_('select.option',null,JText::_('JOPTION_SELECT_PUBLISHED'));
		$options[] = JHTML::_('select.option',1,JText::_('JPUBLISHED'));
		$options[] = JHTML::_('select.option',0,JText::_('JUNPUBLISHED'));
		$options[] = JHTML::_('select.option',-2,JText::_('JTRASHED'));
		$options[] = JHTML::_('select.option','*',JText::_('JALL'));

		return self::genericlist($options, $name, $attribs, $selected, $name);
	}
	
	public static function menutype($selected = null, $name = 'menutype', $attribs = array())
	{
		$model = FOFModel::getTmpInstance('Menus','AdvancedmenusModel');
		$items = $model->savestate(0)->limit(0)->limitstart(0)->getItemList();
		
		$options = array();
		
		if(count($items)) foreach($items as $item)
		{
			$options[] = JHTML::_('select.option',$item->menutype, $item->title);
		}

		return self::genericlist($options, $name, $attribs, $selected, $name);
	}

	public static function access($selected = null, $name = 'access', $attribs = array())
	{
		$options = array();
		$options[] = JHTML::_('select.option',null,JText::_('JOPTION_SELECT_ACCESS'));
		foreach (JHtml::_('access.assetgroups') as $o) {
			$options[] = JHTML::_('select.option',$o->value,$o->text);
		}

		return self::genericlist($options, $name, $attribs, $selected, $name);
	}

	public static function languages($selected = null, $name = 'language', $attribs = array())
	{
		$options = array();
		$options[] = JHTML::_('select.option',null,JText::_('JOPTION_SELECT_LANGUAGE'));
		foreach (JHtml::_('contentlanguage.existing', true, true) as $o) {
			$options[] = JHTML::_('select.option',$o->value,$o->text);
		}

		return self::genericlist($options, $name, $attribs, $selected, $name);
	}

	public static function levels($selected = null, $name = 'level', $attribs = array())
	{
		$options = array();
		$options[] = JHTML::_('select.option',null,JText::_('COM_ADVANCEDMENUS_OPTION_SELECT_LEVEL'));
		for ($i = 1; $i <= 10; $i++) {
			$options[] = JHTML::_('select.option',$i,JText::_('J'.$i));
		}

		return self::genericlist($options, $name, $attribs, $selected, $name);
	}
}