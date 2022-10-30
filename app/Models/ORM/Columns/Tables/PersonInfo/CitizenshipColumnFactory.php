<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Columns\Tables\PersonInfo;

use FKSDB\Models\ORM\Columns\ColumnFactory;
use FKSDB\Models\ORM\MetaDataFactory;
use FKSDB\Models\ORM\Models\CountryModel;
use FKSDB\Models\ORM\Services\CountryService;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\SelectBox;

class CitizenshipColumnFactory extends ColumnFactory
{
    private CountryService $countryService;

    public function __construct(CountryService $countryService, MetaDataFactory $metaDataFactory)
    {
        parent::__construct($metaDataFactory);
        $this->countryService = $countryService;
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
        $results = [];
        /** @var CountryModel $country */
        foreach ($this->countryService->getTable() as $country) {
            $results[$country->alpha_2] = $country->name;
        }
        return $results;
    }
}
