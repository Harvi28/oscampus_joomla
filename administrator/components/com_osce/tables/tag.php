<?php

defined('_JEXEC') or die('Restricted access');

class OsceTableTag extends JTable
{
	function __construct(&$db)
	{
		parent::__construct('#__osce_tags', 'id', $db);
	}

	function check() {
		
		// die;
		$title= $this->title;
		$db = JFactory::getDBO();
				$query = $db->getQuery(true);

		if($id !==0 &&$id!==" ")
		{
			// die("jj");
			$query->select('title')
					->from($db->quoteName('#__osce_tags'))
					->where($db->quoteName('title').'LIKE'.$db->quote($title))
					->where($db->quoteName('id').'NOT IN ('.$db->quote($id).')');
					
			$db->setQuery($query);
			$results = $db->loadColumn();
		}
		
        if (in_array($title,$results)) {
           JError::raiseError(500, 'tag already exists');
        	// die("ss");
           return false;
        } 
      
        return true;
    }
}