<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_osce
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');


class OsceController extends JControllerLegacy
{
	protected $default_view = 'tags';

    /**
     * @param bool  $cachable
     * @param array $urlparams
     *
     * @return JControllerLegacy
     * @throws Exception
     */
    public function display($cachable = false, $urlparams = array())
    {
        $app = OscampusFactory::getApplication();

        if ($app->input->getCmd('view', $this->default_view) == $this->default_view) {
            if ($alerts = OscampusHelper::getAlerts()) {
                OscampusFactory::getApplication()->enqueueMessage(join('<br>', $alerts), 'notice');
            }
        }

        return parent::display($cachable, $urlparams);
    }
}
