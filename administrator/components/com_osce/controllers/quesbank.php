<?php  

defined('_JEXEC') or die('Restricted access');


class OsceControllerQuesbank extends JControllerForm
{
    protected $text_prefix = 'COM_OSCE_QUESBANK';

    public function __construct($config = array())
    {
        //die("sdda");
        parent::__construct($config);


    }

    public function postSaveHook($model, $validData)
    {
        //echo "<pre>";
         // die("hiii");
        $item = $model->getItem();
        $entryId = $item->get('id');
        
        $idlistarr = $validData['tag_id'];
        $idlists = json_encode($idlistarr);
        // echo "<pre>";
        // print_r($idlistarr);
        // print_r($entryId);
        // die;

        
        foreach($validData['tag_id'] as $i=>$idlist)
        {
            
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $query->select('*')
                    ->from($db->quoteName('#__osce_tags'))
                    ->where($db->quoteName('id') . ' LIKE ' . $db->quote($idlist));
                // echo $query;
                // die;
            $db->setQuery($query);
            $tcolumns= $db->loadColumn();  
            
            $query1 = $db->getQuery(true);
            foreach($tcolumns as $column)
            {
            $columns=array('quesbank_id','tag_id');
            $values=array($entryId,$column);

            $query1
                ->insert($db->quoteName('#__osce_quesbank_tags'))
                ->columns($db->quoteName($columns))
                ->values(implode(',',$values));
           
            $db->setQuery($query1);
            // die;
            $result = $db->execute();


        }
    }

    }
}