<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models\Schedule;

use FKSDB\Models\Exceptions\NotImplementedException;
use FKSDB\Models\ORM\Columns\Types\EnumColumn;
use FKSDB\Models\Utils\FakeStringEnum;
use Fykosak\Utils\UI\Title;
use Nette\Utils\Html;

final class ScheduleGroupType extends FakeStringEnum implements EnumColumn
{
    // phpcs:disable
    public const Accommodation = 'accommodation';
    public const AccommodationGender = 'accommodation_gender';
    public const AccommodationTeacher = 'accommodation_teacher';
    public const Visa = 'visa';
    public const VaccinationCovid = 'vaccination_covid';

    public const TeacherPresent = 'teacher_present';

    public const Weekend = 'weekend';
    public const WeekendInfo = 'weekend_info';

    public const DSEFMorning = 'dsef_morning';
    public const DSEFAfternoon = 'dsef_afternoon';
    public const DSEFAllDay = 'dsef_all_day';

    public const Apparel = 'apparel';

    public const Transport = 'transport';
    public const Ticket = 'ticket';
    // phpcs:enable

    /**
     * @return self[]
     */
    public static function cases(): array
    {
        return [
            new self(self::Accommodation),
            new self(self::AccommodationGender),
            new self(self::AccommodationTeacher),
            new self(self::Visa),
            new self(self::VaccinationCovid),
            new self(self::TeacherPresent),
            new self(self::WeekendInfo),
            new self(self::Weekend),
            new self(self::DSEFMorning),
            new self(self::DSEFAllDay),
            new self(self::DSEFAfternoon),
            new self(self::Apparel),
            new self(self::Transport),
            new self(self::Ticket),
        ];
    }

    /**
     * @throws NotImplementedException
     */
    public function badge(): Html
    {
        $badge = '';
        switch ($this->value) {
            case self::Accommodation:
                $badge = 'badge bg-color-1';
                break;
            case self::AccommodationGender:
                $badge = 'badge bg-color-2';
                break;
            case self::AccommodationTeacher:
                $badge = 'badge bg-color-3';
                break;
            case self::TeacherPresent:
                $badge = 'badge bg-color-4';
                break;
            case self::Visa:
                $badge = 'badge bg-color-5';
                break;
            case self::VaccinationCovid:
                $badge = 'badge bg-color-6';
                break;
            case self::DSEFMorning:
            case self::DSEFAfternoon:
            case self::DSEFAllDay:
            case self::Weekend:
                $badge = 'badge bg-color-7';
                break;
            case self::WeekendInfo:
                $badge = 'badge bg-color-8';
                break;
            case self::Apparel:
                $badge = 'badge bg-color-9';
                break;
            case self::Transport:
            case self::Ticket:
                $badge = 'badge bg-color-10';
                break;
        }

        return Html::el('span')->addAttributes(['class' => $badge])->addHtml($this->title()->toHtml());
    }

    /**
     * @throws NotImplementedException
     */
    public function title(): Title
    {
        return new Title(null, $this->label(), $this->getIconName());
    }

    public function getIconName(): string
    {
        switch ($this->value) {
            case self::Accommodation:
                return 'fas fa-bed';
            case self::Weekend:
                return 'fas fa-calendar';
            case self::WeekendInfo:
                return 'fas fa-info';
        }
        return '';
    }

    /**
     * @throws NotImplementedException
     */
    public function label(): string
    {
        switch ($this->value) {
            case self::Accommodation:
                return _('Accommodation');
            case self::AccommodationGender:
                return _('Accommodation gender');
            case self::AccommodationTeacher:
                return _('Accommodation teacher');
            case self::TeacherPresent:
                return _('Schedule during competition');
            case self::Visa:
                return _('Visa');
            case self::VaccinationCovid:
                return _('Covid-19 Vaccination');
            case self::WeekendInfo:
                return _('Info');
            case self::Weekend:
                return _('Schedule');
            case self::DSEFMorning:
            case self::DSEFAfternoon:
            case self::DSEFAllDay:
                return _('DSEF');
            case self::Apparel:
                return _('Apparel');
            case self::Transport:
                return _('Transport');
            case self::Ticket:
                return _('Ticket');
        }
        throw new NotImplementedException();
    }

    public function isSelectable(): bool
    {
        if ($this->value === self::WeekendInfo) {
            return false;
        }
        return true;
    }
}
