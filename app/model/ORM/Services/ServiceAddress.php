<?php

namespace FKSDB\ORM\Services;

use FKSDB\ORM\AbstractServiceSingle;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\DeprecatedLazyDBTrait;
use FKSDB\ORM\IModel;
use FKSDB\ORM\Models\ModelAddress;
use FKSDB\ORM\Models\ModelRegion;
use FKSDB\ORM\Services\Exception\InvalidPostalCode;
use Tracy\Debugger;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ServiceAddress extends AbstractServiceSingle {
    use DeprecatedLazyDBTrait;

    const PATTERN = '/[0-9]{5}/';

    public function getModelClassName(): string {
        return ModelAddress::class;
    }

    protected function getTableName(): string {
        return DbNames::TAB_ADDRESS;
    }

    /**
     * @param array $data
     * @return ModelAddress
     */
    public function createNewModel(array $data): IModel {
        if (!isset($data['region_id']) || is_null($data['region_id'])) {
            $data['region_id'] = $this->inferRegion($data['postal_code']);
        }
        return parent::createNewModel($data);
    }

    public function updateModel2(IModel $model, array $data): bool {
        if (!isset($data['region_id']) || is_null($data['region_id'])) {
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
        $row = $this->getContext()->table(DbNames::TAB_PSC_REGION)->where('psc = ?', $postalCode)->fetch();
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
            } elseif (in_array($firstChar, ['8', '9', '0'])) {
                return ModelRegion::SLOVAKIA;
            } else {
                throw new InvalidPostalCode($postalCode);
            }
        }
    }

    /**
     *
     * @param string $postalCode
     * @return bool
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
