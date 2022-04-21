<?php
/**
 * @package   OSCampus
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @author    Bill Tomczak <bill@joomlashack.com>
 * @copyright 2019 Joomlashack.com. All rights reserved
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

namespace Oscampus\Font;

use DirectoryIterator;

defined('_JEXEC') or die();

class Manager
{
    /**
     * @var array[Font[]]
     */
    protected $fonts = array();

    /**
     * @param string $directory
     *
     * @return Font[]
     */
    public function getFonts($directory = null)
    {
        if ($directory === null) {
            $directory = JPATH_SITE . '/media/com_oscampus/fonts';
        }

        return $this->loadFonts($directory);
    }

    /**
     * @param string $directory
     *
     * @return Font[]
     */
    protected function loadFonts($directory)
    {
        $key = md5($directory);
        if (!isset($this->fonts[$key])) {
            $this->fonts[$key] = array();

            $files = $this->getFontFiles($directory);
            foreach ($files as $file) {
                if ($font = $this->getInfo($file)) {
                    $this->fonts[$key][] = $font;
                }
            }
        }

        return $this->fonts[$key];
    }

    /**
     * @param string $directory
     *
     * @return string[]
     */
    protected function getFontFiles($directory)
    {
        $fontList = array();

        if ($directory && is_dir($directory)) {
            $files = new DirectoryIterator($directory);

            while ($files->valid()) {
                if ($files->isDir()) {
                    if (!$files->isDot()) {
                        $fontList = array_merge(
                            $fontList,
                            $this->getFontFiles($files->getPathname())
                        );
                    }

                } elseif (in_array($files->getExtension(), array('ttf', 'otf'))) {
                    $fontList[] = $files->getPathname();
                }
                $files->next();
            }
        }

        return $fontList;
    }

    /**
     * @param string $family
     * @param string $subfamily
     * @param string $directory
     *
     * @return Font
     */
    public function getFont($family, $subfamily = null, $directory = null)
    {
        $fonts     = $this->getFonts($directory);
        $subfamily = $subfamily ?: 'regular';

        foreach ($fonts as $font) {
            if (
                !strcasecmp($font->family, $family)
                && !strcasecmp($font->subfamily, $subfamily)
            ) {
                return $font;
            }
        }

        return null;
    }

    /**
     * @param string $hash
     * @param string $directory
     *
     * @return Font
     */
    public function getFontByHash($hash, $directory = null)
    {
        $fonts = $this->getFonts($directory);

        foreach ($fonts as $font) {
            if ($font->hash === $hash) {
                return $font;
            }
        }

        return null;
    }

    /**
     * @param string $filePath
     *
     * @return Font
     */
    public function getInfo($filePath)
    {
        if ($filePath && preg_match('/\.(ttf|otf)$/', $filePath) && is_file($filePath)) {
            $fh   = fopen($filePath, 'r');
            $data = fread($fh, filesize($filePath));
            fclose($fh);

            $fontData   = array();
            $tableCount = $this->hexToDec(substr($data, 4, 2));

            for ($i = 0; $i < $tableCount; $i++) {
                if ($this->getTag($data, $i) == 'name') {
                    $start       = 12 + $i * 16 + 8;
                    $tableOffset = $this->hexToDec(substr($data, $start, 4));

                    $storageOffset = $this->hexToDec(substr($data, $tableOffset + 4, 2));
                    $nameRecords   = $this->hexToDec(substr($data, $tableOffset + 2, 2));
                    break;
                }
            }

            if (!empty($nameRecords)) {
                if (!empty($storageOffset) && !empty($tableOffset)) {
                    $storageStart = $storageOffset + $tableOffset;

                    for ($j = 0; $j < $nameRecords; $j++) {
                        $nameId     = $this->getData($data, $tableOffset + 6, $j);
                        $itemLength = $this->getData($data, $tableOffset + 8, $j);
                        $itemOffset = $this->getData($data, $tableOffset + 10, $j);

                        if (!empty($nameId) && !isset($fontData[$nameId])) {
                            $fontData[$nameId] = '';

                            for ($l = 0; $l < $itemLength; $l++) {
                                $position = $storageStart + $itemOffset + $l;

                                if (ord($data[$position]) == '0') {
                                    continue;

                                } else {
                                    $fontData[$nameId] .= $data[$position];
                                }
                            }
                        }
                    }

                    if ($fontData) {
                        $font = new Font($filePath, $fontData);

                        return $font;
                    }
                }
            }
        }

        return null;
    }

    /**
     * @param string $data
     * @param int    $index
     *
     * @return string
     */
    protected function getTag($data, $index)
    {
        $start = 12 + ($index * 16);

        return substr($data, $start, 4);
    }

    /**
     * @param string $data
     * @param int    $base
     * @param int    $index
     * @param int    $length
     *
     * @return int
     */
    protected function getData($data, $base, $index = 0, $length = 2)
    {
        $start = $base + 6 + ($index * 12);

        return $this->hexToDec(substr($data, $start, $length));
    }

    /**
     * @param string $string
     *
     * @return int
     */
    protected function hexToDec($string)
    {
        $hexString = '';
        for ($i = 0; $i < strlen($string); $i++) {
            $hexString .= str_pad(dechex(ord($string[$i])), 2, '0', STR_PAD_LEFT);
        }

        return hexdec($hexString);
    }
}
