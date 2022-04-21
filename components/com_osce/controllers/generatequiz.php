<?php 
// die; 
require_once JPATH_ADMINISTRATOR . '/components/com_oscampus/models/lesson.php';


class OsceControllerGeneratequiz extends JControllerForm
{
	// http://localhost/oscampus/index.php?option=com_osce&view=generatequiz&task=generatequiz.harvi
	
	public function generate()
	{
		// die;
		// echo "<pre>";
		// die;

		if(!empty($_POST) && !empty($_POST['jform'])){
			$tags = $_POST['jform']['tags'];
			$limit = $_POST['jform']['list']['limit'];

			if(empty($tags) && !isset($limit)){
				// throw error
				die('please make error appropreate');
			}

			$model = $this->getModel('generatequiz');
			//harviii
			$questionTag = $model->getTags($tags);

		
			$quetionsData = $model->getQuebyTagsId($questionTag, $limit);
			$questitle = $quetionsData['title'];

			

			if(!empty($quetionsData)){
				 // die;
				OscampusModel::addIncludePath(OSCAMPUS_ADMIN.'/models');
				$issaved = OscampusModel::getInstance('Lesson', 'OscampusModel')->save($quetionsData);
				if($issaved){
					// redirect to quiz pge
					// die("hiii");
					$app = JFactory::getApplication(); 
						$qsdata = JComponentHelper::getParams('com_osce');
						$lessonName = $qsdata->get('lesson');
						// $model = $this->getModel('lessonAlias');
						$lessonAlias = $model->lessonAlias($lessonName);
					
					
					$url= JRoute::_('index.php?option=com_oscampus&view=course'.'/'.$lessonAlias.'/'.$questitle);

						// $url= JRoute::_('index.php?option=com_oscampus&view=mycourses'.'/'.$lessonAlias);
					// echo "<pre>";
					// print_r($url);
					// die;
					
					$app->redirect($url);
				
				}else{
					// redirect on same page
					$app = JFactory::getApplication(); 
					$url= JRoute::_('index.php?option=com_osce&view=generatequiz',false);
					
					$app->redirect($url);
					die;
				}
			}

		}


		// die;
	}
	public function save($key=null, $urlVar=null){
		// die;
		$app = JFactory::getApplication(); 
		$input = $app->input; 
		// $model = $this->getModel('generatequiz');
		// $tag = $model->getTags();

		
		
	
		return true;
	}

	public function getQuebyTagsId(){
	

		$model = $this->getModel('generatequiz');
		$tagData = $model->getQuebyTagsId();

		// echo "<pre>";print_r($tagData);die;

		echo json_encode($tagData);
		die;
	}



	
}