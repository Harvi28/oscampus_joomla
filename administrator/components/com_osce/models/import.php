<?php  
defined('_JEXEC') or die('Restricted access');


class OsceModelImport extends JModelList
{
	public function getListQuery()
	{
		
		$db    = JFactory::getDbo();
		
		$query = $db->getQuery(true);

		
		$query->select('*')
                ->from($db->quoteName('#__osce_fileinfo'));
                
		$udate = $this->getState('filter.uploaded_on');

  		if (!empty($udate))
		{
			$like = $db->quote('%' . $udate . '%');
			$query->where('uploaded_on LIKE ' . $like);

		}
		
        return $query;
	}


	public function data($csvDa)
	{
		
		$dCount= count($csvDa);
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select(array('id','title'));
		$query->from($db->quoteName('#__osce_tags'));
		$db->setQuery($query);
		$tagsR = $db->loadAssocList('id');

		 $tags = [];
		 foreach($tagsR as $tsgK=>$tagr){
		 	$tags[$tsgK] = $tagr['title'];
		 }
		 

		foreach($csvDa as $csvDK=>$singleEntry)
		{
			$tagsId = [];
			// echo "<pre>";
			// print_r($singleEntry['Tags']);
			// die;
			foreach($singleEntry['Tags'] as $titleKey=>$title)
			{
				$title = str_replace(' ','',$title);
				
			

				if(!in_array($title, $tags))
				{
					$insertedId = $this->insertTag($title);
					$csvDa[$csvDK]['Tags'][$titleKey] = $insertedId;
					$tags[$insertedId] = $title;
				}
				
				else
				{
					$csvDa[$csvDK]['Tags'][$titleKey] = array_search($title, $tags);
	
				}



			}
	
		}

		return $csvDa;
	}
	private function insertTag($tagName)
	{
		
			$modified_on = date('Y-m-d H:i:s');
			$created_by = JFactory::getUser()->id;

			$columns = array('title','modified_on','created_by');

			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->insert($db->quoteName('#__osce_tags'))
					  ->columns($columns)
					  ->values("'".$tagName."','".$modified_on."','".$created_by."'");
			
			$db->setQuery($query);
			$insertedList = $db->execute();
			// echo "<pre>";
			// print_r($insertedList);
			// die;
			$new_row_id = $db->insertid();
			return $new_row_id;
		// }
	}


	public function generateQuestion($test)
	{
		
		
		foreach($test as $t)
		{
			
			$titld = $t['Tags'];
			// $t['title'] = json_encode($t['title']);
			$modified_on = date('Y-m-d H:i:s');
			$jstagD = $this->tagD($titld);
			$created_by = JFactory::getUser()->id;

			$jstagD = json_encode($jstagD);
			
			$columns = array('tag_id','ques','opt1','opt2','opt3','opt4','correct_ans','modified_on','created_by');

			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->insert($db->quoteName('#__osce_quesbanks'))
					  ->columns($db->quoteName($columns))
					  ->values("'".$jstagD."', '".$t['Question']."', '".$t['Option 1']."', '".$t['Option 2']."', '".$t['Option 3']."', '".$t['Option 4']."', '".$t['Correct Ans']."','".$modified_on."','".$created_by."'");

				
			
			$db->setQuery($query);
		$insertedQuestion = $db->execute();

		$lastInsertedID = $db->insertid();
		$mapData = $this->addInMap($lastInsertedID);
		// die;
		// return $lastInsertedID;
		
		}

	}

	private function tagD($titld)
	{
		
		$db = JFactory::getDbo();
			$query = $db->getQuery(true);
		$query->select('id')
                    ->from($db->quoteName('#__osce_tags'))
                    ->where($db->quoteName('id').' IN (' . implode(',', $titld) . ')');

            // echo $query;
            $db->setQuery($query);
            $tcola= $db->loadColumn();
            
        // } 
        // echo "<pre>";
        // print_r($tcola);
        // die;
        return $tcola;
       
	}
	private function tags()
	{
		$tagcol = array();
		
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('title')
              ->from($db->quoteName('#__osce_tags'));
                   
            // echo $query;
            $db->setQuery($query);
            $tagcol= $db->loadColumn();
            
       
        return $tagcol;
       
	}

	public function addInMap($lastInsertedID)
	{
			// echo "<pre>";
			print_r($lastInsertedID);
			// die;
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select($db->quoteName(array('a.id', 'a.tag_id')));
			$query->from($db->quoteName('#__osce_quesbanks','a'));
			$query->where($db->quoteName('id') . ' LIKE ' . $db->quote($lastInsertedID));
			// echo $query;
			// die("dfsd");
			$db->setQuery($query);
			$results = $db->loadAssocList();
			
			foreach($results as $key=>$val)
			{
				$qid = $val['id'];
				
				foreach($val as $skey=>$sval)
				{

					$sval = json_decode($sval);
					// echo $sval;
					 foreach($sval as $mkey=>$mval)
					 {
					 	// echo $mval;
					 	$db    = JFactory::getDbo();
						$query = $db->getQuery(true);
						$columns = array('quesbank_id','tag_id');
						$query->insert($db->quoteName('#__osce_quesbank_tags'))
							   ->columns($db->quoteName($columns))
					  			->values("'".$qid."', '".$mval."'");
					  	$db->setQuery($query);
						$finald = $db->execute();
						// echo $finald;
						// die;
						
					  	// return $finald;
					 }

				}
			}	
	}


	public function fileDataInsert($files)
	{
		// echo "<pre>";
		// print_r($files);
		// die;
		$fileName = $files['name'];
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		// $columns = array('filename');
		$query->insert($db->quoteName('#__osce_fileinfo'))
			  ->columns($db->quoteName('filename'))
			  ->values("'".$fileName."'");
		// echo $query;
		// die;
		$db->setQuery($query);
		$finald = $db->execute();
		// $filedata = $this->fileData();
		$target_dir = 'components/com_osce/assets/';
		$target_file = $target_dir . basename($files['name']);
		if (move_uploaded_file($files['tmp_name'], $target_file)) 
		{
			// die("hii");
    		echo "The file ". htmlspecialchars( basename( $_FILES["fileToUpload"]["name"])). " has been uploaded.";
  		}


	}

	public function fileData()
	{
		$db    = JFactory::getDbo();
		$query1 = $db->getQuery(true);
		$query1->select(array('id','filename','uploaded_on'));
		$query1->from($db->quoteName('#__osce_fileinfo'));
		// $query1->where($db->quoteName('id') . ' LIKE ' . $db->quote($lastInsertedID));
		// echo $query1;
		// die;
		$db->setQuery($query1);
		$results = $db->loadAssocList();

		return $results;
	}
	
	
}
