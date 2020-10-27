<?php

namespace FKSDB\DBReflection\ColumnFactories\PersonInfo;

use FKSDB\DBReflection\ColumnFactories\DefaultColumnFactory;
use FKSDB\DBReflection\MetaDataFactory;
use FKSDB\ORM\Models\ModelRegion;
use FKSDB\ORM\Services\ServiceRegion;
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
