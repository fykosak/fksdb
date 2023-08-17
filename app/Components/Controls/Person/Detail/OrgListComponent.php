<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Person\Detail;

use FKSDB\Components\Grids\Components\Button\PresenterButton;
use FKSDB\Components\Grids\Components\Container\RowContainer;
use FKSDB\Components\Grids\Components\Referenced\TemplateItem;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\FieldLevelPermission;
use FKSDB\Models\ORM\Models\OrgModel;
use Fykosak\NetteORM\TypedGroupedSelection;
use Fykosak\Utils\UI\Title;

/**
 * @phpstan-extends DetailComponent<OrgModel>
 */
class OrgListComponent extends DetailComponent
{
    protected function getMinimalPermissions(): int
    {
        return FieldLevelPermission::ALLOW_RESTRICT;
    }

    /**
     * @phpstan-return TypedGroupedSelection<OrgModel>
     */
    protected function getModels(): TypedGroupedSelection
    {
        return $this->person->getOrganisers();
    }

    protected function getHeadline(): Title
    {
        return new Title(null, _('Organizers'));
    }

    /**
     * @throws BadTypeException
     * @throws \ReflectionException
     */
    protected function configure(): void
    {
        $this->classNameCallback = fn(OrgModel $org) => 'alert alert-' . $org->contest->getContestSymbol();
        /** @phpstan-var RowContainer<OrgModel> $row0 */
        $row0 = new RowContainer($this->container);
        $this->addRow($row0, 'row0');
        $row0->addComponent(new TemplateItem($this->container, '@contest.name'), 'contest_name');
        $row0->addComponent(
            new TemplateItem($this->container, _('@org.since - @org.until')),
            'duration'
        );
        /** @phpstan-var RowContainer<OrgModel> $row1 */
        $row1 = new RowContainer($this->container);
        $row1->addComponent(
            new TemplateItem($this->container, '@org.domain_alias', '@org.domain_alias:title'),
            'domain_alias'
        );
        $row1->addComponent(
            new TemplateItem($this->container, '\signature{@org.tex_signature}', '@org.tex_signature:title'),
            'tex_signature'
        );
        $this->addRow($row1, 'row1');
        $this->addButton(
            new PresenterButton( // @phpstan-ignore-line
                $this->container,
                null,
                new Title(null, _('Edit')),
                fn(OrgModel $model) => [':Org:Org:edit', ['contestId' => $model->contest_id, 'id' => $model->org_id]],
                null,
                fn() => $this->isOrg,
            ),
            'edit'
        );
        $this->addButton(
            new PresenterButton( // @phpstan-ignore-line
                $this->container,
                null,
                new Title(null, _('Detail')),
                fn(OrgModel $model) => [':Org:Org:detail', ['contestId' => $model->contest_id, 'id' => $model->org_id]],
                null,
                fn() => $this->isOrg,
            ),
            'detail'
        );
    }
}
