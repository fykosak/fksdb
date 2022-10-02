<?php

declare(strict_types=1);

namespace FKSDB\Components\Forms\Containers;

use FKSDB\Models\ORM\Models\AddressModel;
use FKSDB\Models\ORM\Models\PostContactModel;
use FKSDB\Models\ORM\Models\RegionModel;
use FKSDB\Models\ORM\Services\RegionService;
use Fykosak\NetteORM\Model;
use Nette\DI\Container as DIContainer;
use Nette\InvalidStateException;

class AddressContainer extends ModelContainer
{
    private RegionService $regionService;

    public function __construct(DIContainer $container)
    {
        parent::__construct($container);
    }

    final public function injectServiceRegion(RegionService $regionService): void
    {
        $this->regionService = $regionService;
    }

    /**
     * Used for substituting form's IControl (via duck-typing).
     *
     * @param iterable|null $value
     */
    public function setValue($value): void
    {
        $this->setValues($value ?? []);
    }

    /**
     * Used for substituting form's IControl (via duck-typing).
     *
     * @param iterable $value
     */
    public function setDefaultValue($value): void
    {
        $this->setDefaults($value ?? []);
    }

    /**
     * @param PostContactModel|mixed $data
     * @return static
     */
    public function setValues($data, bool $erase = false): self
    {
        if ($data instanceof Model) { //assert its from address table
            if ($data instanceof PostContactModel) {
                $address = $data->address;
            } else {
                $address = $data;
            }
            /** @var AddressModel $address */
            $data = $address->toArray();
            $data['country_iso'] = $address->region_id ? $address->region->country_iso : null;
        } elseif (is_array($data) && isset($data['region_id'])) {
            $region = $this->regionService->findByPrimary($data['region_id']);
            $data['country_iso'] = $region->country_iso;
        }

        return parent::setValues($data, $erase);
    }

    /**
     * @param null $returnType
     * @return array|object
     */
    public function getUnsafeValues($returnType = null, array $controls = null)
    {
        $values = parent::getUnsafeValues($returnType);
        if (count($values) && !isset($values['region_id'])) {
            if (!$this->regionService) {
                throw new InvalidStateException(
                    'You must set ' . RegionService::class
                    . ' before getting values from the address container.'
                );
            }
            /** @var RegionModel|null $region */
            $region = $this->regionService->getCountries()->where('country_iso', $values['country_iso'])->fetch();
            $values['region_id'] = $region ? $region->region_id : null;
        }
        return $values;
    }
}
