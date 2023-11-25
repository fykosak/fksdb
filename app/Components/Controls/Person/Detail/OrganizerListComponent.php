<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Person\Detail;

use FKSDB\Components\Grids\Components\Referenced\SimpleItem;
use FKSDB\Components\Grids\Components\Referenced\TemplateItem;
use FKSDB\Models\ORM\FieldLevelPermission;
use FKSDB\Models\ORM\Models\OrganizerModel;
use Fykosak\NetteORM\Selection\TypedGroupedSelection;
use Fykosak\Utils\UI\Title;

/**
 * @phpstan-extends DetailComponent<OrganizerModel,array{}>
 */
class OrganizerListComponent extends DetailComponent
{
    protected function getMinimalPermissions(): int
    {
        return FieldLevelPermission::ALLOW_RESTRICT;
    }

    /**
     * @phpstan-return TypedGroupedSelection<OrganizerModel>
     */
    protected function getModels(): TypedGroupedSelection
    {
        return $this->person->getOrganizers();
    }

    protected function getHeadline(): Title
    {
        return new Title(null, _('Organizers'));
    }

    protected function configure(): void
    {
        $this->classNameCallback = fn(OrganizerModel $model) => 'alert alert-' . $model->contest->getContestSymbol();
        $row0 = $this->createRow();
        $row0->addComponent(new SimpleItem($this->container, '@contest.name'), 'contest_name');
        $row0->addComponent(
            new TemplateItem($this->container, '@org.since - @org.until'),
            'duration'
        );
        $row1 = $this->createRow();
        $row1->addComponent(
            new SimpleItem($this->container, '@org.domain_alias'),
            'domain_alias'
        );
        $row1->addComponent(
            new TemplateItem($this->container, '\signature{@org.tex_signature}', '@org.tex_signature:title'),
            'tex_signature'
        );

        if ($this->isOrganizer) {
            $this->addPresenterButton(
                ':Organizer:Organizer:edit',
                'edit',
                new Title(null, _('button.edit')),
                false,
                ['contestId' => 'contest_id', 'id' => 'org_id']
            );
            $this->addPresenterButton(
                ':Organizer:Organizer:detail',
                'detail',
                new Title(null, _('button.detail')),
                false,
                ['contestId' => 'contest_id', 'id' => 'org_id']
            );
        }
    }
}
