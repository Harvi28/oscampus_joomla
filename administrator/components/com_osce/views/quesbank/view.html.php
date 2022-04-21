<?php  

defined('_JEXEC') or die('Restricted access');
JHtml::_('behavior.formvalidator');


//die("fds");
class OsceViewQuesbank extends JViewLegacy
{
	protected $form = null;

	function display($tpl=null)
	{
		//die("sd");
		$this->form = $this->get('Form');
		$this->item = $this->get('Item');
		// echo"<pre>";
		// print_r($this->form);
		// die("gds");
		$this->addToolBar();

		parent::display($tpl);

	}

	protected function addToolBar()
	{
		$isNew = ($this->item->id == 0);

		$title = JText::_('COM_OSCE_QUESBANK_TITLE');
		JToolbarHelper::title($title, 'quesbank');
		JToolbarHelper::save('quesbank.save');
		JToolbarHelper::save2new('quesbank.save2new');
		JToolBarHelper::cancel('quesbank.cancel',$isNew ? 'JTOOLBAR_CANCEL' : 'JTOOLBAR_CLOSE');
	}

}