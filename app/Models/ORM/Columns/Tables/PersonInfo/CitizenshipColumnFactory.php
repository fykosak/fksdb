<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Columns\Tables\PersonInfo;

use FKSDB\Models\ORM\Columns\ColumnFactory;
use FKSDB\Models\ORM\MetaDataFactory;
use FKSDB\Models\ORM\Models\RegionModel;
use FKSDB\Models\ORM\Services\RegionService;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\SelectBox;

class CitizenshipColumnFactory extends ColumnFactory
{
    private RegionService $regionService;

    public function __construct(RegionService $regionService, MetaDataFactory $metaDataFactory)
    {
        parent::__construct($metaDataFactory);
        $this->regionService = $regionService;
    }

    protected function createFormControl(...$args): BaseControl
    {
        $control = new SelectBox($this->getTitle());
        $control->setItems($this->getCountries());
        $control->setPrompt(_('Choose citizenship'));
        return $control;
    }

    private function getCountries(): array
    {
        $countries = $this->regionService->getCountries();
        $results = [];
        /** @var RegionModel $country */
        foreach ($countries as $country) {
            $results[$country->country_iso] = $country->name;
        }
        return $results;
    }
}
