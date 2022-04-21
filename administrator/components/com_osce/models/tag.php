<?php

defined('_JEXEC') or die('Restricted access');

class OsceModelTag extends JModelAdmin
{
		//protected $messages;

	public function getTable($type = 'Tag', $prefix = 'OsceTable', $config = array())
	{
		//die("sdsf");
		return JTable::getInstance($type, $prefix, $config);
	}

	public function getForm($data = array(), $loadData = true)
	{
		//die("sdsf");
		// Get the form.
		$form = $this->loadForm('com_osce.tag','tag',array('control' => 'jform','load_data' => $loadData));
		// echo "<pre>";
		// print_r($form);
		// die("sdf");

		if (empty($form))
		{
			//die("sdf");
			return false;
		}

		return $form;
	}

	protected function loadFormData()
	{
		//die("sdf");
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState(
			'com_osce.edit.tag.data',
			array()
		);

		//echo "<pre>";
		//print_r($data);
		//die();

		if (empty($data))
		{
			//die();
			$data = $this->getItem();
			//echo "<pre>";
			//print_r($data);
			//die("sssdf");
		}

		return $data;
	}

	public function save($data)
	{
		$data['created_by'] = JFactory::getUser()->id;
		$data['modified_on'] = date('Y-m-d H:i:s');

		// echo "<pre>";
		// print_r($data);
		// die;
		
		return parent::save($data);
	}

 
}