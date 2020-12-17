<?php

namespace FKSDB\Model\ORM\Services\Fyziklani;

use FKSDB\ORM\DeprecatedLazyService;
use FKSDB\Model\ORM\DbNames;
use FKSDB\Model\ORM\Models\Fyziklani\ModelFyziklaniSubmit;
use FKSDB\Model\ORM\Models\Fyziklani\ModelFyziklaniTask;
use FKSDB\Model\ORM\Models\Fyziklani\ModelFyziklaniTeam;
use FKSDB\Model\ORM\Models\ModelEvent;
use FKSDB\Model\ORM\Services\AbstractServiceSingle;
use FKSDB\Model\ORM\Tables\TypedTableSelection;
use Nette\Database\Context;
use Nette\Database\IConventions;

/**
 * @author Lukáš Timko <lukast@fykos.cz>
 * @method ModelFyziklaniSubmit createNewModel(array $data)
 */
class ServiceFyziklaniSubmit extends AbstractServiceSingle {
    use DeprecatedLazyService;

    public function __construct(Context $connection, IConventions $conventions) {
        parent::__construct($connection, $conventions, DbNames::TAB_FYZIKLANI_SUBMIT, ModelFyziklaniSubmit::class);
    }

    public function findByTaskAndTeam(ModelFyziklaniTask $task, ModelFyziklaniTeam $team): ?ModelFyziklaniSubmit {
        /** @var ModelFyziklaniSubmit $row */
        $row = $this->getTable()->where([
            'fyziklani_task_id' => $task->fyziklani_task_id,
            'e_fyziklani_team_id' => $team->e_fyziklani_team_id,
        ])->fetch();
        return $row ?: null;
    }

    public function findAll(ModelEvent $event): TypedTableSelection {
        return $this->getTable()->where('e_fyziklani_team_id.event_id', $event->event_id);
    }

    public function getSubmitsAsArray(ModelEvent $event, ?string $lastUpdated): array {
        $query = $this->getTable()->where('e_fyziklani_team.event_id', $event->event_id);
        $submits = [];
        if ($lastUpdated) {
            $query->where('modified >= ?', $lastUpdated);
        }
        foreach ($query as $row) {
            $submit = ModelFyziklaniSubmit::createFromActiveRow($row);
            $submits[$submit->fyziklani_submit_id] = $submit->__toArray();
        }
        return $submits;
    }
}