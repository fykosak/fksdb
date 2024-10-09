<?php

declare(strict_types=1);

namespace FKSDB\Models\Authorization\Roles\ContestYear;

use FKSDB\Models\Authorization\Roles\Contest\ContestRole;
use FKSDB\Models\Authorization\Roles\ImplicitRole;
use FKSDB\Models\ORM\Models\ContestantModel;
use FKSDB\Models\ORM\Models\ContestModel;
use FKSDB\Models\ORM\Models\ContestYearModel;
use Fykosak\NetteORM\Model\Model;
use Nette\Utils\Html;

final class ContestantRole implements ContestYearRole, ContestRole, ImplicitRole
{
    public const RoleId = 'contestant'; // phpcs:ignore

    public ContestantModel $contestant;

    public function __construct(ContestantModel $contestant)
    {
        $this->contestant = $contestant;
    }

    public function getContestYear(): ContestYearModel
    {
        return $this->contestant->getContestYear();
    }

    public function getRoleId(): string
    {
        return self::RoleId;
    }

    public function getContest(): ContestModel
    {
        return $this->contestant->contest;
    }

    public function getModel(): Model
    {
        return $this->contestant;
    }

    public function badge(): Html
    {
        return Html::el('span')
            ->addAttributes(['class' => 'me-2 badge bg-primary'])
            ->addText($this->label() . ' (' . $this->description() . ')');
    }

    public function description(): string
    {
        return 'řešitel semináře, role je automaticky přiřazována při vytvoření řešitele';
    }

    public function label(): string
    {
        return 'Contestant';
    }
}
