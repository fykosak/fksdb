<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Person\Detail;

use FKSDB\Components\Grids\ListComponent\Button\DefaultButton;
use FKSDB\Components\Grids\ListComponent\Container\RowContainer;
use FKSDB\Components\Grids\ListComponent\Referenced\TemplateItem;
use FKSDB\Models\ORM\FieldLevelPermission;
use FKSDB\Models\ORM\Models\ContestantModel;
use Fykosak\Utils\UI\Title;

class ContestantListComponent extends BaseListComponent
{
    protected function getMinimalPermissions(): int
    {
        return FieldLevelPermission::ALLOW_RESTRICT;
    }

    protected function getModels(): iterable
    {
        return $this->person->getContestants();
    }

    protected function configure(): void
    {
        $this->classNameCallback = fn(ContestantModel $contestant): string => 'alert alert-' .
            $contestant->contest->getContestSymbol();
        $this->setTitle(new TemplateItem($this->container, '@contest.name'));
        $row1 = new RowContainer($this->container);
        $this->addComponent($row1, 'row1');
        $row1->addComponent(new TemplateItem($this->container, _('Contest year @contestant.year')), 'contestant__year');
        if ($this->isOrg) {
            $this->addButton(
                new DefaultButton($this->container, _('Edit'), fn(ContestantModel $contestant): array => [
                    ':Org:Contestant:edit',
                    [
                        'contestId' => $contestant->contest_id,
                        'year' => $contestant->year,
                        'id' => $contestant->contestant_id,
                    ],
                ]),
                'edit'
            );
            $this->addButton(
                new DefaultButton($this->container, _('Detail'), fn(ContestantModel $contestant): array => [
                    ':Org:Contestant:detail',
                    [
                        'contestId' => $contestant->contest_id,
                        'year' => $contestant->year,
                        'id' => $contestant->contestant_id,
                    ],
                ]),
                'detail'
            );
        } else {
            $this->addButton(
                new DefaultButton($this->container, _('Detail'), fn(ContestantModel $contestant): array => [
                    ':Public:Dashboard:default',
                    ['contestId' => $contestant->contest_id, 'year' => $contestant->year],
                ]),
                'detail'
            );
        }
    }

    protected function getTitle(): Title
    {
        return new Title(null, _('Contestants'));
    }
}
