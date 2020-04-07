<?php

namespace FKSDB\Components\DatabaseReflection\PersonInfo;


use FKSDB\Components\DatabaseReflection\AbstractRow;
use FKSDB\Components\DatabaseReflection\DefaultPrinterTrait;
use FKSDB\ORM\Models\ModelRegion;
use FKSDB\ORM\Services\ServiceRegion;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\SelectBox;
use Nette\Localization\ITranslator;


/**
 * Class CitizenshipField
 * @package FKSDB\Components\Forms\Factories\PersonInfo
 */
class CitizenshipRow extends AbstractRow {
    use DefaultPrinterTrait;
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
    public function getTitle(): string {
        return _('Státní příslušnost');
    }

    /**
     * @param array $args
     * @return BaseControl
     */
    public function createField(...$args): BaseControl {
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
            $country = ModelRegion::createFromActiveRow($row);
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

    /**
     * @return string
     */
    protected function getModelAccessKey(): string {
        return 'citizenship';
    }
}
