<?php

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
 * @property \Nette\DateTime created
 * @property \Nette\DateTime modified
 */
class ModelFyziklaniSubmit extends \AbstractModelSingle {

    /**
     * @return ModelFyziklaniTask
     */
    public function getTask() {
        $data = $this->ref(DbNames::TAB_FYZIKLANI_TASK, 'fyziklani_task_id');
        return ModelFyziklaniTask::createFromTableRow($data);
    }

    /**
     * @return ModelFyziklaniTeam
     */
    public function getTeam() {
        $data = $this->ref(DbNames::TAB_E_FYZIKLANI_TEAM, 'e_fyziklani_team_id');
        return ModelFyziklaniTeam::createFromTableRow($data);
    }

    public function __toArray() {
        return [
            'points' => $this->points,
            'teamId' => $this->e_fyziklani_team_id,
            'taskId' => $this->fyziklani_task_id,
            'created' => $this->created->format(\DATE_ISO8601),
        ];
    }
}
