<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Person\Detail;

use FKSDB\Models\ORM\FieldLevelPermissionValue;
use FKSDB\Models\ORM\Models\ContestantModel;
use Fykosak\Utils\UI\Title;

class ContestantListComponent extends BaseListComponent
{
    protected function getMinimalPermissions(): FieldLevelPermissionValue
    {
        return FieldLevelPermissionValue::Restrict;
    }

    protected function getModels(): iterable
    {
        return $this->person->getContestants();
    }

    protected function configure(): void
    {
        $this->classNameCallback = fn(ContestantModel $contestant) => 'alert alert-' .
            $contestant->contest->getContestSymbol();
        $row0 = $this->createColumnsRow('row0');
        $contestColumn = $row0->createReferencedColumn('contest.name');
        $contestColumn->className .= ' h4';
        $row1 = $this->createColumnsRow('row1');
        $row1->createRendererColumn(
            'contestant__year',
            fn(ContestantModel $contestant) => \sprintf(_('Contest year %d'), $contestant->year)
        );
        if ($this->isOrg) {
            $this->createDefaultButton('edit', _('Edit'), fn(ContestantModel $contestant) => [
                ':Org:Contestant:edit',
                [
                    'contestId' => $contestant->contest_id,
                    'year' => $contestant->year,
                    'id' => $contestant->contestant_id,
                ],
            ]);
            $this->createDefaultButton('detail', _('Detail'), fn(ContestantModel $contestant) => [
                ':Org:Contestant:detail',
                [
                    'contestId' => $contestant->contest_id,
                    'year' => $contestant->year,
                    'id' => $contestant->contestant_id,
                ],
            ]);
        } else {
            $this->createDefaultButton('detail', _('Detail'), fn(ContestantModel $contestant) => [
                ':Public:Dashboard:default',
                ['contestId' => $contestant->contest_id, 'year' => $contestant->year],
            ]);
        }
    }

    protected function getTitle(): Title
    {
        return new Title(null, _('Contestants'));
    }
}
