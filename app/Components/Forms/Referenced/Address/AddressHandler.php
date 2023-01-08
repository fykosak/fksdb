<?php

declare(strict_types=1);

namespace FKSDB\Components\Forms\Referenced\Address;

use FKSDB\Models\ORM\Models\AddressModel;
use FKSDB\Models\ORM\Models\PSCSubdivisionModel;
use FKSDB\Models\ORM\Services\AddressService;
use FKSDB\Models\ORM\Services\CountryService;
use FKSDB\Models\ORM\Services\Exceptions\InvalidAddressException;
use FKSDB\Models\ORM\Services\PSCSubdivisionService;
use FKSDB\Models\Persons\ReferencedHandler;
use FKSDB\Models\Utils\FormUtils;
use Fykosak\NetteORM\Model;
use Nette\DI\Container;
use Tracy\Debugger;

class AddressHandler extends ReferencedHandler
{
    private const PATTERN = '/[0-9]{5}/';

    private AddressService $addressService;
    private PSCSubdivisionService $PSCSubdivisionService;

    public function __construct(Container $container)
    {
        $container->callInjects($this);
    }

    public function inject(AddressService $addressService, PSCSubdivisionService $PSCSubdivisionService): void
    {
        $this->addressService = $addressService;
        $this->PSCSubdivisionService = $PSCSubdivisionService;
    }

    public function store(array $values, ?Model $model = null): ?AddressModel
    {
        $data = FormUtils::removeEmptyValues(FormUtils::emptyStrToNull2($values), true);
        if (!isset($data['target']) || !isset($data['city'])) {
            return null;
        }
        if (!isset($data['country_id'])) {
            $countryData = $this->inferCountry($data['postal_code']);
            if ($countryData) {
                $data = array_merge($data, $countryData);
            }
        }
        if (!isset($data['country_id'])) {
            throw new InvalidAddressException(_('Cannot infer country'));
        }
        if (
            in_array($data['country_id'], [CountryService::SLOVAKIA, CountryService::CZECH_REPUBLIC]) &&
            !isset($data['postal_code'])
        ) {
            throw new InvalidAddressException(_('PSC is required for Czech and Slovak'));
        }
        // $this->findModelConflicts($model, $data, null);
        return $this->addressService->storeModel($data, $model);
    }

    public function inferCountry(?string $postalCode): ?array
    {
        if (!$postalCode) {
            return null;
        }
        if (!preg_match(self::PATTERN, $postalCode)) {
            return null;
        }
        /** @var PSCSubdivisionModel $pscSubdivision */
        $pscSubdivision = $this->PSCSubdivisionService->findByPrimary($postalCode);
        if ($pscSubdivision) {
            return [
                'country_subdivision_id' => $pscSubdivision->country_subdivision_id,
                'country_id' => $pscSubdivision->country_subdivision->country_id,
            ];
        } else {
            if (strlen($postalCode) != 5) {
                return null;
            }
            Debugger::log("Czechoslovak PSC not found '$postalCode'", Debugger::WARNING);
            $firstChar = substr($postalCode, 0, 1);

            if (in_array($firstChar, ['1', '2', '3', '4', '5', '6', '7'])) {
                return [
                    'country_id' => CountryService::CZECH_REPUBLIC,
                ];
            } elseif (in_array($firstChar, ['8', '9', '0'])) {
                return [
                    'country_id' => CountryService::SLOVAKIA,
                ];
            }
        }
        return null;
    }
}
