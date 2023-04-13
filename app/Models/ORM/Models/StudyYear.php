<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models;

use FKSDB\Models\ORM\Columns\Types\EnumColumn;
use Nette\Utils\Html;
use Nette\Utils\Strings;

enum StudyYear: string implements EnumColumn
{
    case Primary6 = 'PRIMARY_6';
    case Primary7 = 'PRIMARY_7';
    case Primary8 = 'PRIMARY_8';
    case Primary9 = 'PRIMARY_9';

    case High1 = 'HIGH_1';
    case High2 = 'HIGH_2';
    case High3 = 'HIGH_3';
    case High4 = 'HIGH_4';

    case UniversityAll = 'UNIVERSITY_ALL';

    case None = 'NONE';

    public function badge(): Html
    {
        return Html::el('span')->addText($this->label());
    }

    public function label(): string
    {
        return match ($this) {
            self::Primary6 => _('Primary school 6th or lower.'),
            self::Primary7 => _('Primary school 7th'),
            self::Primary8 => _('Primary school 8th'),
            self::Primary9 => _('Primary school 9th'),
            self::High1 => _('High school 1st grade'),
            self::High2 => _('High school 2nd grade'),
            self::High3 => _('High school 3rd grade'),
            self::High4 => _('High school 4th grade'),
            self::UniversityAll => _('University any grade'),
            self::None => _('Not a student'),
        };
    }

    public function numeric(): ?int
    {
        return match ($this) {
            self::Primary6 => 6,
            self::Primary7 => 7,
            self::Primary8 => 8,
            self::Primary9 => 9,
            self::High1 => 1,
            self::High2 => 2,
            self::High3 => 3,
            self::High4 => 4,
            default => null,
        };
    }

    public static function getPrimarySchoolCases(): array
    {
        return [
            new self(self::P_6),
            new self(self::P_7),
            new self(self::P_8),
            new self(self::P_9),
        ];
    }

    public function isPrimarySchool(): bool
    {
        return Strings::startsWith($this->value, 'PRIMARY_');
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
        return Strings::startsWith($this->value, 'HIGH_');
    }

    public static function tryFromLegacy(?int $studyYear): ?self
    {
        if (is_null($studyYear)) {
            return null;
        }
        return match ($studyYear) {
            1 => self::High1,
            2 => self::High2,
            3 => self::High3,
            4 => self::High4,
            6 => self::Primary6,
            7 => self::Primary7,
            8 => self::Primary8,
            9 => self::Primary9,
            default => throw new \InvalidArgumentException(),
        };
    }
}
