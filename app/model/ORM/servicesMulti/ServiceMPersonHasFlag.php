<?php

/**
 * @author Lukáš Timko <lukast@fykos.cz>
 */
class ServiceMPersonHasFlag extends AbstractServiceMulti {

    protected $modelClassName = 'ModelMPersonHasFlag';
    protected $joiningColumn = 'flag_id';

    public function __construct(ServiceFlag $mainService, ServicePersonHasFlag $joinedService) {
        parent::__construct($mainService, $joinedService);
    }

    public function createNew($data = null) {
        $mainModel = $this->getMainService()->findByFid($data['fid']);
        if ($mainModel === null) {
            $mainModel = $this->getMainService()->createNew($data);
        }
        $joinedModel = $this->getJoinedService()->createNew($data);

        $result = new ModelMPersonHasFlag($this, $mainModel, $joinedModel);
        return $result;
    }
}
