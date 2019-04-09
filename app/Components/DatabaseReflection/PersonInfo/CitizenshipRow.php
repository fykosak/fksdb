<?php

namespace FKSDB\Components\DatabaseReflection\PersonInfo;


use FKSDB\Components\DatabaseReflection\AbstractRow;
use FKSDB\ORM\Models\ModelRegion;
use FKSDB\ORM\Services\ServiceRegion;
use Nette\Forms\Controls\SelectBox;
use Nette\Forms\IControl;
use Nette\Localization\ITranslator;


/**
 * Class CitizenshipField
 * @package FKSDB\Components\Forms\Factories\PersonInfo
 */
class CitizenshipRow extends AbstractRow {
    /**
     * @var ServiceRegion
     */
    private $serviceRegion;

    /**
     * CitizenshipField constructor.
     * @param ITranslator $translator
     * @param ServiceRegion $serviceRegion
     */
    public function __construct(ITranslator $translator, ServiceRegion $serviceRegion) {
        $this->serviceRegion = $serviceRegion;
        parent::__construct($translator);
    }

    /**
     * @return string
     */
    public static function getTitle(): string {
        return _('Státní příslušnost');
    }

    /**
     * @return IControl
     */
    public function createField(): IControl {
        $control = new SelectBox($this->getTitle());
        $control->setItems($this->getCountries());
        $control->setPrompt(_('Vyberte státní příslušnost'));
        return $control;
    }

    /**
     * @return array
     */
    private function getCountries() {
        $countries = $this->serviceRegion->getCountries();
        $results = [];
        foreach ($countries as $row) {
            $country = ModelRegion::createFromTableRow($row);
            $results[$country->country_iso] = $country->name;
        }
        return $results;
    }
    /**
     * @return int
     */
    public function getPermissionsValue(): int {
        return self::PERMISSION_ALLOW_FULL;
    }
}
