<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Person\Detail;

use FKSDB\Components\Grids\Components\Container\RowContainer;
use FKSDB\Components\Grids\Components\Referenced\TemplateItem;
use FKSDB\Components\Grids\Components\Renderer\RendererItem;
use FKSDB\Models\ORM\FieldLevelPermissionValue;
use FKSDB\Models\ORM\Models\GrantModel;
use Fykosak\Utils\UI\Title;
use Nette\Database\Table\Selection;

class RoleComponent extends DetailComponent
{

    protected function getMinimalPermission(): FieldLevelPermissionValue
    {
        return FieldLevelPermissionValue::Full;
    }

    protected function getHeadline(): Title
    {
        return new Title(null, _('Assigned roles'));
    }

    protected function getModels(): Selection
    {
        return $this->person->getLogin()->getGrants();
    }

    protected function configure(): void
    {
        $this->classNameCallback = fn(GrantModel $grant) => 'alert alert-' . $grant->contest->getContestSymbol();
        $this->setTitle(new TemplateItem($this->container, '@contest.name'));

        $row1 = new RowContainer($this->container, new Title(null, ''));
        $this->addRow($row1, 'row1');
        $row1->addComponent(
            new RendererItem(
                $this->container,
                fn(GrantModel $grant) => $grant->role->name . ' - ' . $grant->role->description,
                new Title(null, '')
            ),
            'role'
        );
    }
}
