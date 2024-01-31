<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models;

use FKSDB\Models\ORM\Columns\Types\EnumColumn;
use Fykosak\Utils\UI\Title;
use Nette\Utils\Html;
use Nette\Utils\Strings;

enum StudyYear: string implements EnumColumn
{
    // phpcs:disable
    case Primary5 = 'P_5';
    case Primary6 = 'P_6';
    case Primary7 = 'P_7';
    case Primary8 = 'P_8';
    case Primary9 = 'P_9';

    case High1 = 'H_1';
    case High2 = 'H_2';
    case High3 = 'H_3';
    case High4 = 'H_4';

    case UniversityAll = 'U_ALL';

    case None = 'NONE';

    // phpcs:enable

    public function behaviorType(): string
    {
        if ($this->isPrimarySchool()) {
            return 'primary';
        } elseif ($this->isHighSchool()) {
            return 'success';
        } elseif ($this === self::UniversityAll) {
            return 'warning';
        }
        return 'dark';
    }

    public function badge(): Html
    {
        return Html::el('span')
            ->setAttribute('class', 'badge bg-' . $this->behaviorType())
            ->addText($this->label());
    }

    public function label(): string
    {
        return match ($this) {
            self::Primary5 => _('Primary school 5th grade or lower.'),
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
            self::Primary5 => 5,
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

    /**
     * @phpstan-return self[]
     */
    public static function getPrimarySchoolCases(): array
    {
        return [
            self::Primary5,
            self::Primary6,
            self::Primary7,
            self::Primary8,
            self::Primary9,
        ];
    }

    public function isPrimarySchool(): bool
    {
        return Strings::startsWith($this->value, 'P_');
    }

    /**
     * @phpstan-return static[]
     */
    public static function getHighSchoolCases(): array
    {
        return [
            self::High1,
            self::High2,
            self::High3,
            self::High4,
        ];
    }

    public function isHighSchool(): bool
    {
        return Strings::startsWith($this->value, 'H_');
    }

    public function getGraduationYear(int $acYear): ?int
    {
        if ($this->isHighSchool()) {
            return $acYear + 5 - $this->numeric();
        } elseif ($this->isPrimarySchool()) {
            return $acYear + 14 - $this->numeric();
        }
        return null;
        // throw new \InvalidArgumentException('Graduation year not match');
    }

    public function title(): Title
    {
        return new Title(null, $this->label());
    }
}
