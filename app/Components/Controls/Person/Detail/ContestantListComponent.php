<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Person\Detail;

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
        $row0 = $this->createColumnsRow('row0');
        $contestColumn = $row0->createReferencedColumn('contest.name');
        $contestColumn->className .= ' h4';
        $row1 = $this->createColumnsRow('row1');
        $row1->createRendererColumn(
            'contestant__year',
            fn(ContestantModel $contestant): string => \sprintf(_('Contest year %d'), $contestant->year)
        );
        if ($this->isOrg) {
            $this->createDefaultButton('edit', _('Edit'), fn(ContestantModel $contestant): array => [
                ':Org:Contestant:edit',
                [
                    'contestId' => $contestant->contest_id,
                    'year' => $contestant->year,
                    'id' => $contestant->contestant_id,
                ],
            ]);
            $this->createDefaultButton('detail', _('Detail'), fn(ContestantModel $contestant): array => [
                ':Org:Contestant:detail',
                [
                    'contestId' => $contestant->contest_id,
                    'year' => $contestant->year,
                    'id' => $contestant->contestant_id,
                ],
            ]);
        } else {
            $this->createDefaultButton('detail', _('Detail'), fn(ContestantModel $contestant): array => [
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