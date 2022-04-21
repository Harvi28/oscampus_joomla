<?php
/**
 * @package   OSCampus
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2016-2021 Joomlashack.com. All rights reserved
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 *
 * This file is part of OSCampus.
 *
 * OSCampus is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * OSCampus is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with OSCampus.  If not, see <http://www.gnu.org/licenses/>.
 */

defined('_JEXEC') or die();

$user = OscampusFactory::getUser();

$signupType    = $user->guest ? 'signup.new' : 'signup.upgrade';
$signupPage    = OscampusHelper::normalizeUrl($this->getParams()->get($signupType));
$signupMessage = $signupPage
    ? 'COM_OSCAMPUS_LESSON_BECOME_A_MEMBER_TO_VIEW_LESSON'
    : 'COM_OSCAMPUS_LESSON_MEMBERSHIP_REQUIRED';

?>

<div id="signup-overlay">
    <div class="osc-overlay-inner">
        <h3><?php echo JText::_($signupMessage); ?></h3>
        <?php
        if ($signupPage) :
            echo '<div>'
                . JHtml::_(
                    'link',
                    $signupPage,
                    JText::_('COM_OSCAMPUS_LESSON_SIGNUP_HERE'),
                    'class="osc-btn osc-btn-main"'
                )
                . '</div>';
        endif;
        ?>
    </div>
</div>

<script>
    (function($) {
        $('#signup-overlay').appendTo('.osc-signup-box');
    })(jQuery);
</script>
