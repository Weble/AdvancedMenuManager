<?php
// Protect from unauthorized access
defined('_JEXEC') or die();

class AdvancedmenusViewItems extends FOFViewHtml
{
	public function onBrowse($tpl = null) {

		parent::onBrowse($tpl);
		
		$model = $this->getModel();
		
		//$this->assign('modules', $modules);
		
		return true;
	}
}