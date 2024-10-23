<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models\Schedule;

use FKSDB\Models\Exceptions\NotImplementedException;
use FKSDB\Models\ORM\Columns\Types\EnumColumn;
use Fykosak\Utils\UI\Title;
use Nette\Utils\Html;

enum ScheduleGroupType: string implements EnumColumn
{
    case Accommodation = 'accommodation';
    case AccommodationGender = 'accommodation_gender';
    case AccommodationTeacher = 'accommodation_teacher';
    case Visa = 'visa';
    case VaccinationCovid = 'vaccination_covid';

    case TeacherPresent = 'teacher_present';

    case Weekend = 'weekend';
    case Info = 'info';

    case Excursion = 'excursion';

    case Apparel = 'apparel';
    case Food = 'food';

    case Transport = 'transport';
    case Ticket = 'ticket';

    /**
     * @throws NotImplementedException
     */
    public function badge(): Html
    {
        $badge = '';
        switch ($this) {
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
            case self::Excursion:
            case self::Weekend:
                $badge = 'badge bg-color-7';
                break;
            case self::Info:
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
        return match ($this) {
            self::Accommodation => 'fas fa-bed',
            self::Weekend => 'fas fa-calendar',
            self::Info => 'fas fa-info',
            self::Excursion => 'fas fa-flask',
            self::Food => 'fas fa-utensils',
            default => '',
        };
    }

    /**
     * @throws NotImplementedException
     */
    public function label(): string
    {
        switch ($this) {
            case self::Accommodation:
                return _('Accommodation');
            case self::AccommodationGender:
                return _('Accommodation gender');
            case self::AccommodationTeacher:
                return _('Accommodation teacher');
            case self::Food:
                return _('Food');
            case self::TeacherPresent:
                return _('Schedule during competition');
            case self::Visa:
                return _('Visa');
            case self::VaccinationCovid:
                return _('Covid-19 Vaccination');
            case self::Info:
                return _('Info');
            case self::Weekend:
                return _('Schedule');
            case self::Excursion:
                return _('Excursion');
            case self::Apparel:
                return _('Apparel');
            case self::Transport:
                return _('Transport');
            case self::Ticket:
                return _('Ticket');
        }
        throw new NotImplementedException();
    }
}
