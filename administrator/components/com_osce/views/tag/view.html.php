<?php  

defined('_JEXEC') or die('Restricted access');

//die("fds");
class OsceViewTag extends JViewLegacy
{
	protected $form = null;

	function display($tpl=null)
	{
		//die("sd");
		$this->form = $this->get('Form');
		$this->item = $this->get('Item');
		// echo "<pre>";
		// print_r($this->item);
		// die("resa");
		$this->addToolBar();

		parent::display($tpl);

	}

	protected function addToolBar()
	{
		$isNew = ($this->item->id == 0);
		$title = JText::_('COM_OSCE_TAG_TITLE');
		JToolbarHelper::title($title, 'tag');
		JToolbarHelper::save('tag.save');
		JToolbarHelper::save2new('tag.save2new');
		JToolBarHelper::cancel('tag.cancel',$isNew ? 'JTOOLBAR_CANCEL' : 'JTOOLBAR_CLOSE');
	}

}