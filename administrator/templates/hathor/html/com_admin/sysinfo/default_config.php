<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  Template.hathor
 *
 * @copyright   (C) 2012 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
?>
<fieldset class="adminform">
	<legend><?php echo JText::_('COM_ADMIN_CONFIGURATION_FILE'); ?></legend>
	<table class="adminlist">
		<thead>
			<tr>
				<th width="300">
					<?php echo JText::_('COM_ADMIN_SETTING'); ?>
				</th>
				<th>
					<?php echo JText::_('COM_ADMIN_VALUE'); ?>
				</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="2">&#160;</td>
			</tr>
		</tfoot>
		<tbody>
			<?php foreach ($this->config as $key => $value):?>
			<tr>
				<td>
					<?php echo $key;?>
				</td>
				<td>
					<?php echo htmlspecialchars($value, ENT_QUOTES);?>
				</td>
			</tr>
			<?php endforeach;?>
		</tbody>
	</table>
</fieldset>
