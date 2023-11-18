<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models\Schedule;

use FKSDB\Models\Exceptions\NotImplementedException;
use FKSDB\Models\ORM\Columns\Types\EnumColumn;
use FKSDB\Models\Utils\FakeStringEnum;
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

    public const Schedule = 'schedule';
    public const ScheduleInfo = 'schedule_info';
    public const DSEF = 'dsef';

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
            new self(self::ScheduleInfo),
            new self(self::Schedule),
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
            case self::DSEF:
            case self::ScheduleInfo:
                $badge = 'badge bg-color-7';
                break;
            case self::Schedule:
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
        return Html::el('span')->addAttributes(['class' => $badge])->addText($this->label());
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
            case self::ScheduleInfo:
                return _('Weekend info');
            case self::Schedule:
                return _('Weekend');
            case self::DSEF:
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

    /**
     * @phpstan-return array{
     *      capacity:bool,
     *      description:bool,
     *      groupLabel:bool,
     *      price:bool,
     *      groupTime:bool,
     * }
     */
    public function getRenderOptions(): array
    {
        $params = [
            'capacity' => true,
            'description' => true,
            'groupLabel' => true,
            'price' => true,
            'groupTime' => false,
        ];
        switch ($this->value) {
            case self::DSEF:
                $params['price'] = false;
                $params['groupLabel'] = false;
                break;
            case self::Accommodation:
                break;
            case self::VaccinationCovid:
            case self::AccommodationTeacher:
            case self::AccommodationGender:
            case self::Visa:
            case self::TeacherPresent:
                $params['capacity'] = false;
                $params['price'] = false;
                $params['groupLabel'] = false;
                break;
            case self::Schedule:
                $params['groupTime'] = true;
                break;
            case self::Apparel:
                $params['capacity'] = false;
                $params['price'] = false;
                $params['groupTime'] = false;
                break;
            case self::Transport:
                $params['capacity'] = false;
                $params['price'] = false;
                break;
            case self::Ticket:
                $params['capacity'] = false;
                break;
        }
        return $params;
    }
}
