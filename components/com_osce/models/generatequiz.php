<?php  
// die;

// require_once JPATH_COMPONENT_ADMINISTRATOR . '/config.xml';
// $this->config = require_once JPATH_COMPONENT_ADMINISTRATOR . '/helpers/qsconfig.php';




class OsceModelGeneratequiz extends JModelList
{
	// die;

	public function getTable($type = 'Generatequiz', $prefix = 'GenerateTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	public function getForm($data = array(), $loadData = true)
	{
		// die("sdsf");
		// Get the form.
		$form = $this->loadForm('com_osce.generatequiz','generatequiz',array('control' => 'jform','load_data' => $loadData));
		//echo "<pre>";
		//print_r($form);
		//die("sdf");

		if (empty($form))
		{
			//die("sdf");
			return false;
		}

		return $form;
	}

	/*
		$tag => array
		$limiy => number
	*/


	public function getTags($tags)
	{
		 // die;
		
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select($db->quoteName(array('quesbank_id')));
		$query->from($db->quoteName('#__osce_quesbank_tags'));
		$query->where($db->quoteName('tag_id'). ' IN (' . implode(',', $tags) . ')');
			// ->where($db->quoteName('published') . " = " . $db->quote(1));

		// echo $query;
		// die;
		$db->setQuery($query);
		$results = $db->loadColumn();
		// echo "<pre>";
		// print_r($results);
		// die;
		return $results;
	}
	public function getQuebyTagsId($questionTag, $limit){
		
		// echo "<pre>";
		// print_r($questionTag);
		// die;
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select($db->quoteName(array('id','published','ques','opt1','opt2','opt3','opt4','correct_ans')));
		$query->from($db->quoteName('#__osce_quesbanks'));
		$query->where($db->quoteName('id').' IN (' . implode(',', $questionTag) . ')')
		      ->where($db->quoteName('published') . " = " . $db->quote(1));
		$query->order('RAND()');
		$query->setLimit($limit);
		
		$db->setQuery($query);
		$results = $db->loadAssocList();
		// echo "<pre>";
		// print_r($results);
		// die;
		
		
		$resutco = count($results);
		$length = 25;
		$randomData = str_split('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ01234785957abnjskienkhd');
		shuffle($randomData);
		$finalArray = array_slice($randomData,0,6);
		$finalString = implode('',$finalArray);

	$qsdata = JComponentHelper::getParams('com_osce');
$passing_score = $qsdata->get('passingScore');
$time_limit = $qsdata->get('timeLimit');
$lessonName = $qsdata->get('lesson');

						// echo "<pre>";
						// print_r($lessonName);
						// die;

		$questionData = [
	"id" => 0,
	"title" => $finalString,
	"alias" => $finalString,
	"courses_id" => $lessonName,
	"module_title" => "module 1",
	"type" => "quiz",
	"published" => 1,

	"publish_up" => "2022 - 03 - 28 12: 01: 00",
	"publish_down" => "2060 - 03 - 31 12: 01: 00",
	"access" => 1,
	"description" => "",
	"metadata" => [
		"title" => "",
		"description" => "",
	],
	"content" => [
		"quizLength" => -1,
		"timeLimit" => $time_limit,
		"questions" => [
			[
				"text" => 'hiiii',
				"answers"=> [
					"0" => "harvi",
					"1" => "hv",
					"2" => "hahaharvi",
					"3" => "none",
				],

				"correct" => 2,
			]

		]

	],
	"tags" => "",
];

			for($i=0;$i<$resutco;$i++)
			{
				 
				$questionData['content']['questions'][$i]['text']=$results[$i]['ques'];
				$questionData['content']['questions'][$i]['answers'][0]=$results[$i]['opt1'];
				$questionData['content']['questions'][$i]['answers'][1]=$results[$i]['opt2'];
				$questionData['content']['questions'][$i]['answers'][2]=$results[$i]['opt3'];
				$questionData['content']['questions'][$i]['answers'][3]=$results[$i]['opt4'];
				$cra = $results[$i]['correct_ans'];
				$questionData['content']['questions'][$i]['correct']=$cra-1;
			
					
			}
			
		// echo "<pre>";
		// print_r($questionData);
		// die;
		return $questionData;

	}

	public function lessonAlias($lessonName)
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select($db->quoteName('alias'));
		$query->from($db->quoteName('#__oscampus_courses'));
		$query->where($db->quoteName('id').' LIKE ' . $db->quote($lessonName));
		$db->setQuery($query);
		$results = $db->loadResult();
		return $results;
	}
}


