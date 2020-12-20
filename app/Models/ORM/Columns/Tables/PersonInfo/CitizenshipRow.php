<?php

namespace FKSDB\Models\ORM\Columns\Tables\PersonInfo;

use FKSDB\Models\ORM\Columns\Types\DefaultColumnFactory;
use FKSDB\Models\ORM\MetaDataFactory;
use FKSDB\Models\ORM\Models\ModelRegion;
use FKSDB\Models\ORM\Services\ServiceRegion;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\SelectBox;

/**
 * Class CitizenshipRow
 * @author Michal Červeňák <miso@fykos.cz>
 */
class CitizenshipRow extends DefaultColumnFactory {

    private ServiceRegion $serviceRegion;

    public function __construct(ServiceRegion $serviceRegion, MetaDataFactory $metaDataFactory) {
        parent::__construct($metaDataFactory);
        $this->serviceRegion = $serviceRegion;
    }

    protected function createFormControl(...$args): BaseControl {
        $control = new SelectBox($this->getTitle());
        $control->setItems($this->getCountries());
        $control->setPrompt(_('Choose citizenship'));
        return $control;
    }

    private function getCountries(): array {
        $countries = $this->serviceRegion->getCountries();
        $results = [];
        /** @var ModelRegion $country */
        foreach ($countries as $country) {
            $results[$country->country_iso] = $country->name;
        }
        return $results;
    }
}
