<?php
// Protect from unauthorized access
defined('_JEXEC') or die();

class AdvancedmenusDispatcher extends FOFDispatcher
{
	public function onBeforeDispatch() {
		$result = parent::onBeforeDispatch();

		if($result) {
			// Load Akeeba Strapper
			include_once JPATH_ROOT.'/media/akeeba_strapper/strapper.php';
			$tag = uniqid();
			AkeebaStrapper::$tag = $tag;
			AkeebaStrapper::bootstrap();
			AkeebaStrapper::jQueryUI();
			AkeebaStrapper::addJSfile('media://com_akeeba/js/gui-helpers.js');
			AkeebaStrapper::addJSfile('media://com_akeeba/js/akeebaui.js');
			AkeebaStrapper::addJSfile('media://com_akeeba/plugins/js/akeebaui.js');
			AkeebaStrapper::addCSSfile('media://com_akeeba/theme/akeebaui.css');
		}

		return $result;
	}
	
	public function __construct($config = array()) 
	{
		$this->defaultView = 'menus';
			
		parent::__construct($config);
	}
}