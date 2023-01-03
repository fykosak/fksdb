<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Person\Detail;

use FKSDB\Components\Grids\Components\Button\PresenterButton;
use FKSDB\Components\Grids\Components\Container\RowContainer;
use FKSDB\Components\Grids\Components\Referenced\TemplateItem;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\FieldLevelPermissionValue;
use FKSDB\Models\ORM\Models\OrgModel;
use Fykosak\Utils\UI\Title;
use Nette\Database\Table\Selection;

class OrgListComponent extends DetailComponent
{
    protected function getMinimalPermission(): FieldLevelPermissionValue
    {
        return FieldLevelPermissionValue::Restrict;
    }

    protected function getModels(): Selection
    {
        return $this->person->getOrganisers();
    }

    protected function getHeadline(): Title
    {
        return new Title(null, _('Organisers'));
    }

    /**
     * @throws BadTypeException
     * @throws \ReflectionException
     */
    protected function configure(): void
    {
        $this->classNameCallback = fn(OrgModel $org) => 'alert alert-' . $org->contest->getContestSymbol();
        $this->setTitle(new TemplateItem($this->container, '@contest.name'));
        $row0 = new RowContainer($this->container);
        $this->addRow($row0, 'row0');
        $row0->addComponent(
            new TemplateItem($this->container, _('@org.since - @org.until')),
            'duration'
        );

        $row1 = new RowContainer($this->container);
        $row1->addComponent(new TemplateItem($this->container, '@org.domain_alias'), 'domain_alias');
        $row1->addComponent(new TemplateItem($this->container, '\siganture{@org.tex_signature}'), 'tex_signature');
        $this->addRow($row1, 'row1');
        $this->addButton(
            new PresenterButton(
                $this->container,
                new Title(null, _('Edit')),
                fn(OrgModel $model) => [':Org:Org:edit', ['contestId' => $model->contest_id, 'id' => $model->org_id]],
                null,
                fn() => $this->isOrg,
            ),
            'edit'
        );
        $this->addButton(
            new PresenterButton(
                $this->container,
                new Title(null, _('Detail')),
                fn(OrgModel $model) => [':Org:Org:detail', ['contestId' => $model->contest_id, 'id' => $model->org_id]],
                null,
                fn() => $this->isOrg,
            ),
            'detail'
        );
    }
}
