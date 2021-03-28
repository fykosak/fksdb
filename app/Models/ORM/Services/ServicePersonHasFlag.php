<?php

namespace FKSDB\Models\ORM\Services;

use DateTime;
use Fykosak\NetteORM\Exceptions\ModelException;
use FKSDB\Models\ORM\IModel;
use Fykosak\NetteORM\AbstractModel;
use FKSDB\Models\ORM\Models\ModelPersonHasFlag;
use Nette\Utils\ArrayHash;

/**
 * @author Lukáš Timko <lukast@fykos.cz>
 */
class ServicePersonHasFlag extends OldAbstractServiceSingle {

    /**
     * @param null|iterable $data
     * @return ModelPersonHasFlag
     * @throws ModelException
     * @deprecated
     */
    public function createNew(?iterable $data = null): ModelPersonHasFlag {
        if ($data === null) {
            $data = new ArrayHash();
        }
        $data['modified'] = new DateTime();
        return parent::createNew($data);
    }

    public function createNewModel(array $data): AbstractModel {
        $data['modified'] = new DateTime();
        return parent::createNewModel($data);
    }

    public function updateModelLegacy(IModel $model, ?iterable $data, bool $alive = true): void {
        if ($data === null) {
            $data = new ArrayHash();
        }
        $data['modified'] = new DateTime();
        parent::updateModelLegacy($model, $data);
    }

    public function updateModel(AbstractModel $model, array $data): bool {
        $data['modified'] = new DateTime();
        return parent::updateModel($model, $data);
    }
}
