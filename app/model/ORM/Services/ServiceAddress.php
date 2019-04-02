<?php

namespace FKSDB\ORM\Services;

use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\AbstractServiceSingle;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\IModel;
use FKSDB\ORM\Models\ModelAddress;
use FKSDB\ORM\Models\ModelRegion;
use InvalidPostalCode;
use Nette\InvalidArgumentException;
use Tracy\Debugger;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ServiceAddress extends AbstractServiceSingle {

    const PATTERN = '/[0-9]{5}/';

    /**
     * @return string
     */
    protected function getModelClassName(): string {
        return ModelAddress::class;
    }

    /**
     * @return string
     */
    protected function getTableName(): string {
        return DbNames::TAB_ADDRESS;
    }

    /**
     * @param \FKSDB\ORM\IModel $model
     * @return mixed|void
     */
    public function save(IModel &$model) {
        $modelClassName = $this->getModelClassName();
        if (!$model instanceof $modelClassName) {
            throw new InvalidArgumentException('Service for class ' . $this->getModelClassName() . ' cannot store ' . get_class($model));
        }
        /**
         * @var \FKSDB\ORM\Models\ModelAddress $model
         */
        if (!isset($model->region_id)) {
            $model->region_id = $this->inferRegion($model->postal_code);
        }
        parent::save($model);
    }

    /**
     * @param null $data
     * @return ModelAddress
     */
    public function createNewModel($data = null): AbstractModelSingle {
        if (!isset($data['region_id'])) {
            $data['region_id'] = $this->inferRegion($data['postal_code']);
        }
        return parent::createNewModel($data);
    }

    /**
     * @param ModelAddress|AbstractModelSingle $model
     * @param $data
     * @return int
     */
    public function updateModel2(AbstractModelSingle $model, $data = null) {
        if (!isset($data['region_id'])) {
            $data['region_id'] = $this->inferRegion($data['postal_code']);
        }
        return parent::updateModel2($model, $data);
    }


    /**
     *
     * @param string $postalCode
     * @return int
     * @throws InvalidPostalCode
     */
    public function inferRegion($postalCode) {
        if (!$postalCode) {
            throw new InvalidPostalCode($postalCode);
        }

        if (!preg_match(self::PATTERN, $postalCode)) {
            throw new InvalidPostalCode($postalCode);
        }

        $row = $this->getTable()->getConnection()->table('psc_region')->where('psc = ?', $postalCode)->fetch();
        if ($row) {
            return $row->region_id;
        } else {
            if (strlen($postalCode) != 5) {
                throw new InvalidPostalCode($postalCode);
            }
            Debugger::log("Czechoslovak PSC not found '$postalCode'", Debugger::WARNING);
            $firstChar = substr($postalCode, 0, 1);

            if (in_array($firstChar, ['1', '2', '3', '4', '5', '6', '7'])) {
                return ModelRegion::CZECH_REPUBLIC;
            } else if (in_array($firstChar, ['8', '9', '0'])) {
                return ModelRegion::SLOVAKIA;
            } else {
                throw new InvalidPostalCode($postalCode);
            }
        }
    }

    /**
     *
     * @param string $postalCode
     * @return boolean
     */
    public function tryInferRegion($postalCode): bool {
        try {
            $this->inferRegion($postalCode);
            return true;
        } catch (InvalidPostalCode $exception) {
            return false;
        }
    }

}
