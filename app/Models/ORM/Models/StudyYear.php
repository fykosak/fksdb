<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models;

use FKSDB\Models\ORM\Columns\Types\EnumColumn;
use FKSDB\Models\Utils\FakeStringEnum;
use Nette\Utils\Html;
use Nette\Utils\Strings;

final class StudyYear extends FakeStringEnum implements EnumColumn
{
    // phpcs:disable
    public const Primary5 = 'P_5';
    public const Primary6 = 'P_6';
    public const Primary7 = 'P_7';
    public const Primary8 = 'P_8';
    public const Primary9 = 'P_9';

    public const High1 = 'H_1';
    public const High2 = 'H_2';
    public const High3 = 'H_3';
    public const High4 = 'H_4';

    public const UniversityAll = 'U_ALL';

    public const None = 'NONE';
    // phpcs:enable

    public function badge(): Html
    {
        if ($this->isPrimarySchool()) {
            return Html::el('span')
                ->setAttribute('class', 'badge bg-primary')
                ->addText($this->label());
        } elseif ($this->isHighSchool()) {
            return Html::el('span')
                ->setAttribute('class', 'badge bg-success')
                ->addText($this->label());
        } elseif ($this->value === self::UniversityAll) {
            return Html::el('span')
                ->setAttribute('class', 'badge bg-warning')
                ->addText($this->label());
        }
        return Html::el('span')
            ->setAttribute('class', 'badge bg-dark')
            ->addText($this->label());
    }

    public function label(): string
    {
        switch ($this->value) {
            case self::Primary5:
                return _('Primary school 5th grade or lower.');
            case self::Primary6:
                return _('Primary school 6th');
            case self::Primary7:
                return _('Primary school 7th');
            case self::Primary8:
                return _('Primary school 8th');
            case self::Primary9:
                return _('Primary school 9th');
            case self::High1:
                return _('High school 1st grade');
            case self::High2:
                return _('High school 2nd grade');
            case self::High3:
                return _('High school 3rd grade');
            case self::High4:
                return _('High school 4th grade');
            case self::UniversityAll:
                return _('University any grade');
            case self::None:
                return _('Not a student');
        }
        throw new \InvalidArgumentException();
    }

    public function numeric(): ?int
    {
        switch ($this->value) {
            case self::Primary5:
                return 5;
            case self::Primary6:
                return 6;
            case self::Primary7:
                return 7;
            case self::Primary8:
                return 8;
            case self::Primary9:
                return 9;
            case self::High1:
                return 1;
            case self::High2:
                return 2;
            case self::High3:
                return 3;
            case self::High4:
                return 4;
            default:
                return null;
        }
    }

    /**
     * @phpstan-return self[]
     */
    public static function getPrimarySchoolCases(): array
    {
        return [
            new self(self::Primary5),
            new self(self::Primary6),
            new self(self::Primary7),
            new self(self::Primary8),
            new self(self::Primary9),
        ];
    }

    public function isPrimarySchool(): bool
    {
        return Strings::startsWith($this->value, 'P');
    }

    /**
     * @phpstan-return static[]
     */
    public static function getHighSchoolCases(): array
    {
        return [
            new self(self::High1),
            new self(self::High2),
            new self(self::High3),
            new self(self::High4),
        ];
    }

    public function isHighSchool(): bool
    {
        return Strings::startsWith($this->value, 'H');
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

    public static function cases(): array
    {
        return [
            new self(self::Primary5),
            new self(self::Primary6),
            new self(self::Primary7),
            new self(self::Primary8),
            new self(self::Primary9),
            new self(self::High1),
            new self(self::High2),
            new self(self::High3),
            new self(self::High4),
            new self(self::UniversityAll),
            new self(self::None),
        ];
    }
}
