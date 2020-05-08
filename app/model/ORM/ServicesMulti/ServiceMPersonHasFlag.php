<?php

namespace FKSDB\ORM\ServicesMulti;

use FKSDB\ORM\AbstractServiceMulti;
use FKSDB\ORM\ModelsMulti\ModelMPersonHasFlag;
use FKSDB\ORM\Services\ServiceFlag;
use FKSDB\ORM\Services\ServicePersonHasFlag;

/**
 * @author Lukáš Timko <lukast@fykos.cz>
 * @method ServiceFlag getMainService()
 * @method ServicePersonHasFlag getJoinedService()
 */
class ServiceMPersonHasFlag extends AbstractServiceMulti {
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
     * @throws \Exception
     */
    public function createNew($data = null) {
        $mainModel = $this->getMainService()->findByFid($data['fid']);
        if ($mainModel === null) {
            $mainModel = $this->getMainService()->createNew($data);
        }
        $joinedModel = $this->getJoinedService()->createNew($data);

        return new ModelMPersonHasFlag($this, $mainModel, $joinedModel);
    }

    public function getJoiningColumn(): string {
        return 'flag_id';
    }

    public function getModelClassName(): string {
        return ModelMPersonHasFlag::class;
    }
}
