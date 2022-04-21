<?php  

defined('_JEXEC') or die;

class OsceViewImport extends JViewLegacy
{
	function display($tpl=null)
	{
		// die;

				$app = JFactory::getApplication();

		ImportHelper::addSubmenu('import');
		$this->filterForm    	= $this->get('FilterForm');
		$this->activeFilters 	= $this->get('ActiveFilters');

		parent::display($tpl);

	}

	
}



?>