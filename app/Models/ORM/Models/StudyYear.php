<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models;

use FKSDB\Models\ORM\Columns\Types\EnumColumn;
use FKSDB\Models\Utils\FakeStringEnum;
use Nette\Utils\Html;
use Nette\Utils\Strings;

class StudyYear extends FakeStringEnum implements EnumColumn
{
    public const P_5 = 'P_5';
    public const P_6 = 'P_6';
    public const P_7 = 'P_7';
    public const P_8 = 'P_8';
    public const P_9 = 'P_9';

    public const H_1 = 'H_1';
    public const H_2 = 'H_2';
    public const H_3 = 'H_3';
    public const H_4 = 'H_4';

    public const U_ALL = 'U_ALL';

    public const NONE = 'NONE';

    public function badge(): Html
    {
        return Html::el('span')->addText($this->value);
    }

    public function label(): string
    {
        switch ($this->value) {
            case self::P_5:
                return _('Primary school 5th grade or lower.');
            case self::P_6:
                return _('Primary school 6th');
            case self::P_7:
                return _('Primary school 7th');
            case self::P_8:
                return _('Primary school 8th');
            case self::P_9:
                return _('Primary school 9th');
            case self::H_1:
                return _('High school 1st grade');
            case self::H_2:
                return _('High school 2nd grade');
            case self::H_3:
                return _('High school 3rd grade');
            case self::H_4:
                return _('High school 4th grade');
            case self::U_ALL:
                return _('University any grade');
            case self::NONE:
                return _('Not a student');
        }
        throw new \InvalidArgumentException();
    }

    public function numeric(): ?int
    {
        switch ($this->value) {
            case self::P_5:
                return 5;
            case self::P_6:
                return 6;
            case self::P_7:
                return 7;
            case self::P_8:
                return 8;
            case self::P_9:
                return 9;
            case self::H_1:
                return 1;
            case self::H_2:
                return 2;
            case self::H_3:
                return 3;
            case self::H_4:
                return 4;
            default:
                return null;
        }
    }

    /**
     * @return static[]
     */
    public static function getPrimarySchoolCases(): array
    {
        return [
            new self(self::P_5),
            new self(self::P_6),
            new self(self::P_7),
            new self(self::P_8),
            new self(self::P_9),
        ];
    }

    public function isPrimarySchool(): bool
    {
        return Strings::startsWith($this->value, 'P');
    }

    /**
     * @return static[]
     */
    public static function getHighSchoolCases(): array
    {
        return [
            new self(self::H_1),
            new self(self::H_2),
            new self(self::H_3),
            new self(self::H_4),
        ];
    }

    public function isHighSchool(): bool
    {
        return Strings::startsWith($this->value, 'H');
    }

    public static function tryFromLegacy(?int $studyYear): ?self
    {
        if (is_null($studyYear)) {
            return null;
        }
        switch ($studyYear) {
            case 1:
                return new self(self::H_1);
            case 2:
                return new self(self::H_2);
            case 3:
                return new self(self::H_3);
            case 4:
                return new self(self::H_4);

            case 6:
                return new self(self::P_6);
            case 7:
                return new self(self::P_7);
            case 8:
                return new self(self::P_8);
            case 9:
                return new self(self::P_9);
        }
        throw new \InvalidArgumentException();
    }

    public static function cases(): array
    {
        return [];
    }
}
