<?php

declare(strict_types=1);

namespace FKSDB\Models\Authorization\Roles\Events;

use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\Grant\EventGrantModel;
use Nette\Utils\Html;

final class ExplicitEventRole implements EventRole
{
    // phpcs:disable
    public const GameInserter = 'event.gameInserter';
    public const ApplicationManager = 'event.applicationManager';
    // phpcs:enable

    private EventGrantModel $eventGrant;

    public function __construct(EventGrantModel $eventGrant)
    {
        $this->eventGrant = $eventGrant;
    }

    final public function getEvent(): EventModel
    {
        return $this->eventGrant->event;
    }

    public function getRoleId(): string
    {
        return $this->eventGrant->role;
    }

    public function badge(): Html
    {
        return Html::el('span')->addAttributes([
            'class' => 'badge bg-primary',
        ])->addText($this->eventGrant->role);
    }
}
