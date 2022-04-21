<?php  

defined('_JEXEC') or die;

class OsceViewTags extends JViewLegacy
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
		// die("fgd");

		//die("gf");
		$this->addToolBar();

		TagHelper::addSubmenu('tags');

		parent::display($tpl);

	}

	protected function addToolBar()
	{
		JToolbarHelper::title(JText::_('COM_TAGS'));
		JToolbarHelper::addNew('tag.add');
		JToolbarHelper::editList('tag.edit');
		JToolbarHelper::deleteList('','tags.delete');
		JToolBarHelper::preferences('com_osce');

	}
}



?>