<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Person\Detail;

use FKSDB\Models\ORM\FieldLevelPermissionValue;
use FKSDB\Models\ORM\Models\GrantModel;
use Fykosak\Utils\UI\Title;
use Tracy\Debugger;

class RoleComponent extends BaseListComponent
{

    protected function getMinimalPermissions(): FieldLevelPermissionValue
    {
        return FieldLevelPermissionValue::Full;
    }

    protected function getTitle(): Title
    {
        return new Title(null, _('Assigned roles'));
    }

    protected function getModels(): iterable
    {
        return $this->person->getLogin()->getGrants();
    }

    protected function configure(): void
    {
        $this->classNameCallback = fn(GrantModel $grant) => 'alert alert-' . $grant->contest->getContestSymbol();
        $row0 = $this->createColumnsRow('row0');
        $contestColumn = $row0->createReferencedColumn('contest.name');
        $contestColumn->className .= ' h4';
        $row1 = $this->createColumnsRow('row1');
        $row1->createRendererColumn(
            'role',
            fn(GrantModel $grant) => $grant->role->name . ' - ' . $grant->role->description
        );
    }
}
