<?php

namespace FKSDB\ORM\Models\Fyziklani;

use FKSDB\ORM\AbstractModelSingle;
use Nette\Database\Table\ActiveRow;
use Nette\Utils\DateTime;

/**
 *
 * @author Lukáš Timko <lukast@fykos.cz>
 * @author Michal Červeňák <miso@fykos.cz>
 * @property-readinteger e_fyziklani_team_id
 * @property-readinteger points
 * @property-readinteger fyziklani_task_id
 * @property-readinteger fyziklani_submit_id
 * @property-readinteger task_id
 * @property-readActiveRow e_fyziklani_team
 * @property-readActiveRow fyziklani_task
 * @property-readDateTime created
 * @property-readDateTime modified
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
