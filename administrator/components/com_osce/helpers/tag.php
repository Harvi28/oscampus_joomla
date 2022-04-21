<?php

// No direct access to this file

defined('_JEXEC') or die('Restricted access');

abstract class TagHelper extends JHelperContent 
{
public static function addSubmenu($vName) 
	{
		//die("sdss");

		JHtmlSidebar::addEntry(
			JText::_('COM_OSCE_TAG'),
			'index.php?option=com_osce',
			$vName == 'tags'
		);

		JHtmlSidebar::addEntry(
			JText::_('COM_OSCE_QUESTIONBANK'),
			'index.php?option=com_osce&view=quesbanks&extension=com_osce',
			$vName == 'quesbanks'

		);
		JHtmlSidebar::addEntry(
			JText::_('COM_OSCE_IMPORT'),
			'index.php?option=com_osce&view=import&extension=com_osce',
			$vName == 'import'
		);

	}

}
