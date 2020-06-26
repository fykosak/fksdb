<?php

namespace FKSDB\ORM\Models\Fyziklani;

use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\Models\IEventReferencedModel;
use FKSDB\ORM\Models\IFyziklaniTaskReferencedModel;
use FKSDB\ORM\Models\IFyziklaniTeamReferencedModel;
use FKSDB\ORM\Models\ModelEvent;
use Nette\Database\Table\ActiveRow;
use Nette\Security\IResource;

/**
 *
 * @author Lukáš Timko <lukast@fykos.cz>
 * @author Michal Červeňák <miso@fykos.cz>
 *
 * @property-read string state
 * @property-read int e_fyziklani_team_id
 * @property-read int points
 * @property-read int fyziklani_task_id
 * @property-read int fyziklani_submit_id
 * @property-read int task_id
 * @property-read ActiveRow e_fyziklani_team
 * @property-read ActiveRow fyziklani_task
 * @property-read \DateTimeInterface created
 * @property-read \DateTimeInterface modified
 */
class ModelFyziklaniSubmit extends AbstractModelSingle implements IFyziklaniTeamReferencedModel, IEventReferencedModel, IFyziklaniTaskReferencedModel, IResource {
    const STATE_NOT_CHECKED = 'not_checked';
    const STATE_CHECKED = 'checked';

    const RESOURCE_ID = 'fyziklani.submit';

    public function getFyziklaniTask(): ModelFyziklaniTask {
        return ModelFyziklaniTask::createFromActiveRow($this->fyziklani_task);
    }

    public function getEvent(): ModelEvent {
        return $this->getFyziklaniTeam()->getEvent();
    }

    public function getFyziklaniTeam(): ModelFyziklaniTeam {
        return ModelFyziklaniTeam::createFromActiveRow($this->e_fyziklani_team);
    }

    public function isChecked(): bool {
        return $this->state === self::STATE_CHECKED;
    }

    public function __toArray(): array {
        return [
            'points' => $this->points,
            'teamId' => $this->e_fyziklani_team_id,
            'taskId' => $this->fyziklani_task_id,
            'created' => $this->created->format('c'),
        ];
    }

    public function canRevoke(): bool {
        return $this->canChange() && !is_null($this->points);
    }

    public function canChange(): bool {
        return $this->getFyziklaniTeam()->hasOpenSubmitting();
    }

    public function getResourceId(): string {
        return self::RESOURCE_ID;
    }
}
