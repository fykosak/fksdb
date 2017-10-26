<?php

use ORM\Models\Events\ModelBrawlTeam;

/**
 *
 * @author Lukáš Timko <lukast@fykos.cz>
 * @author Michal Červeňák <miso@fykos.cz>
 * @property integer e_fyziklani_team_id
 * @property integer points
 * @property integer fyziklani_task_id
 * @property integer fyziklani_submit_id
 */
class ModelBrawlSubmit extends AbstractModelSingle {
    
    /**
     * @return ModelBrawlTask
     */
    public function getTask() {
        $data = $this->ref(DbNames::TAB_FYZIKLANI_TASK, 'fyziklani_task_id');
        return ModelBrawlTask::createFromTableRow($data);
    }
    
    /**
     * @return \ORM\Models\Events\ModelFyziklaniTeam
     */
    public function getTeam() {
        $data = $this->ref(DbNames::TAB_E_FYZIKLANI_TEAM, 'e_fyziklani_team_id');
        return \ORM\Models\Events\ModelFyziklaniTeam::createFromTableRow($data);
    }
}
