<?php

use FKSDB\ORM\ModelRegion;
use Nette\Diagnostics\Debugger;
use Nette\InvalidArgumentException;
use ORM\IModel;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ServiceAddress extends AbstractServiceSingle {

    const PATTERN = '/[0-9]{5}/';

    protected $tableName = DbNames::TAB_ADDRESS;
    protected $modelClassName = 'FKSDB\ORM\ModelAddress';

    public function save(IModel &$model) {
        if (!$model instanceof $this->modelClassName) {
            throw new InvalidArgumentException('Service for class ' . $this->modelClassName . ' cannot store ' . get_class($model));
        }
        if (!isset($model->region_id)) {
            $model->region_id = $this->inferRegion($model->postal_code);
        }
        parent::save($model);
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
        } catch (InvalidPostalCode $e) {
            return false;
        }
    }

}
