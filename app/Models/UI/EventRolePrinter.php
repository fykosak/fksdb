<?php

declare(strict_types=1);

namespace FKSDB\Models\UI;

use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\PersonModel;
use Nette\Utils\Html;

class EventRolePrinter
{
    public static function getHtml(PersonModel $person, EventModel $event): Html
    {
        $roles = $person->getEventRoles($event);
        if (!count($roles)) {
            return Html::el('span')
                ->addAttributes(['class' => 'badge bg-danger'])
                ->addText(_('No role'));
        }
        $container = Html::el('span');
        foreach ($roles as $role) {
            $container->addHtml($role->badge());
        }
        return $container;
    }
}
