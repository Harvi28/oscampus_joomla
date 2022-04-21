<?php
/**
 * @package   OSCampus
 * @contact   www.joomlashack.com, help@joomlashack.com
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

defined('_JEXEC') or die();

/**
 * Class Font
 *
 * @package Oscampus\Font
 *
 * @property string $copyright
 * @property string $family
 * @property string $subfamily
 * @property string $id
 * @property string $fullname
 * @property string $version
 * @property string $postscriptName
 * @property string $trademark
 * @property string $manufacturer
 * @property string $designer
 * @property string $description
 * @property string $vendorUrl
 * @property string $designerUrl
 * @property string $license
 * @property string $licenseUrl
 * @property string $filepath
 * @property string $hash
 */
class Font
{
    const COPYRIGHT       = 0;
    const FONT_FAMILY     = 1;
    const FONT_SUBFAMILY  = 2;
    const ID              = 3;
    const FULL_NAME       = 4;
    const VERSION         = 5;
    const POSTSCRIPT_NAME = 6;
    const TRADEMARK       = 7;
    const MANUFACTURER    = 8;
    const DESIGNER        = 9;
    const DESCRIPTION     = 10;
    const VENDOR_URL      = 11;
    const DESIGNER_URL    = 12;
    const LICENSE         = 13;
    const LICENSE_URL     = 14;

    /**
     * @var string[]
     */
    protected $data = null;

    /**
     * @var string
     */
    protected $filepath = null;

    /**
     * Font constructor.
     *
     * @param string $filepath
     * @param array  $data
     */
    public function __construct($filepath, $data)
    {
        $this->filepath = $filepath;
        $this->data     = $data;
    }

    /**
     * @param string $name
     *
     * @return mixed
     */
    public function __get($name)
    {
        $method = 'get' . ucfirst($name);
        if (method_exists($this, $method)) {
            return $this->$method();
        }

        return null;
    }

    /**
     * @param int $index
     *
     * @return mixed
     */
    protected function getValue($index)
    {
        return isset($this->data[$index]) ? $this->data[$index] : null;
    }

    /**
     * @return string
     */
    public function getFilepath()
    {
        return $this->filepath;
    }

    /**
     * @return string
     */
    public function getHash()
    {
        return md5($this->getId());
    }

    /**
     * @return string
     */
    public function getCopyright()
    {
        return $this->getValue(static::COPYRIGHT);
    }

    /**
     * @return string
     */
    public function getFamily()
    {
        return $this->getValue(static::FONT_FAMILY);
    }

    /**
     * @return string
     */
    public function getSubfamily()
    {
        return $this->getValue(static::FONT_SUBFAMILY);
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->getValue(static::ID);
    }

    /**
     * @return string
     */
    public function getFullname()
    {
        return $this->getValue(static::FULL_NAME);
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        return $this->getValue(static::VERSION);
    }

    /**
     * @return string
     */
    public function getPostscriptName()
    {
        return $this->getValue(static::POSTSCRIPT_NAME);
    }

    /**
     * @return string
     */
    public function getTradmark()
    {
        return $this->getValue(static::TRADEMARK);
    }

    /**
     * @return string
     */
    public function getManufacturer()
    {
        return $this->getValue(static::MANUFACTURER);
    }

    /**
     * @return string
     */
    public function getDesigner()
    {
        return $this->getValue(static::DESIGNER);
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->getValue(static::DESCRIPTION);
    }

    /**
     * @return string
     */
    public function getVendorUrl()
    {
        return $this->getValue(static::VENDOR_URL);
    }

    /**
     * @return string
     */
    public function getDesignerUrl()
    {
        return $this->getValue(static::DESIGNER_URL);
    }

    /**
     * @return string
     */
    public function getLicense()
    {
        return $this->getValue(static::LICENSE);
    }

    /**
     * @return string
     */
    public function getLicenseUrl()
    {
        return $this->getValue(static::LICENSE_URL);
    }
}
