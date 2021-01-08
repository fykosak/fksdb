<?php

namespace FKSDB\Model\ORM\Services;

use FKSDB\Model\ORM\DbNames;
use FKSDB\Model\ORM\IModel;
use FKSDB\Model\ORM\Models\AbstractModelSingle;
use FKSDB\Model\ORM\Models\ModelAddress;
use FKSDB\Model\ORM\Models\ModelRegion;
use FKSDB\Model\ORM\Services\Exceptions\InvalidPostalCode;
use Nette\Database\Context;
use Nette\Database\IConventions;
use Tracy\Debugger;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ServiceAddress extends AbstractServiceSingle {
    use DeprecatedLazyService;

    private const PATTERN = '/[0-9]{5}/';

    public function __construct(Context $connection, IConventions $conventions) {
        parent::__construct($connection, $conventions, DbNames::TAB_ADDRESS, ModelAddress::class);
    }

    /**
     * @param array $data
     * @return ModelAddress
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
