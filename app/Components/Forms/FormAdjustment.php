<?php

declare(strict_types=1);

namespace FKSDB\Components\Forms;

use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\Transitions\Holder\ModelHolder;
use Nette\Forms\Form;

interface FormAdjustment
{
    public function __invoke(array $values, Form $form, EventModel $event, ModelHolder $holder): void;
}
