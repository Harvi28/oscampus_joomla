<?php  
use Joomla\CMS\Factory;
JHtml::_('jquery.framework', false);
// die;



class OsceViewGeneratequiz extends JViewLegacy
{
		protected $form = null;

	public function display($tpl=null)
	{
		$app = JFactory::getApplication();

		$document = JFactory::getDocument();
		$this->form = $this->get('Form');

		
		$this->pagination	= $this->get('Pagination');
		$this->filterForm    	= $this->get('FilterForm');
		$this->activeFilters 	= $this->get('ActiveFilters');
		$this->script = $this->get('Script');
        $this->setDocument();

  //       $data = ['name'=>'harvi'];
		// OscampusModel::getInstance('Lesson', 'OscampusModel')->save($data);
		// die;


		
		parent::display($tpl);
		// $this->setDocument();
	}

	protected function setDocument(){
		// die("Fdg");
		$document = JFactory::getDocument();
		// 
		// JHtml::script(Juri::base(). 'components/com_osce/assets/js/load_que.js');

	}
	
}