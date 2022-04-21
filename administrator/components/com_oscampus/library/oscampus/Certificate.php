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

namespace Oscampus;

use DateTime;
use Exception;
use JDatabaseDriver;
use JDatabaseQuery;
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Filesystem\Path;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\User\User;
use Joomla\Registry\Registry;
use Oscampus\Font\Manager as FontManager;
use OscampusEventDispatcher;
use OscampusFactory;
use OscampusTable;
use OscampusTableCertificates;
use OscampusTableCourses;

defined('_JEXEC') or die();

/**
 * Class Certificate
 *
 * @package Oscampus
 */
class Certificate extends AbstractBase
{
    /**
     * @var CMSApplication
     */
    protected $app = null;

    /**
     * @var FontManager
     */
    protected $fontManager = null;

    /**
     * @var Registry
     */
    protected $params = null;

    /**
     * @var object[][]
     */
    protected static $awarded = [];

    /**
     * @var object
     */
    protected $certificate = null;

    /**
     * @inheritDoc
     *
     * @param JDatabaseDriver $dbo
     * @param CMSApplication  $application
     * @param FontManager     $fontManager
     * @param Registry        $params
     *
     * @return void
     */
    public function __construct(
        JDatabaseDriver $dbo,
        CMSApplication $application,
        FontManager $fontManager,
        Registry $params
    ) {
        parent::__construct($dbo);

        $this->app         = $application;
        $this->fontManager = $fontManager;
        $this->params      = $params;
    }

    /**
     * @param User|int                  $user
     * @param ?OscampusTableCourses|int $course
     *
     * @return object|object[]
     */
    public function getCertificates($user, $course = null)
    {
        $userId   = (int)($user instanceof User ? $user->id : $user);
        $courseId = (int)($course instanceof OscampusTableCourses ? $course->id : $course);

        if (!isset(static::$awarded[$userId])) {
            $query = $this->getQuery('certificate.users_id = ' . $userId);

            static::$awarded[$userId] = $this->dbo->setQuery($query)->loadObjectList('courses_id');
        }

        if ($courseId) {
            if (isset(static::$awarded[$userId][$courseId])) {
                return static::$awarded[$userId][$courseId];
            }

            return null;
        }

        return static::$awarded[$userId];
    }

    /**
     * @param User|int                 $user
     * @param OscampusTableCourses|int $course
     *
     * @return Certificate
     */
    public function load($user, $course): Certificate
    {
        if ($certificate = $this->getCertificates($user, $course)) {
            $this->createProvisional($certificate->courses_id, $certificate->users_id, $certificate->certificates_id);

            $this->certificate->id          = $certificate->id;
            $this->certificate->date_earned = $certificate->date_earned;
        }

        return $this;
    }

    /**
     * @param int $id
     *
     * @return $this
     */
    public function loadById(int $id): ?Certificate
    {
        $query = $this->getQuery('certificate.id = ' . (int)$id);

        if ($certificate = $this->dbo->setQuery($query)->loadObject()) {
            $this->createProvisional($certificate->courses_id, $certificate->users_id, $certificate->certificates_id);
            $this->certificate->id          = $id;
            $this->certificate->date_earned = $certificate->date_earned;
        }

        return $this;
    }

    /**
     * @param OscampusTableCourses|int       $course
     * @param ?User|int                      $user
     * @param ?OscampusTableCertificates|int $template
     *
     * @return Certificate
     */
    public function createProvisional($course, $user = null, $template = null): Certificate
    {
        if (!$course instanceof OscampusTableCourses) {
            $courseId = (int)$course;
            $course   = OscampusTable::getInstance('Courses');
            $course->load(['id' => $courseId]);
        }

        if (!$template instanceof OscampusTableCertificates) {
            $templateId = (int)$template ?: $course->certificates_id;
            $template   = OscampusTable::getInstance('Certificates');

            $query = $templateId ? ['id' => $templateId] : ['default' => 1];
            $template->load($query);
        }

        if (!$user instanceof User) {
            $user = OscampusFactory::getUser((int)$user);
        }

        $teacher = User::getInstance();
        if ($course->teachers_id) {
            $teacherTable = OscampusTable::getInstance('Teachers');
            if ($teacherTable->load(['id' => $course->teachers_id])) {
                $teacher->load($teacherTable->users_id);
            }
        }

        $this->certificate = (object)[
            'id'              => null,
            'users_id'        => $user->id,
            'courses_id'      => $course->id,
            'date_earned'     => gmdate('Y-m-d H:i:s'),
            'certificates_id' => $template->id,
            'image'           => $template->image,
            'font'            => $template->font,
            'fontsize'        => $template->fontsize,
            'fontcolor'       => $template->fontcolor,
            'dateformat'      => $template->dateformat,
            'movable'         => $template->movable,
            'name'            => $user->name,
            'username'        => $user->username,
            'course_title'    => $course->title,
            'teacher_id'      => $teacher ? $teacher->id : null,
            'teacher_name'    => $teacher ? $teacher->name : null
        ];

        return $this;
    }

    /**
     * @param ?bool $download
     *
     * @return void
     * @throws Exception
     */
    public function render(bool $download = false)
    {
        $imagePath = $this->getImagePath();

        $mimeType = mime_content_type($imagePath);
        switch ($mimeType) {
            case 'image/jpeg':
                $certificateImage = imagecreatefromjpeg($imagePath);
                break;

            case 'image/png':
                $certificateImage = imagecreatefrompng($imagePath);
                break;

            case 'image/gif':
                $certificateImage = imagecreatefromgif($imagePath);
                break;

            case 'image/bmp':
                $certificateImage = imagecreatefrombmp($imagePath);
                break;

            default:
                $certificateImage = false;
                break;
        }

        if ($certificateImage === false) {
            throw new Exception(Text::sprintf('COM_OSCAMPUS_ERROR_CERTIFICATE_BADMIME', $mimeType));
        }

        $fontSize = $this->certificate->fontsize
            ?: $this->params->get('certificates.fontsize')
                ?: 48;

        $fontAngle = 0;
        $fontColor = $this->getFontColor($certificateImage);

        $fontHash = $this->certificate->font
            ?: $this->params->get('certificates.font');

        if ($fontHash) {
            $font = $this->fontManager->getFontByHash($fontHash);
        }
        if (empty($font)) {
            $font = $this->fontManager->getFont('roboto');
        }

        $movable  = new Registry($this->certificate->movable);
        $editBase = json_decode($movable->get('editBase'));
        $overlays = (array)$movable->get('overlays');

        $actualHeight = imagesy($certificateImage);
        $resize       = $actualHeight / $editBase->height;

        foreach ($overlays as $name => &$overlay) {
            if ($overlay = json_decode($overlay)) {
                $text = $this->getOverlayText($name, $movable->get('text.' . $name));

                $box      = imageftbbox($fontSize, $fontAngle, $font->filepath, $text);
                $boxWidth = $box[2] - $box[0];

                $x = $overlay->left * $resize;
                $y = ($overlay->top + $overlay->height) * $resize;

                $xCentered = $x + (($overlay->width * $resize) / 2) - ($boxWidth / 2);

                imagefttext(
                    $certificateImage,
                    $fontSize,
                    $fontAngle,
                    $xCentered,
                    $y,
                    $fontColor,
                    $font->filepath,
                    $text
                );

                $overlay->value = $text;
            }
        }

        header('content-type: image/png');
        header('cache-control: no-store, max-age=0');
        if ($download) {
            $tokens = [
                '{id}'       => $this->certificate->id,
                '{name}'     => $overlays['student']->value,
                '{class}'    => $overlays['course']->value,
                '{provider}' => $overlays['provider']->value,
                '{date}'     => date('Y-m-d', strtotime($overlays['completed']->value))
            ];

            $tokens = array_map(['\\Joomla\\CMS\\Filter\\OutputFilter', 'stringURLSafe'], $tokens);

            $fileName = $this->params->get('certificates.filename') ?: 'certificate_{class}';
            $fileName = str_replace(array_keys($tokens), $tokens, $fileName);

            header(sprintf('content-disposition: attachment; filename="%s"', $fileName . '.png'));
        }

        imagepng($certificateImage);
        imagedestroy($certificateImage);
        jexit();
    }

    /**
     * Award a certificate if all requirements have been passed
     *
     * @param int          $courseId
     * @param UserActivity $activity
     *
     * @return void
     * @throws Exception
     */
    public function award(int $courseId, UserActivity $activity)
    {
        if ($courseId && $this->params->get('certificates.enabled', 1)) {
            $summary = $activity->getLessonSummary($courseId);
            $summary = array_pop($summary);

            if ($summary && $summary->certificates_id >= 0) {
                if ($summary->viewed == $summary->lessons) {
                    // All classes have at least been viewed
                    $lessons = $activity->getCourseLessons($courseId);

                    // Check all quizzes passed
                    if ($quizPassed = $this->params->get('quizzes.passingScore')) {
                        foreach ($lessons as $lessonId => $lesson) {
                            if ($lesson->type == 'quiz') {
                                if ($lesson->score < $quizPassed) {
                                    return;
                                }
                            }
                        }
                    }

                    // All requirements passed, award certificate
                    if ($summary->certificates_id) {
                        $certificateId = $summary->certificates_id;

                    } else {
                        $query = $this->dbo->getQuery(true)
                            ->select('id')
                            ->from('#__oscampus_certificates')
                            ->where($this->dbo->quoteName('default') . ' = 1');

                        $certificateId = $this->dbo->setQuery($query)->loadResult();
                    }

                    $certificate = (object)[
                        'users_id'        => $summary->users_id,
                        'courses_id'      => $courseId,
                        'date_earned'     => OscampusFactory::getDate()->toSql(),
                        'certificates_id' => $certificateId
                    ];

                    $isNew = !(bool)$summary->awarded_id;

                    PluginHelper::importPlugin('oscampus');
                    $events = OscampusEventDispatcher::getInstance();

                    $events->trigger('oscampusCertificateBeforeAward', [$certificate, $summary, $isNew]);

                    if ($summary->awarded_id) {
                        $certificate->id = $summary->awarded_id;
                        $this->dbo->updateObject('#__oscampus_courses_certificates', $certificate, 'id');

                    } else {
                        $this->dbo->insertObject('#__oscampus_courses_certificates', $certificate, 'id');
                        $certificate->id = $this->dbo->insertid();
                    }

                    $events->trigger('oscampusCertificateAfterAward', [$certificate, $summary, $isNew]);
                }
            }
        }
    }

    /**
     * @param ?string|string[] $where
     *
     * @return JDatabaseQuery
     */
    protected function getQuery($where = null): JDatabaseQuery
    {
        $query = $this->dbo->getQuery(true)
            ->select([
                'certificate.*',
                'template.image',
                'template.font',
                'template.fontsize',
                'template.fontcolor',
                'template.movable',
                'user.name',
                'user.username',
                'course.title AS course_title',
                'teacher.id AS teacher_id',
                'teacher.name AS teacher_name'
            ])
            ->from('#__oscampus_courses_certificates AS certificate')
            ->leftJoin('#__oscampus_courses AS course ON course.id = certificate.courses_id')
            ->leftJoin('#__users AS teacher ON teacher.id = course.teachers_id')
            ->leftJoin('#__users AS user ON user.id = certificate.users_id')
            ->leftJoin('#__oscampus_certificates AS template ON certificate.certificates_id = template.id');

        if ($where) {
            $query->where($where);
        }

        return $query;
    }

    /**
     * @return string
     * @throws Exception
     */
    protected function getImagePath(): string
    {
        if ($this->certificate) {
            $image = $this->certificate->image ?: $this->params->get('certificates.image');
            if ($image) {
                $image = HTMLHelper::_(
                    'image',
                    $image,
                    null,
                    null,
                    false,
                    1
                );

            } else {
                $image = HTMLHelper::_(
                    'image',
                    'com_oscampus/default-certificate.jpg',
                    null,
                    null,
                    true,
                    1
                );
            }

            if ($image) {
                $imagePath = Path::clean(JPATH_SITE . '/' . str_replace(Uri::root(true), '', $image));

                if (is_file($imagePath)) {
                    return $imagePath;
                }

                throw new Exception(Text::sprintf('COM_OSCAMPUS_ERROR_CERTIFICATE_BADIMAGE', $image, $imagePath));

            } else {
                throw new Exception(Text::_('COM_OSCAMPUS_ERROR_CERTIFICATE_NOIMAGE'));
            }
        }

        throw new Exception(Text::_('COM_OSCAMPUS_ERROR_CERTIFICATE_LOADED'));
    }

    /**
     * @param resource $image
     *
     * @return false|int
     */
    protected function getFontColor($image)
    {
        $hexColor = $this->certificate->fontcolor
            ?: $this->params->get('certificates.fontcolor')
                ?: '000';

        // Convert hex string to rgb
        $hexColor = trim($hexColor, '#');
        if (strlen($hexColor) == 3) {
            $rgb = str_split($hexColor);

        } elseif (strlen($hexColor) == 6) {
            $rgb = str_split($hexColor, 2);

        } else {
            $rgb = str_split(str_pad($hexColor, 6, '0', STR_PAD_RIGHT));
        }

        $rgb = array_map('hexdec', $rgb);

        list($red, $green, $blue) = $rgb;

        return imagecolorallocate($image, $red, $green, $blue);
    }

    /**
     * @param string  $name
     * @param ?string $customText
     *
     * @return string
     * @throws Exception
     */
    protected function getOverlayText(string $name, string $customText = null): string
    {
        if ($customText) {
            return (string)$customText;

        } else {
            switch ($name) {
                case 'student':
                    return $this->certificate->name;

                case 'course':
                    return $this->certificate->course_title;

                case 'teacher':
                    return $this->certificate->teacher_name ?: Text::_('COM_OSCAMPUS_CERTIFICATE_TEACHER_NONE');

                case 'provider':
                    return Uri::getInstance()->getHost();

                case 'completed':
                    $dateEarned = new DateTime($this->certificate->date_earned);
                    $dateFormat = $this->certificate->dateformat
                        ?: $this->params->get('certificates.dateformat', 'M  j, Y');

                    return $dateEarned->format($dateFormat);
            }
        }

        return sprintf('<<%s>>', $name);
    }
}
