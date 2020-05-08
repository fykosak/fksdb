<?php

use FKSDB\ORM\AbstractServiceMulti;
use FKSDB\ORM\Services\ServiceFlag;
use FKSDB\ORM\Services\ServicePersonHasFlag;

/**
 * @author Lukáš Timko <lukast@fykos.cz>
 * @method ServiceFlag getMainService()
 * @method ServicePersonHasFlag getJoinedService()
 */
class ServiceMPersonHasFlag extends AbstractServiceMulti {
    /** @var string */
    protected $modelClassName = 'ModelMPersonHasFlag';
    /** @var string */
    protected $joiningColumn = 'flag_id';

    /**
     * ServiceMPersonHasFlag constructor.
     * @param ServiceFlag $mainService
     * @param ServicePersonHasFlag $joinedService
     */
    public function __construct(ServiceFlag $mainService, ServicePersonHasFlag $joinedService) {
        parent::__construct($mainService, $joinedService);
    }

    /**
     * @param null $data
     * @return ModelMPersonHasFlag
     * @throws Exception
     */
    public function createNew($data = null) {
        $mainModel = $this->getMainService()->findByFid($data['fid']);
        if ($mainModel === null) {
            $mainModel = $this->getMainService()->createNew($data);
        }
        $joinedModel = $this->getJoinedService()->createNew($data);

        return new ModelMPersonHasFlag($this, $mainModel, $joinedModel);
    }
}
