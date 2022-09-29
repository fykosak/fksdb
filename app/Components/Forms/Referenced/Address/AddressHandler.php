<?php

declare(strict_types=1);

namespace FKSDB\Components\Forms\Referenced\Address;

use FKSDB\Components\Forms\Referenced\ReferencedHandler;
use FKSDB\Models\ORM\Models\AddressModel;
use FKSDB\Models\ORM\Models\PSCRegionModel;
use FKSDB\Models\ORM\Models\RegionModel;
use FKSDB\Models\ORM\Services\AddressService;
use FKSDB\Models\ORM\Services\Exceptions\InvalidAddressException;
use FKSDB\Models\ORM\Services\Exceptions\InvalidPostalCode;
use FKSDB\Models\ORM\Services\PSCRegionService;
use FKSDB\Models\ORM\Services\RegionService;
use FKSDB\Models\Utils\FormUtils;
use Fykosak\NetteORM\Model;
use Nette\DI\Container;
use Tracy\Debugger;

class AddressHandler implements ReferencedHandler
{
    private const PATTERN = '/[0-9]{5}/';

    private AddressService $addressService;
    private PSCRegionService $PSCRegionService;
    private RegionService $regionService;

    public function __construct(Container $container)
    {
        $container->callInjects($this);
    }

    public function inject(
        AddressService $addressService,
        PSCRegionService $PSCRegionService,
        RegionService $regionService
    ): void {
        $this->addressService = $addressService;
        $this->PSCRegionService = $PSCRegionService;
        $this->regionService = $regionService;
    }

    public function getResolution(): string
    {
        return ReferencedHandler::RESOLUTION_OVERWRITE;
    }

    public function setResolution(string $resolution): void
    {
        // void
    }

    public function update(Model $model, array $values): void
    {
        $this->store($model, $values);
    }

    public function createFromValues(array $values): AddressModel
    {
        return $this->store(null, $values);
    }

    private function store(?AddressModel $model, array $values): AddressModel
    {
        $data = FormUtils::removeEmptyValues(FormUtils::emptyStrToNull2($values));

        $region = null;
        if (isset($data['postal_code'])) {
            $region = $this->inferRegion($data['postal_code'], true);
        }
        if (isset($data['region_id'])) {
            $region = $this->regionService->findByPrimary($data['region_id']);
        }
        if (!$region) {
            throw new InvalidAddressException(_('Cannot infer region'));
        }
        $data['region_id'] = $region->region_id;

        return $this->addressService->storeModel($data, $model);
    }

    /**
     * @throws InvalidPostalCode
     */
    private function inferRegion(?string $postalCode, bool $throw = false): ?RegionModel
    {
        if (!$postalCode) {
            if ($throw) {
                throw new InvalidPostalCode($postalCode);
            }
            return null;
        }

        if (!preg_match(self::PATTERN, $postalCode)) {
            if ($throw) {
                throw new InvalidPostalCode($postalCode);
            }
            return null;
        }
        /** @var PSCRegionModel $pscRegion */
        $pscRegion = $this->PSCRegionService->findByPrimary($postalCode);
        if ($pscRegion) {
            return $pscRegion->region;
        } else {
            if (strlen($postalCode) != 5) {
                if ($throw) {
                    throw new InvalidPostalCode($postalCode);
                }
                return null;
            }
            Debugger::log("Czechoslovak PSC not found '$postalCode'", Debugger::WARNING);
            $firstChar = substr($postalCode, 0, 1);

            if (in_array($firstChar, ['1', '2', '3', '4', '5', '6', '7'])) {
                return $this->regionService->findByPrimary(RegionModel::CZECH_REPUBLIC);
            } elseif (in_array($firstChar, ['8', '9', '0'])) {
                return $this->regionService->findByPrimary(RegionModel::SLOVAKIA);
            }
        }
        if ($throw) {
            throw new InvalidPostalCode($postalCode);
        }
        return null;
    }
}
