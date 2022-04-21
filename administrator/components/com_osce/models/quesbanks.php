<?php  
defined('_JEXEC') or die('Restricted access');


class OsceModelQuesbanks extends JModelList
{
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'id',
				'ques',
				'opt1',
				'opt2',
				'opt3',
				'opt4',
				'tag_id',
				'created_on',
				'created_by',
				'correct_ans',
				'modify_at'
				
			);
		}

		parent::__construct($config);
	}

	public function getListQuery()
	{
		//die("ss");
		$db    = JFactory::getDbo();
		// echo "<pre>";
		// print_r($db);
		// die("fd");
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('*')
                ->from($db->quoteName('#__osce_quesbanks','a'));
       

       $search = $this->getState('filter.search');
       // echo "<pre>";
       // print_r($search);
       // die;

       if (!empty($search))
		{
			$like = $db->quote('%' . $search . '%');
			$query->where('ques LIKE ' . $like);
		}


       $cdate = $this->getState('filter.created_on');
       // echo "<pre>";
       // print_r($cdate);
       // die;


       if (!empty($cdate))
		{
			$like = $db->quote('%' . $cdate . '%');
			$query->where('created_on LIKE ' . $like);

		}

		$published = $this->getState('filter.published');


		if (is_numeric($published))
		{
			$query->where('a.published = ' . (int) $published);
		}
		elseif ($published === '')
		{
			$query->where('(a.published IN (0, 1))');
		}


		return $query;
	}

	public function tags($tag_id)
	{
		
		$tagcol = array();
		foreach($tag_id as $t_key=>$t_id)
		{
			// echo $t_key;
			// echo $t_id; 
			
			$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('title')
              ->from($db->quoteName('#__osce_tags'))
               ->where($db->quoteName('id').'LIKE'.$db->quote($t_id));    
            // echo $query;
            // die;
            $db->setQuery($query);
            $result= $db->loadResult();
         	array_push($tagcol,$result);
        }
		
		 return $tagcol;
	}
}

?>