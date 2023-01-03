<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Person\Detail;

use FKSDB\Models\ORM\FieldLevelPermissionValue;
use FKSDB\Components\Grids\Components\Button\PresenterButton;
use FKSDB\Components\Grids\Components\Container\RowContainer;
use FKSDB\Components\Grids\Components\Referenced\TemplateBaseItem;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\FieldLevelPermission;
use FKSDB\Models\ORM\Models\ContestantModel;
use Fykosak\Utils\UI\Title;
use Nette\Database\Table\Selection;

class ContestantListComponent extends DetailComponent
{
    protected function getMinimalPermissions(): FieldLevelPermissionValue
    {
        return FieldLevelPermissionValue::Restrict;
    }

    protected function getModels(): Selection
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
        $this->setTitle(new TemplateBaseItem($this->container, '@contest.name'));
        $row1 = new RowContainer($this->container, new Title(null, ''));
        $this->addRow($row1, 'row1');
        $row1->addComponent(
            new TemplateBaseItem($this->container, _('Contest year @contestant.year')),
            'contestant__year'
        );
        if ($this->isOrg) {
            $this->addButton(
                new PresenterButton(
                    $this->container,
                    new Title(null, _('Edit')),
                    fn(ContestantModel $contestant): array => [
                        ':Org:Contestant:edit',
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
                    new Title(null, _('Detail')),
                    fn(ContestantModel $contestant): array => [
                        ':Org:Contestant:detail',
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
