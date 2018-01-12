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
 * @property DateTime created
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
}
