<?php

declare(strict_types=1);

namespace FKSDB\Models\Authorization\Roles\Contest;

use FKSDB\Models\Authorization\Roles\ImplicitRole;
use FKSDB\Models\ORM\Models\ContestModel;
use FKSDB\Models\ORM\Models\OrganizerModel;
use Fykosak\NetteORM\Model\Model;
use Nette\Utils\Html;

final class OrganizerRole implements ContestRole, ImplicitRole
{
    public const RoleId = 'org'; // phpcs:ignore
    public OrganizerModel $organizer;

    public function __construct(OrganizerModel $organizer)
    {
        $this->organizer = $organizer;
    }

    public function getContest(): ContestModel
    {
        return $this->organizer->contest;
    }

    public function getRoleId(): string
    {
        return self::RoleId;
    }

    public function badge(): Html
    {
        return Html::el('span')
            ->addAttributes(['class' => 'me-2 badge bg-primary '])
            ->addText($this->label() . ' (' . $this->description() . ')');
    }

    public function description(): string
    {
        return 'základní role organizátora';
    }

    public function label(): string
    {
        return 'Organizer';
    }

    public function getModel(): Model
    {
        return $this->organizer;
    }
}
