<?php

/**
 *
 * @author Lukáš Timko <lukast@fykos.cz>
 */
class ModelFyziklaniSubmit extends AbstractModelSingle {
    
    /**
     * @return ModelFyziklaniTask
     */
    public function getTask() {
        $data = $this->fyziklani_task;
        return ModelFyziklaniTask::createFromTableRow($data);
    }
    
    /**
     * @return ORM\Models\Events\ModelFyziklaniTeam
     */
    public function getTeam() {
        $data = $this->e_fyziklani_team;
        return ORM\Models\Events\ModelFyziklaniTeam::createFromTableRow($data);
    }
}
