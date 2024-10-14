<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models\Grant;

use FKSDB\Models\Authorization\Roles\EventRole;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\LoginModel;
use Fykosak\NetteORM\Model\Model;
use Nette\Utils\Html;

/**
 * @property-read int $event_grant_id
 * @property-read int $login_id
 * @property-read LoginModel $login
 * @property-read string|self::* $role
 * @property-read int $event_id
 * @property-read EventModel $event
 */
final class EventGrantModel extends Model implements EventRole
{
    // phpcs:disable
    public const GameInserter = 'event.gameInserter';
    public const ApplicationManager = 'event.applicationManager';

    // phpcs:enable

    final public function getEvent(): EventModel
    {
        return $this->event;
    }

    public function getRoleId(): string
    {
        return $this->role;
    }

    public function badge(): Html
    {
        return Html::el('span')->addAttributes([
            'class' => 'badge bg-primary',
        ])->addText($this->role);
    }
}
