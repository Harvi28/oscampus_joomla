<?php

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

$inclideOsCampus = JPATH_ADMINISTRATOR . '/components/com_oscampus/include.php';
if (file_exists($inclideOsCampus)) {
    include_once $inclideOsCampus;
}else{
    die("Oscampus component is not found");
}

// var_dump(OscampusFactory::getApplication()->getName());


// echo "<pre>";
// print_r($tmp);
// die;


// OscampusModel::addIncludePath(OSCAMPUS_ADMIN.'/models');
// OscampusModel::getInstance('Lesson', 'OscampusModel')->save($data);

// die;


$controller = JControllerLegacy::getInstance('osce');

// Perform the Request task
$input = JFactory::getApplication()->input;
$controller->execute($input->getCmd('task'));

// Redirect if set by the controller
$controller->redirect();