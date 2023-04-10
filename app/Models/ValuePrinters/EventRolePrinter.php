<?php

declare(strict_types=1);

namespace FKSDB\Models\ValuePrinters;

use FKSDB\Models\Authorization\EventRole\{EventRole
};
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\PersonModel;
use Nette\SmartObject;
use Nette\Utils\Html;

class EventRolePrinter
{
    use SmartObject;

    public function __invoke(PersonModel $person, EventModel $event): Html
    {
        $container = Html::el('span');
        $roles = $person->getEventRoles($event);
        if (!count($roles)) {
            $container->addHtml(
                Html::el('span')
                    ->addAttributes(['class' => 'badge bg-danger'])
                    ->addText(_('No role'))
            );
            return $container;
        }
        return $this->getHtml($roles);
    }

    /**
     * @param EventRole[] $roles
     */
    private function getHtml(array $roles): Html
    {
        $container = Html::el('span');
        foreach ($roles as $role) {
            $container->addHtml($role->badge());
        }
        return $container;
    }
}
