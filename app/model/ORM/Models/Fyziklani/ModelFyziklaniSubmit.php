<?php

namespace FKSDB\ORM\Models\Fyziklani;

use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\Models\IEventReferencedModel;
use FKSDB\ORM\Models\IFyziklaniTeamReferencedModel;
use FKSDB\ORM\Models\ModelEvent;
use Nette\Database\Table\ActiveRow;
use Nette\Utils\DateTime;

/**
 *
 * @author Lukáš Timko <lukast@fykos.cz>
 * @author Michal Červeňák <miso@fykos.cz>
 *
 * @property-read string state
 * @property-read integer e_fyziklani_team_id
 * @property-read integer points
 * @property-read integer fyziklani_task_id
 * @property-read integer fyziklani_submit_id
 * @property-read integer task_id
 * @property-read ActiveRow e_fyziklani_team
 * @property-read ActiveRow fyziklani_task
 * @property-read DateTime created
 * @property-read DateTime modified
 */
class ModelFyziklaniSubmit extends AbstractModelSingle implements IFyziklaniTeamReferencedModel, IEventReferencedModel {
    const STATE_NOT_CHECKED = 'not_checked';
    const STATE_CHECKED = 'checked';

    /**
     * @return ModelFyziklaniTask
     */
    public function getTask(): ModelFyziklaniTask {
        return ModelFyziklaniTask::createFromActiveRow($this->fyziklani_task);
    }

    /**
     * @return ModelEvent
     */
    public function getEvent(): ModelEvent {
        return $this->getFyziklaniTeam()->getEvent();
    }

    /**
     * @return ModelFyziklaniTeam
     * @deprecated
     */
    public function getTeam(): ModelFyziklaniTeam {
        return $this->getFyziklaniTeam();
    }

    /**
     * @return ModelFyziklaniTeam
     */
    public function getFyziklaniTeam(): ModelFyziklaniTeam {
        return ModelFyziklaniTeam::createFromActiveRow($this->e_fyziklani_team);
    }

    /**
     * @return bool
     */
    public function isChecked(): bool {
        return $this->state === self::STATE_CHECKED;
    }

    /**
     * @return array
     */
    public function __toArray(): array {
        return [
            'points' => $this->points,
            'teamId' => $this->e_fyziklani_team_id,
            'taskId' => $this->fyziklani_task_id,
            'created' => $this->created->format('c'),
        ];
    }

    /**
     * @return bool
     */
    public function canChange(): bool {
        return $this->getTeam()->hasOpenSubmitting();
    }
}
