<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Tests\Event\Team;

use FKSDB\Models\ORM\Tests\Adapter;
use FKSDB\Models\ORM\Models\Fyziklani\TeamMemberModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use FKSDB\Models\ORM\Models\PersonHistoryModel;
use Fykosak\NetteORM\Model\Model;

/**
 * @phpstan-extends Adapter<TeamModel2,PersonHistoryModel>
 */
class TeamToPersonHistoryAdapter extends Adapter
{
    protected function getModels(Model $model): iterable
    {
        $models = [];
        $members = $model->getMembers();
        /** @var TeamMemberModel $member */
        foreach ($members as $member) {
            $history = $member->getPersonHistory();
            if ($history) {
                $models[] = $history;
            }
        }
        return $models;
    }

    protected function getLogPrepend(Model $model): string
    {
        return sprintf(_('In person "%s"(%d) in current year: '), $model->person->getFullName(), $model->person_id);
    }

    public function getId(): string
    {
        return 'PersonHistory' . $this->test->getId();
    }
}
