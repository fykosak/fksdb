<?php

declare(strict_types=1);

namespace FKSDB\Models\Authorization\Roles\Events;

use FKSDB\Models\ORM\Models\EventModel;
use Nette\Security\Role;
use Nette\Utils\Html;

class EventRole implements Role
{
    // phpcs:disable
    public const GameInserter = 'gameInserter';
    public const ApplicationManager = 'applicationManager';
    // phpcs:enable

    protected EventModel $event;
    private string $roleId;

    public function __construct(string $roleId, EventModel $event)
    {
        $this->event = $event;
        $this->roleId = $roleId;
    }

    final public function getEvent(): EventModel
    {
        return $this->event;
    }

    public function getRoleId(): string
    {
        return $this->roleId;
    }

    public function badge(): Html
    {
        return Html::el('span')->addAttributes([
            'class' => 'badge bg-primary',
        ])->addText($this->roleId);
    }
}
