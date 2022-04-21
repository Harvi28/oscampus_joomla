<?php  

defined('_JEXEC') or die('Restricted Access');
//$listOrder     = $this->escape($this->state->get('list.ordering'));
//$listDirn      = $this->escape($this->state->get('list.direction'));
?>
<form action="index.php?option=com_osce&view=tags" method="post" id="adminForm" name="adminForm">
	<div id="j-sidebar-container" class="span2">
      		<?php echo JHtmlSidebar::render(); ?>
    </div>
    <div id="j-main-container" class="span10">
    <div class="row-fluid">
		<div class="span6">
                <?php
                    echo JLayoutHelper::render(
                        'joomla.searchtools.default',
                        array('view' => $this)
                    );
                ?>
            </div>
		
	</div>
	
	<table class="table table-striped table-hover">
		<thead>
		<tr>
			<th width="2%"><?php echo JText::_('COM_OSCE_NUM'); ?></th>
			<th width="3%">
				<?php echo JHtml::_('grid.checkall'); ?>
			</th>
			<th width="5%">
				<?php echo JText::_('COM_OSCE_PUBLISHED'); ?>
			</th>
			
			<th width="10%">
				<?php echo JHtml::_('searchtools.sort', 'COM_OSCE_ID', 'id'); ?>
			</th>
			
			
			<th width="15%">
				<?php echo JHtml::_('searchtools.sort', 'COM_OSCE_TITLE', 'title'); ?>
			</th>
			
			<th width="15%">
				<?php echo JHtml::_('searchtools.sort', 'COM_OSCE_CREATED_ON', 'created_on'); ?>
			</th>
			<th width="5%">
				<?php echo JHtml::_('searchtools.sort', 'COM_OSCE_CREATED_BY', 'created_by'); ?>
			</th>
			
		</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="9">
					<?php //echo $this->pagination->getListFooter(); ?>
				</td>
			</tr>
		</tfoot>
		<tbody>
		

			<?php if (!empty($this->items)) : ?>
				<?php foreach ($this->items as $i => $row) : 
					

					$link = JRoute::_('index.php?option=com_osce&view=tag&task=tag.edit&id=' . $row->id);
					
					
				?>

					<tr>
						<td>
							<?php echo $this->pagination->getRowOffset($i); ?>
						</td>

						<td>
							<?php echo JHtml::_('grid.id', $i, $row->id); ?>
						</td>
						<td align="center">
   						<?php echo JHtml::_('jgrid.published', $row->published, $i, 'tags.', true, 'cb'); ?>
   						</td>
						

						<td align="center">
							<?php echo $row->id; ?>
						</td>
						<td align="center">
							<a href="<?php echo $link; ?>">
								<?php echo $row->title; ?>
							</a>
						</td>
						
						<td align="center">
							
							<?php echo $row->created_on; ?>
							<br>
							<?php echo $row->modified_on; ?>

						</td>
						<td align="center">
							<?php echo JFactory::getUser($row->created_by)->name;?>
						</td>
						
						
				<?php endforeach; ?>
			<?php endif; ?>
		</tbody>
	</table>

	<input type="hidden" name="task" value=""/>
	<input type="hidden" name="boxchecked" value="0"/>
	
	<?php echo JHtml::_('form.token'); ?>
</form>