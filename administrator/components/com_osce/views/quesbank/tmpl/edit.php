<?php   

defined('_JEXEC') or die('Restricted Access');
JHtml::_('formbehavior.chosen', 'select');

?>
<html>
<head>
    <script type="text/javascript">
        
    </script>
</head>
<body>
<form action="<?php echo JRoute::_('index.php?option=com_osce&layout=edit&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="adminForm" enctype="multipart/form-data">
	<div class="form-horizontal">
        <?php foreach ($this->form->getFieldsets() as $name => $fieldset): ?>
        <fieldset class="adminform">
            <legend><?php echo JText::_($fieldset->label); ?></legend>
            <div class="row-fluid">
                <div class="span6">
                    <?php  foreach($this->form->getFieldset() as $field): ?> 
                            <div class="control-group">
                                <div class="control-label"><?php echo $field->label; ?></div>
                                <div class="controls"><?php echo $field->input; ?></div>
                            </div>
                     <?php endforeach; ?>

                </div>
            </div>
        </fieldset>
        <?php endforeach; ?>

    </div>
    <input type="hidden" name="task" value="quesbank.edit" />
    <?php echo JHtml::_('form.token'); ?>
</form>
</body>
</html>