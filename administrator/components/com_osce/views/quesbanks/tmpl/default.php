<?php  

defined('_JEXEC') or die('Restricted Access');

?>
<form action="index.php?option=com_osce&view=quesbanks" method="post" id="adminForm" name="adminForm">
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
			<th width="1%"><?php echo JText::_('COM_OSCE_NUM'); ?></th>
			<th width="1%">
				<?php echo JHtml::_('grid.checkall'); ?>
			</th>
			<th width="2%">
				<?php echo JText::_('COM_OSCE_PUBLISHED'); ?>
			</th>
			
			<th width="5%">
				<?php echo JHtml::_('searchtools.sort', 'COM_OSCE_ID', 'id'); ?>
			</th>
			<th width="10%">
				<?php echo JHtml::_('searchtools.sort', 'COM_OSCE_QUESTIONS', 'ques'); ?>
			</th>
			
			<th width="10%">
				<?php echo JHtml::_('searchtools.sort', 'COM_OSCE_OPTION1', 'opt1'); ?>
			</th>
			<th width="10%">
				<?php echo JHtml::_('searchtools.sort', 'COM_OSCE_OPTION2', 'opt2'); ?>
			</th>
			<th width="10%">
				<?php echo JHtml::_('searchtools.sort', 'COM_OSCE_OPTION3', 'opt3'); ?>
			</th>
			<th width="10%">
				<?php echo JHtml::_('searchtools.sort', 'COM_OSCE_OPTION4', 'opt4'); ?>
			</th>
			<th width="10%">
				<?php echo JHtml::_('searchtools.sort', 'COM_CORRECT_ANS', 'correct_ans'); ?>
			</th>
			<th width="10%">
				<?php echo JHtml::_('searchtools.sort', 'COM_TAG_ID', 'tag_id'); ?>
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
				<td colspan="15">
					<?php echo $this->pagination->getListFooter(); ?>
				</td>
			</tr>
		</tfoot>
		<tbody>
			<?php if (!empty($this->items)) : ?>
				<?php foreach ($this->items as $i => $row) :
					$link = JRoute::_('index.php?option=com_osce&view=quesbank&task=quesbank.edit&id=' . $row->id);

					?>

				<tr>
						<td>
							<?php echo $this->pagination->getRowOffset($i); ?>
						</td>
						
						<td>
							<?php echo JHtml::_('grid.id', $i, $row->id); ?>
						</td>
						<td align="center">
							<?php echo JHtml::_('jgrid.published', $row->published, $i, 'quesbanks.', true, 'cb'); ?>
						</td>
						<td align="center">
							<?php echo $row->id; ?>
						</td>

						<td align="center">
							<a href="<?php echo $link; ?>">

							<?php echo $row->ques; ?>
						</a>
						</td>

						<td align="center">
							<?php echo $row->opt1; ?>
						</td>

						<td align="center">
							<?php echo $row->opt2; ?>
						</td>

						<td align="center">
							<?php echo $row->opt3; ?>
						</td>

						<td align="center">
							<?php echo $row->opt4; ?>
						</td>
						<td align="center">
							<?php echo $row->correct_ans; ?>
						</td>
						<td>
							<?php $model = $this->getModel('quesbanks');
							$tag_id = $row->tag_id;
							$tag_id = json_decode($tag_id);
							

							// $tag_id = array($tag_id);
							$tests = $model->tags($tag_id);
							$tests = json_encode($tests);
							echo $tests;
							
							?>
							
						</td>
						<td align="center">
							
							<?php echo $row->created_on; ?>
							<br>
							<?php echo $row->modified_on; ?>
						</td>
						<td align="center">
							<?php echo JFactory::getUser($row->created_by)->name;?>
						</td>
						
				</tr>

				<?php endforeach; ?>
			<?php endif; ?>
		</tbody>

			
	</table>

	<input type="hidden" name="task" value=""/>
	<input type="hidden" name="boxchecked" value="0"/>
	
	<?php echo JHtml::_('form.token'); ?>
</form>