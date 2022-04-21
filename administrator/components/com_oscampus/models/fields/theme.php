<?php
/**
 * @package   OSCampus
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2018-2021 Joomlashack.com. All rights reserved
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

JFormHelper::loadFieldClass('List');

class OscampusFormFieldTheme extends JFormFieldList
{
    protected function getOptions()
    {
        $language = JFactory::getLanguage();

        $options = $this->getOptionsFromPath(JPATH_SITE . '/media/com_oscampus/css/themes');
        foreach ($options as $file) {
            $text = 'COM_OSCAMPUS_OPTION_THEME_' . str_replace(' ', '_', $file->value);
            if ($language->hasKey($text)) {
                $text = JText::_($text);
            } else {
                $text = $this->formatName($file->value);
            }
            $file->text = $text;
        }

        $coreValues = array_map(
            function ($row) {
                return $row->value;
            },
            $options
        );

        $options = array_merge(
            $options,
            $this->getTemplateOptions($coreValues)
        );

        // Sort by alpha name making sure default is always first
        usort($options, function ($a, $b) {
            if ($a->value == 'default') {
                return -1;
            } elseif ($b->value == 'default') {
                return 1;
            }

            return (int)($a->text > $b->text);
        });

        return $options;
    }

    /**
     * @param string[] $excludeValues
     *
     * @return object[]
     */
    protected function getTemplateOptions(array $excludeValues)
    {
        $db      = JFactory::getDbo();
        $client  = JApplicationHelper::getClientInfo('site', true);
        $options = array();

        $stylesQuery = $db->getQuery(true)
            ->select(
                array(
                    's.id',
                    's.title',
                    'e.name AS name',
                    's.template'
                )
            )
            ->from('#__template_styles AS s')
            ->leftJoin('#__extensions AS e ON e.element=s.template')
            ->where(
                array(
                    's.client_id = ' . (int)$client->id,
                    'e.enabled = 1',
                    $db->quoteName('e.type') . ' = ' . $db->quote('template')
                )
            )
            ->order(
                array(
                    'template ASC',
                    'title ASC'
                )
            );

        $styles = $db->setQuery($stylesQuery)->loadObjectList();
        if ($styles) {
            foreach ($styles as $style) {
                $templatePath = $client->path . '/templates/' . $style->template;

                $themePath = $templatePath . '/css/com_oscampus/themes';
                if (is_dir($themePath)) {
                    $themeOptions = $this->getOptionsFromPath($themePath);
                    foreach ($themeOptions as $themeOption) {
                        if (!in_array($themeOption->value, $excludeValues)) {
                            $themeOption->text = sprintf(
                                '%s [%s]',
                                $this->formatName($themeOption->value),
                                $this->formatName($style->name)
                            );

                            $options[] = $themeOption;
                        }
                    }
                }
            }
        }

        return $options;
    }

    /**
     * Format a filename into a more human-friendly aesthetic
     *
     * @param string $text
     *
     * @return string
     */
    protected function formatName($text)
    {
        return ucwords(str_replace('_', ' ', $text));
    }

    /**
     * @param string $path
     *
     * @return object[]
     */
    protected function getOptionsFromPath($path)
    {
        $directory = new DirectoryIterator($path);
        $options   = array();

        while ($directory->valid()) {
            $file = $directory->current();

            if ($file->getExtension() == 'css') {
                $value     = $file->getBasename('.css');
                $options[] = JHtml::_('select.option', $value, $value);
            }

            $directory->next();
        }

        return $options;
    }
}
