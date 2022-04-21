<?php
/**
 *
 * @package     Joomla.Administrator
 * @subpackage  com_osce
 */

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

defined('_JEXEC') or die();
// Set some global property
$document = JFactory::getDocument();

$inclideOsCampus = JPATH_ADMINISTRATOR . '/components/com_oscampus/include.php';
if (file_exists($inclideOsCampus)) {
    include_once $inclideOsCampus;
}else{
    die("Oscampus component is not found");
}

// Access check.
if (!OscampusFactory::getUser()->authorise('core.manage', 'com_oscampus')) {
    throw new Exception(Text::_('JERROR_ALERTNOAUTHOR'), 404);
}
HTMLHelper::_('behavior.tabstate');
//if (!JFactory::getUser()->authorise('core.manage', 'com_osce'))
//{
// 	throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'));
//}

// Require helper file
JLoader::register('TagHelper', JPATH_COMPONENT . '/helpers/tag.php');
JLoader::register('QuesbankHelper', JPATH_COMPONENT . '/helpers/quesbank.php');
JLoader::register('ImportHelper', JPATH_COMPONENT . '/helpers/import.php');



// Get an instance of the controller prefixed by OSCE
$controller = JControllerLegacy::getInstance('osce');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();