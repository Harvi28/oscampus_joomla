<?php  

defined('_JEXEC') or die('Restricted access');



class OsceControllerQuesbanks extends JControllerAdmin
{
	protected $text_prefix = 'COM_OSCE_QUESBANKS';

	public function getModel($name = 'Quesbank', $prefix = 'OsceModel', $config = array('ignore_request' => true))
	{
		//die("sdf");	
		$model = parent::getModel($name, $prefix, $config);

		return $model;
	}

	public function importques()
	{
		 // die;
		$app = JFactory::getApplication();   
		$input = $app->input;
		// die;
		$files = $input->files->get('jform1');
		$model = $this->getModel('import');

		$fileName = $files['name'];
		// $fileData = $model->fileDataInsert($files);
		
		$fileexe = $files['type'];
		if($fileexe == "text/csv")
		{
			$filePath= $files['tmp_name'];
			
			$rows = array_map('str_getcsv',file($filePath));
			
			$header = array_shift($rows);
			$csvDa = [];
			foreach($rows as $row) 
			{
				$row[0] = explode(',',$row[0]);
                $csvDa[] = array_combine($header, $row);
	        }


	        $test = $model->data($csvDa);
	        // echo "<pre>";
	        // print_r($csvDa);
	        // die;

	        $saveQues = $model->generateQuestion($test);
	        
}
		else
		{
			?>
			<script type="text/javascript">
  			<?php echo "alert('file is not in cvv');"; ?>
			</script>
			<?php 
			
		}
		$fileData = $model->fileDataInsert($files);
		 $app = JFactory::getApplication(); 
				
		$url = JRoute::_('index.php?option=com_osce&view=quesbanks');
				
		$app->redirect($url);

	}

}