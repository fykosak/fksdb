<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Person\Detail;

use FKSDB\Components\Grids\Components\Button\PresenterButton;
use FKSDB\Components\Grids\Components\Container\RowContainer;
use FKSDB\Components\Grids\Components\Referenced\TemplateItem;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\FieldLevelPermission;
use FKSDB\Models\ORM\Models\ContestantModel;
use Fykosak\NetteORM\TypedGroupedSelection;
use Fykosak\Utils\UI\Title;

/**
 * @phpstan-extends DetailComponent<ContestantModel>
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

    /**
     * @throws \ReflectionException
     * @throws BadTypeException
     */
    protected function configure(): void
    {
        $this->classNameCallback = fn(ContestantModel $contestant): string => 'alert alert-' .
            $contestant->contest->getContestSymbol();
        $this->setTitle(
            new TemplateItem($this->container, '@contest.name', '@contest.name:title')// @phpstan-ignore-line
        );
        /** @phpstan-var RowContainer<ContestantModel> $row1 */
        $row1 = new RowContainer($this->container, new Title(null, ''));
        $this->addRow($row1, 'row1');
        $row1->addComponent(
            new TemplateItem($this->container, _('Contest year @contestant.year'), '@contestant.year:title'),
            'contestant__year'
        );
        if ($this->isOrganizer) {
            $this->addButton(
                new PresenterButton(
                    $this->container,
                    null,
                    new Title(null, _('Edit')),
                    fn(ContestantModel $contestant): array => [
                        ':Organizer:Contestant:edit',
                        [
                            'contestId' => $contestant->contest_id,
                            'year' => $contestant->year,
                            'id' => $contestant->contestant_id,
                        ],
                    ]
                ),
                'edit'
            );
            $this->addButton(
                new PresenterButton(
                    $this->container,
                    null,
                    new Title(null, _('Detail')),
                    fn(ContestantModel $contestant): array => [
                        ':Organizer:Contestant:detail',
                        [
                            'contestId' => $contestant->contest_id,
                            'year' => $contestant->year,
                            'id' => $contestant->contestant_id,
                        ],
                    ]
                ),
                'detail'
            );
        } else {
            $this->addButton(
                new PresenterButton(
                    $this->container,
                    null,
                    new Title(null, _('Detail')),
                    fn(ContestantModel $contestant): array => [
                        ':Public:Dashboard:default',
                        ['contestId' => $contestant->contest_id, 'year' => $contestant->year],
                    ]
                ),
                'detail'
            );
        }
    }

    protected function getHeadline(): Title
    {
        return new Title(null, _('Contestants'));
    }
}
