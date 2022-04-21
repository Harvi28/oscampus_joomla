<?php  

defined('_JEXEC') or die;

class OsceViewQuesbanks extends JViewLegacy
{
	function display($tpl=null)
	{
		$app = JFactory::getApplication();

		$this->items= $this->get('Items');
		$this->pagination	= $this->get('Pagination');
		$this->filterForm    	= $this->get('FilterForm');
		$this->activeFilters 	= $this->get('ActiveFilters');

		// echo "<pre>";
		// print_r($this->items);
		// die("kss");
		
		$this->addToolBar();
		QuesbankHelper::addSubmenu('quesbanks');



		parent::display($tpl);

	}

	protected function addToolBar()
	{
		JToolbarHelper::title(JText::_('COM_OSCE_QUESBANKS'));
		JToolbarHelper::addNew('quesbank.add');
		JToolbarHelper::editList('quesbank.edit');
		JToolbarHelper::deleteList('','quesbanks.delete');
		
		JToolBarHelper::preferences('com_osce');

	}
}



?>