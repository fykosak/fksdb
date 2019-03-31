<?php

namespace FKSDB\ORM\Services;

use DateTime;
use FKSDB\ORM\AbstractServiceSingle;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\IModel;
use FKSDB\ORM\Models\ModelPersonHasFlag;
use Nette\ArrayHash;

/**
 * @author Lukáš Timko <lukast@fykos.cz>
 */
class ServicePersonHasFlag extends AbstractServiceSingle {
    /**
     * @return string
     */
    protected function getModelClassName(): string {
        return ModelPersonHasFlag::class;
    }

    /**
     * @return string
     */
    protected function getTableName(): string {
        return DbNames::TAB_PERSON_HAS_FLAG;
    }

    /**
     * @param null $data
     * @return \FKSDB\ORM\AbstractModelSingle
     */
    public function createNew($data = null) {
        if ($data === null) {
            $data = new ArrayHash();
        }
        $data['modified'] = new DateTime();
        return parent::createNew($data);
    }
}
