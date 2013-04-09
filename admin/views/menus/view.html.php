<?php
// Protect from unauthorized access
defined('_JEXEC') or die();

class AdvancedmenusViewMenus extends FOFViewHtml
{
	public function onBrowse($tpl = null) {

		parent::onBrowse($tpl);
		
		$model = $this->getModel();
		$id = $model->getModuleId();
		$modules = $model->getModules();

		$this->assign('modMenuId', $id);
		$this->assign('modules', $modules);
		
		return true;
	}
}