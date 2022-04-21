<?php
/** @var \Joomla\Registry\Registry $params */
$params = $displayData['params'];
?>
<div style="margin-top: <?php echo $params->get('top'); ?>px; margin-left: <?php echo $params->get('left'); ?>px; width: <?php echo $params->get('width'); ?>px; height: <?php echo $params->get('height'); ?>px; position: absolute; text-align: center; font-size: <?php echo $params->get('fontsize', 24); ?>px; font-family: <?php echo $params->get('font', 'roboto'); ?>; color: <?php echo $params->get('fontcolor', '#000'); ?>;">
    <?php echo $displayData['innerText']; ?>
</div>