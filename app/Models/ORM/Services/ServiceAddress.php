<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Services;

use Fykosak\NetteORM\Service;
use Fykosak\NetteORM\Exceptions\ModelException;
use FKSDB\Models\ORM\DbNames;
use Fykosak\NetteORM\Model;
use FKSDB\Models\ORM\Models\AddressModel;
use FKSDB\Models\ORM\Models\RegionModel;
use FKSDB\Models\ORM\Services\Exceptions\InvalidPostalCode;
use Nette\Database\Table\ActiveRow;
use Tracy\Debugger;

class ServiceAddress extends Service
{

    private const PATTERN = '/[0-9]{5}/';

    /**
     * @throws ModelException
     */
    public function createNewModel(array $data): AddressModel
    {
        if (!isset($data['region_id'])) {
            $data['region_id'] = $this->inferRegion($data['postal_code']);
        }
        return parent::createNewModel($data);
    }

    public function updateModel(Model $model, array $data): bool
    {
        if (!isset($data['region_id'])) {
            $data['region_id'] = $this->inferRegion($data['postal_code']);
        }
        return parent::updateModel($model, $data);
    }

    /**
     * @throws InvalidPostalCode
     */
    public function inferRegion(?string $postalCode): int
    {
        if (!$postalCode) {
            throw new InvalidPostalCode($postalCode);
        }

        if (!preg_match(self::PATTERN, $postalCode)) {
            throw new InvalidPostalCode($postalCode);
        }
        /** @var ActiveRow|RegionModel $row */
        $row = $this->explorer->table(DbNames::TAB_PSC_REGION)->where('psc = ?', $postalCode)->fetch();
        if ($row) {
            return $row->region_id;
        } else {
            if (strlen($postalCode) != 5) {
                throw new InvalidPostalCode($postalCode);
            }
            Debugger::log("Czechoslovak PSC not found '$postalCode'", Debugger::WARNING);
            $firstChar = substr($postalCode, 0, 1);

            if (in_array($firstChar, ['1', '2', '3', '4', '5', '6', '7'])) {
                return RegionModel::CZECH_REPUBLIC;
            } elseif (in_array($firstChar, ['8', '9', '0'])) {
                return RegionModel::SLOVAKIA;
            } else {
                throw new InvalidPostalCode($postalCode);
            }
        }
    }

    public function tryInferRegion(?string $postalCode): bool
    {
        try {
            $this->inferRegion($postalCode);
            return true;
        } catch (InvalidPostalCode $exception) {
            return false;
        }
    }
}
