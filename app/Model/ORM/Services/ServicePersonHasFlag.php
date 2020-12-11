<?php

namespace FKSDB\Model\ORM\Services;

use DateTime;
use FKSDB\Model\ORM\DbNames;
use FKSDB\Model\ORM\IModel;
use FKSDB\Model\ORM\Models\AbstractModelSingle;
use FKSDB\Model\ORM\Models\ModelPersonHasFlag;
use Fykosak\Utils\ORM\AbstractModel;
use Fykosak\Utils\ORM\Exceptions\ModelException;
use Nette\Database\Context;
use Nette\Database\IConventions;
use Nette\Utils\ArrayHash;

/**
 * @author Lukáš Timko <lukast@fykos.cz>
 */
class ServicePersonHasFlag extends AbstractServiceSingle {

    public function __construct(Context $connection, IConventions $conventions) {
        parent::__construct($connection, $conventions, DbNames::TAB_PERSON_HAS_FLAG, ModelPersonHasFlag::class);
    }

    /**
     * @param null|iterable $data
     * @return ModelPersonHasFlag
     * @throws ModelException
     * @deprecated
     */
    public function createNew(?iterable $data = null) {
        if ($data === null) {
            $data = new ArrayHash();
        }
        $data['modified'] = new DateTime();
        return parent::createNew($data);
    }

    public function createNewModel(array $data): AbstractModelSingle {
        $data['modified'] = new DateTime();
        return parent::createNewModel($data);
    }

    /**
     * @param IModel $model
     * @param array $data
     * @param bool $alive
     * @return mixed|void
     */
    public function updateModel(IModel $model, iterable $data, bool $alive = true): void {
        if ($data === null) {
            $data = new ArrayHash();
        }
        $data['modified'] = new DateTime();
        parent::updateModel($model, $data);
    }

    public function updateModel2(AbstractModel $model, array $data): bool {
        $data['modified'] = new DateTime();
        return parent::updateModel2($model, $data);
    }
}
