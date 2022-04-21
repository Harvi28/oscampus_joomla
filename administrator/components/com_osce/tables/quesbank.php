<?php

defined('_JEXEC') or die('Restricted access');

class OsceTableQuesbank extends JTable
{
	function __construct(&$db)
	{
		parent::__construct('#__osce_quesbanks', 'id', $db);
	}

	function bind( $array, $ignore = '' )
    {
        if (key_exists( 'tag_id', $array ) && is_array( $array['tag_id'] )) {
	        $array['tag_id'] = implode( ',', $array['tag_id'] );
        }

        return parent::bind( $array, $ignore );
    }

    function check() {
		// $data=$this->getItem();
		$ques= $this->ques;
		$id = $this->id;
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		if($id !==0 && $id!==" ")
		{
			// die;
			$query->select('ques')
					->from($db->quoteName('#__osce_quesbanks'))
					->where($db->quoteName('ques').'LIKE'.$db->quote($ques))
					->where($db->quoteName('id').'NOT IN ('.$db->quote($id).')');
			$db->setQuery($query);
			$results = $db->loadColumn();
		}
		
		// else
		// {
		// 	// die("id");
		// 	$id = 0;
		// 	$query->select('ques')
		// 			->from($db->quoteName('#__osce_quesbanks'))
		// 			->where($db->quoteName('ques').'LIKE'.$db->quote($ques))
		// 			->where($db->quoteName('id').'NOT IN ('.$db->quote($id).')');
		// 	$db->setQuery($query);
		// 	$results = $db->loadColumn();
		// }
		
		

        if (in_array($ques,$results)) {
           JError::raiseError(500, 'question already exists');
        	
           // die("ques is exist");
           return false;
        } 
     
        return true;
    }

}