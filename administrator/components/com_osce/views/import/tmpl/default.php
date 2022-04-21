<?php
$url ='components/com_osce/assets/questionBank.csv';
$model = $this->getModel('import');
$fileData = $model->fileData();
// echo "<pre>";
// print_r($fileData);
// die;


?>

<form action="index.php?option=com_osce&view=quesbanks&task=quesbanks.importques" method="post" id="adminForm" name="adminForm" enctype="multipart/form-data">
	<div id="j-sidebar-container" class="span2">
      		<?php echo JHtmlSidebar::render(); ?>
    </div>
		<div>
			<input type="file" name="jform1" value="" style="margin-left: 300px;">
			<input type="submit" name="submit" value="submit" style="width:100px" >
		</div>  
	<div >
		
		<h6 style="margin-left: 300px;"><a href="<?echo $url;?>" download="questionBank.csv">click here to download the file format</a> </h6>
	</div>

	<div class="fileDetail" style="margin-left: 950px;margin-top: 50px;">
		<h3>PREVIOUSLY ADDED FILES</h3>
		<table>
			<tbody>
				<?php foreach($fileData as $i=>$row): ?>
					<tr>
						<?php foreach($row as $j=>$srow): ?>
							
							<?php if($j == "filename"): ?>
								<?php $furl = 'components/com_osce/assets/'.$srow; ?>
								<td align="left">
								<a href = "<?echo $furl;?>" download="<?php echo $srow;?>">
								<?php echo $srow; ?>
								</a>
								</td>
							<?php endif; ?>

							
							
							
						<?php endforeach; ?>
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
	<!-- <div class="row-fluid" style="margin-top: 10px;">
		<div class="span6">
               <?php
                    // echo JLayoutHelper::render(
                    //     'joomla.searchtools.default',
                    //     array('view' => $this)
                    // );
                ?>
            </div>
		
	 </div>-->
</form>

