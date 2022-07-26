<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models\Schedule;

use FKSDB\Models\Exceptions\NotImplementedException;
use FKSDB\Models\ORM\Columns\Types\EnumColumn;
use Nette\Utils\Html;

class ScheduleGroupType implements EnumColumn
{
    public const ACCOMMODATION = 'accommodation';
    public const ACCOMMODATION_GENDER = 'accommodation_gender';
    public const ACCOMMODATION_TEACHER = 'accommodation_teacher';
    public const VISA = 'visa';
    public const VACCINATION_COVID = 'vaccination_covid';

    public const TEACHER_PRESENT = 'teacher_present';

    public const WEEKEND = 'weekend';
    public const WEEKEND_INFO = 'weekend_info';

    public const DSEF_MORNING = 'dsef_morning';
    public const DSEF_AFTERNOON = 'dsef_afternoon';

    public const T_SHIRT_SIZE = 't-shirt_size';
    public const T_SHIRT_COLOR = 't-shirt_color';
    public const JUMPER_SIZE = 'jumper_size';

    public const ARRIVAL_DESTINATION = 'arrival_destination';
    public const DEPARTURE_DESTINATION = 'departure_destination';


    public string $value;

    public function __construct(string $type)
    {
        $this->value = $type;
    }

    public static function tryFrom(?string $type): ?self
    {
        return $type ? new self($type) : null;
    }

    /**
     * @return self[]
     */
    public static function cases(): array
    {
        return [
            new self(self::ACCOMMODATION),
            new self(self::ACCOMMODATION_GENDER),
            new self(self::ACCOMMODATION_TEACHER),
            new self(self::VISA),
            new self(self::VACCINATION_COVID),
            new self(self::TEACHER_PRESENT),
            new self(self::WEEKEND_INFO),
            new self(self::WEEKEND),
            new self(self::DSEF_MORNING),
            new self(self::DSEF_AFTERNOON),
            new self(self::T_SHIRT_COLOR),
            new self(self::T_SHIRT_SIZE),
            new self(self::JUMPER_SIZE),
            new self(self::ARRIVAL_DESTINATION),
            new self(self::DEPARTURE_DESTINATION),
        ];
    }

    public function badge(): Html
    {
        $badge = '';
        switch ($this->value) {
            case self::ACCOMMODATION:
                $badge = 'badge bg-color-1';
                break;
            case self::ACCOMMODATION_GENDER:
                $badge = 'badge bg-color-2';
                break;
            case self::ACCOMMODATION_TEACHER:
                $badge = 'badge bg-color-3';
                break;
            case self::TEACHER_PRESENT:
                $badge = 'badge bg-color-4';
                break;
            case self::VISA:
                $badge = 'badge bg-color-5';
                break;
            case self::VACCINATION_COVID:
                $badge = 'badge bg-color-6';
                break;
            case self::WEEKEND:
            case self::WEEKEND_INFO:
                $badge = 'badge bg-color-7';
                break;
            case self::DSEF_AFTERNOON:
            case self::DSEF_MORNING:
                $badge = 'badge bg-color-8';
                break;
            case self::T_SHIRT_SIZE:
            case self::T_SHIRT_COLOR:
            case self::JUMPER_SIZE:
                $badge = 'badge bg-color-9';
                break;
            case self::ARRIVAL_DESTINATION:
            case self::DEPARTURE_DESTINATION:
                $badge = 'badge bg-color-10';
                break;
        }
        return Html::el('span')->addAttributes(['class' => $badge])->addText($this->label());
    }

    /**
     * @throws NotImplementedException
     */
    public function label(): string
    {
        switch ($this->value) {
            case self::ACCOMMODATION:
                return _('Accommodation');
            case self::ACCOMMODATION_GENDER:
                return _('Accommodation gender');
            case self::ACCOMMODATION_TEACHER:
                return _('Accommodation teacher');
            case self::TEACHER_PRESENT:
                return _('Schedule during competition');
            case self::VISA:
                return _('Visa');
            case self::VACCINATION_COVID:
                return _('Covid-19 Vaccination');
            case self::WEEKEND_INFO:
                return _('Weekend info');
            case self::WEEKEND:
                return _('Weekend');
            case self::DSEF_MORNING:
                return _('DSEF morning');
            case self::DSEF_AFTERNOON:
                return _('DSEF afternoon');
            case self::T_SHIRT_COLOR:
                return _('T-shirt color');
            case self::T_SHIRT_SIZE:
                return _('T-shirt size');
            case self::JUMPER_SIZE:
                return _('Jumper size');
            case self::ARRIVAL_DESTINATION:
                return _('Arrival destination');
            case self::DEPARTURE_DESTINATION:
                return _('Departure destination');
        }
        throw new NotImplementedException();
    }
}
