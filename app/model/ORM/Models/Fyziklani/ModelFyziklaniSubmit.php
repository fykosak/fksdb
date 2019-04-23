<?php

namespace FKSDB\ORM\Models\Fyziklani;

use FKSDB\ORM\AbstractModelSingle;
use Nette\Database\Table\ActiveRow;
use Nette\Utils\DateTime;

/**
 *
 * @author Lukáš Timko <lukast@fykos.cz>
 * @author Michal Červeňák <miso@fykos.cz>
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
class ModelFyziklaniSubmit extends AbstractModelSingle {

    /**
     * @return ModelFyziklaniTask
     */
    public function getTask(): ModelFyziklaniTask {
        return ModelFyziklaniTask::createFromTableRow($this->fyziklani_task);
    }

    /**
     * @return ModelFyziklaniTeam
     */
    public function getTeam(): ModelFyziklaniTeam {
        return ModelFyziklaniTeam::createFromTableRow($this->e_fyziklani_team);
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
}
