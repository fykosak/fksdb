<?php

namespace FKSDB\Components\Forms\Factories\PersonInfo;


use FKSDB\ORM\ModelRegion;
use Nette\Forms\Controls\SelectBox;


class CitizenshipField extends SelectBox {
    /**
     * @var \ServiceRegion
     */
    private $serviceRegion;

    public function __construct(\ServiceRegion $serviceRegion) {
        $this->serviceRegion = $serviceRegion;
        parent::__construct(_('Státní příslušnost'));
        $this->setItems($this->getCountries());
        $this->setPrompt(_('Vyberte státní příslušnost'));
    }

    private function getCountries() {
        $countries = $this->serviceRegion->getCountries();
        $results = [];
        foreach ($countries as $row) {
            $country = ModelRegion::createFromTableRow($row);
            $results[$country->country_iso] = $country->name;
        }
        return $results;
    }
}
