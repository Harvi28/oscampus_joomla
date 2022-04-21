<?php  

defined('_JEXEC') or die('Restricted access');

class OsceControllerTags extends JControllerAdmin
{
	protected $text_prefix = 'COM_OSCE_TAGS';

	public function getModel($name = 'Tag', $prefix = 'OsceModel', $config = array('ignore_request' => true))
	{
		//die("sdf");	
		$model = parent::getModel($name, $prefix, $config);

		return $model;
	}

	public function getData()
	{
		$req_body = file_get_contents('php://input');
		$data = json_decode($req_body);
        $title = $input->get('title');
		  // print_r("gomna");die;
		$model = $this->getModel('tags');
		
		// var_dump($input->get('title'));
		print_r($id);
		die;
		
		print_r($model->getItems());die;
		// echo json_encode($model->getItems());die;
	}

	public function getTags(){
		$model = $this->getModel('tags');
		$tags = $model->getTags();
		echo json_encode($tags);die;
	}

	public function getQuebyTagsId(){
	
		$model = $this->getModel('tags');
		$tagData = $model->getQuebyTagsId();

		// echo "<pre>";print_r($tagData);die;

		echo json_encode($tagData);
		die;
	}

}