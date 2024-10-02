<?php

declare(strict_types=1);

namespace FKSDB\Models\Authorization\Roles\Events;

use FKSDB\Models\ORM\Models\EventModel;
use Nette\Utils\Html;

final class ExplicitEventRole implements EventRole
{
    // phpcs:disable
    public const GameInserter = 'event.gameInserter';
    public const ApplicationManager = 'event.applicationManager';
    // phpcs:enable

    protected EventModel $event;
    /** @phpstan-param self::* $roleId */
    private string $roleId;

    /**
     * @phpstan-param self::* $roleId
     */
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
