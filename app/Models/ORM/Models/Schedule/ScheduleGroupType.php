<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models\Schedule;

use FKSDB\Models\Exceptions\NotImplementedException;
use FKSDB\Models\ORM\Columns\Types\EnumColumn;
use FKSDB\Models\Utils\FakeStringEnum;
use Nette\Utils\Html;

final class ScheduleGroupType extends FakeStringEnum implements EnumColumn
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
    public const DSEF_ALL_DAY = 'dsef_all_day';

    public const APPAREL = 'apparel';

    public const TRANSPORT = 'transport';
    public const TICKET = 'ticket';

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
            new self(self::DSEF_ALL_DAY),
            new self(self::APPAREL),
            new self(self::TRANSPORT),
            new self(self::TICKET),
        ];
    }

    /**
     * @throws NotImplementedException
     */
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
            case self::DSEF_ALL_DAY:
                $badge = 'badge bg-color-8';
                break;
            case self::APPAREL:
                $badge = 'badge bg-color-9';
                break;
            case self::TRANSPORT:
            case self::TICKET:
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
            case self::DSEF_ALL_DAY:
                return _('DSEF all day');
            case self::APPAREL:
                return _('Apparel');
            case self::TRANSPORT:
                return _('Transport');
            case self::TICKET:
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
            case self::DSEF_AFTERNOON:
            case self::DSEF_MORNING:
            case self::DSEF_ALL_DAY:
                $params['price'] = false;
                $params['groupLabel'] = false;
                break;
            case self::ACCOMMODATION:
                break;
            case self::VACCINATION_COVID:
            case self::ACCOMMODATION_TEACHER:
            case self::ACCOMMODATION_GENDER:
            case self::VISA:
            case self::TEACHER_PRESENT:
                $params['capacity'] = false;
                $params['price'] = false;
                $params['groupLabel'] = false;
                break;
            case self::WEEKEND:
                $params['groupTime'] = true;
                break;
            case self::APPAREL:
                $params['capacity'] = false;
                $params['price'] = false;
                $params['groupTime'] = false;
                break;
            case self::TRANSPORT:
                $params['capacity'] = false;
                $params['price'] = false;
                break;
            case self::TICKET:
                $params['capacity'] = false;
                break;
        }
        return $params;
    }
}
