<?php

namespace FKSDB\Models\ORM\Services;

use FKSDB\Models\Exceptions\ModelException;
use FKSDB\Models\ORM\DbNames;
use FKSDB\Models\ORM\DeprecatedLazyDBTrait;
use FKSDB\Models\ORM\IModel;
use FKSDB\Models\ORM\Models\AbstractModelSingle;
use FKSDB\Models\ORM\Models\ModelAddress;
use FKSDB\Models\ORM\Models\ModelRegion;
use FKSDB\Models\ORM\Services\Exceptions\InvalidPostalCode;
use Tracy\Debugger;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ServiceAddress extends AbstractServiceSingle {

    use DeprecatedLazyDBTrait;

    private const PATTERN = '/[0-9]{5}/';

    /**
     * @param array $data
     * @return ModelAddress
     * @throws ModelException
     */
    public function createNewModel(array $data): AbstractModelSingle {
        if (!isset($data['region_id'])) {
            $data['region_id'] = $this->inferRegion($data['postal_code']);
        }
        return parent::createNewModel($data);
    }

    public function updateModel2(IModel $model, array $data): bool {
        if (!isset($data['region_id'])) {
            $data['region_id'] = $this->inferRegion($data['postal_code']);
        }
        return parent::updateModel2($model, $data);
    }

    /**
     *
     * @param string|null $postalCode
     * @return int
     * @throws InvalidPostalCode
     */
    public function inferRegion(?string $postalCode): int {
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

    public function tryInferRegion(?string $postalCode): bool {
        try {
            $this->inferRegion($postalCode);
            return true;
        } catch (InvalidPostalCode $exception) {
            return false;
        }
    }
}
