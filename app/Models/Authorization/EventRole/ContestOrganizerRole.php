<?php

declare(strict_types=1);

namespace FKSDB\Models\Authorization\EventRole;

use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\OrganizerModel;
use Nette\Utils\Html;

class ContestOrganizerRole extends EventRole
{
    public const ROLE_ID = 'event.contestOrganizer';
    public OrganizerModel $organizer;

    public function __construct(EventModel $event, OrganizerModel $organizer)
    {
        parent::__construct(self::ROLE_ID, $event);
        $this->organizer = $organizer;
    }

    public function badge(): Html
    {
        return Html::el('span')
            ->addAttributes(['class' => 'badge bg-color-6'])
            ->addText(_('Contest organizer'));
    }
}
