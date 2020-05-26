<?php

namespace FKSDB\ORM\ServicesMulti;

use FKSDB\ORM\AbstractModelMulti;
use FKSDB\ORM\AbstractServiceMulti;
use FKSDB\ORM\ModelsMulti\ModelMStoredQueryTag;
use FKSDB\ORM\Services\StoredQuery\ServiceStoredQueryTag;
use FKSDB\ORM\Services\StoredQuery\ServiceStoredQueryTagType;
use Nette\InvalidArgumentException;

/**
 * @author Lukáš Timko <lukast@fykos.cz>
 */
class ServiceMStoredQueryTag extends AbstractServiceMulti {
    /**
     * ServiceMStoredQueryTag constructor.
     * @param ServiceStoredQueryTagType $mainService
     * @param ServiceStoredQueryTag $joinedService
     */
    public function __construct(ServiceStoredQueryTagType $mainService, ServiceStoredQueryTag $joinedService) {
        parent::__construct($mainService, $joinedService);
    }

    /**
     * @param null $data
     * @return AbstractModelMulti|ModelMStoredQueryTag
     */
    public function createNew($data = null) {
        $mainModel = $this->getMainService()->findByPrimary($data['tag_type_id']);
        if ($mainModel === null) {
            throw new InvalidArgumentException();
        }
        $joinedModel = $this->getJoinedService()->createNew($data);

        return new ModelMStoredQueryTag($this, $mainModel, $joinedModel);
    }

    public function getJoiningColumn(): string {
        return 'tag_type_id';
    }

    public function getModelClassName(): string {
        return ModelMStoredQueryTag::class;
    }
}
