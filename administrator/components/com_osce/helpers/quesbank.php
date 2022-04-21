<?php

// No direct access to this file

defined('_JEXEC') or die('Restricted access');

abstract class QuesbankHelper extends JHelperContent 
{
public static function addSubmenu($submenu) 
	{
		//die("sdss");

		JHtmlSidebar::addEntry(
			JText::_('COM_OSCE_TAG'),
			'index.php?option=com_osce&view=tags&extension=com_osce',
			$submenu == 'tags'
		);

		JHtmlSidebar::addEntry(
			JText::_('COM_OSCE_QUESTIONBANK'),
			'index.php?option=com_osce',
			$submenu == 'quesbanks'

		);


		JHtmlSidebar::addEntry(
			JText::_('COM_OSCE_IMPORT'),
			'index.php?option=com_osce&view=import&extension=com_osce',
			$submenu == 'import'
		);

	}

}
