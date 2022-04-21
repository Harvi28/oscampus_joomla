<?php   
// include_once '/libraries/cms/form/rule/notequals.php';

defined('_JEXEC') or die('Restricted Access');
// jimport( 'joomla.form.form' );

?>

<form action="<?php echo JRoute::_('index.php?option=com_osce&layout=edit&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="adminForm" enctype="multipart/form-data" >
	<div class="form-horizontal">
        <fieldset class="adminform">
            <legend><?php echo JText::_('COM_OSCE_TAG_DETAILS'); ?></legend>
            <div class="row-fluid">
                <div class="span6">
                    <?php 
                        foreach($this->form->getFieldset() as $field) {

                            //echo $field->name;
                            
                            //echo "<pre>";print_r($field);
                        
                            echo $field->renderField();        
                        }
                    ?>
                </div>
            </div>
        </fieldset>
    </div>
    <input type="hidden" name="task" value="tag.edit" />
    <?php echo JHtml::_('form.token'); ?>
</form>