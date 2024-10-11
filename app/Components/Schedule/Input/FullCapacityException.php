<?php

declare(strict_types=1);

namespace FKSDB\Components\Schedule\Input;

use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Models\Schedule\ScheduleItemModel;
use Fykosak\Utils\Localization\GettextTranslator;

class FullCapacityException extends ScheduleException
{
    public function __construct(ScheduleItemModel $item, PersonModel $person, GettextTranslator $translator)
    {
        parent::__construct(
            $item->schedule_group,
            sprintf(
                _('The person %s could not be registered for "%s" because of full capacity.'),
                $person->getFullName(),
                $item->name->getText($translator->lang)
            )
        );
    }
}
