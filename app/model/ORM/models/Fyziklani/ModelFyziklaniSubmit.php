<?php

namespace FKSDB\ORM\Models\Fyziklani;

use Nette\Database\Table\ActiveRow;
use Nette\DateTime;
use ORM\Models\Events\ModelFyziklaniTeam;

/**
 *
 * @author Lukáš Timko <lukast@fykos.cz>
 * @author Michal Červeňák <miso@fykos.cz>
 * @property integer e_fyziklani_team_id
 * @property integer points
 * @property integer fyziklani_task_id
 * @property integer fyziklani_submit_id
 * @property integer task_id
 * @property ActiveRow e_fyziklani_team
 * @property ActiveRow fyziklani_task
 * @property DateTime created
 * @property DateTime modified
 */
class ModelFyziklaniSubmit extends \AbstractModelSingle {

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
