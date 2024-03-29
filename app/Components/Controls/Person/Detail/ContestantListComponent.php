<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Person\Detail;

use FKSDB\Components\Grids\Components\Referenced\SimpleItem;
use FKSDB\Components\Grids\Components\Referenced\TemplateItem;
use FKSDB\Models\ORM\FieldLevelPermission;
use FKSDB\Models\ORM\Models\ContestantModel;
use Fykosak\NetteORM\Selection\TypedGroupedSelection;
use Fykosak\Utils\UI\Title;

/**
 * @phpstan-extends DetailComponent<ContestantModel,array{}>
 */
class ContestantListComponent extends DetailComponent
{
    protected function getMinimalPermissions(): int
    {
        return FieldLevelPermission::ALLOW_RESTRICT;
    }

    /**
     * @phpstan-return TypedGroupedSelection<ContestantModel>
     */
    protected function getModels(): TypedGroupedSelection
    {
        return $this->person->getContestants();
    }

    protected function configure(): void
    {
        $this->classNameCallback = fn(ContestantModel $contestant): string => $contestant->contest->getContestSymbol();
        $this->setTitle(// @phpstan-ignore-line
            new SimpleItem($this->container, '@contest.name')// @phpstan-ignore-line
        );
        $row1 = $this->createRow();
        $row1->addComponent(
            new TemplateItem($this->container, _('Contest year @contestant.year'), '@contestant.year:title'),
            'contestant__year'
        );
        if ($this->isOrganizer) {
            $this->addPresenterButton(
                ':Organizer:Contestant:edit',
                'edit',
                new Title(null, _('button.edit')),
                false,
                [
                    'contestId' => 'contest_id',
                    'year' => 'year',
                    'id' => 'contestant_id',
                ]
            );
            $this->addPresenterButton(
                ':Organizer:Contestant:detail',
                'detail',
                new Title(null, _('button.detail')),
                false,
                [
                    'contestId' => 'contest_id',
                    'year' => 'year',
                    'id' => 'contestant_id',
                ]
            );
        } else {
            $this->addPresenterButton(
                ':Public:Dashboard:default',
                'detail',
                new Title(null, _('button.detail')),
                false,
                [
                    'contestId' => 'contest_id',
                    'year' => 'year',
                ]
            );
        }
    }

    protected function getHeadline(): Title
    {
        return new Title(null, _('Contestants'));
    }
}
