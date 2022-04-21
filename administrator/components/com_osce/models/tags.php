<?php  
defined('_JEXEC') or die('Restricted access');


class OsceModelTags extends JModelList
{
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'id',
				'title'
				
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
                ->from($db->quoteName('#__osce_tags','a'));
        // echo "<pre>";
        // print_r($query);
        // die();
        $search = $this->getState('filter.search');
       // echo "<pre>";
       // print_r($search);
       // die;

       if (!empty($search))
		{
			$like = $db->quote('%' . $search . '%');
			$query->where('title LIKE ' . $like);
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

	public function getTags(){
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select($db->quoteName(array('id', 'title')));
		$query->from($db->quoteName('#__osce_tags'));
		$db->setQuery($query);
		return $db->loadObjectList();

	}

	
}

?>