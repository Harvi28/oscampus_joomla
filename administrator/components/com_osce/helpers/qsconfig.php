<?php

$qsdata = JComponentHelper::getParams('com_osce');

$passing_score = $qsdata->get('passingScore');
$time_limit = $qsdata->get('timeLimit');

return array(
	'passing_score' = $passing_score,
	'time_limit'= $time_limit
			)

