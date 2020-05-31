<?php

namespace FKSDB\Components\DatabaseReflection\PersonInfo;

use FKSDB\Components\DatabaseReflection\AbstractRow;
use FKSDB\Components\DatabaseReflection\DefaultPrinterTrait;
use FKSDB\ORM\Models\ModelRegion;
use FKSDB\ORM\Services\ServiceRegion;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\SelectBox;

/**
 * Class CitizenshipRow
 * @author Michal Červeňák <miso@fykos.cz>
 */
class CitizenshipRow extends AbstractRow {
    use DefaultPrinterTrait;

    private ServiceRegion $serviceRegion;

    /**
     * CitizenshipField constructor.
     * @param ServiceRegion $serviceRegion
     */
    public function __construct(ServiceRegion $serviceRegion) {
        $this->serviceRegion = $serviceRegion;
    }

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
        /** @var ModelRegion $country */
        foreach ($countries as $country) {
            $results[$country->country_iso] = $country->name;
        }
        return $results;
    }

    public function getPermissionsValue(): int {
        return self::PERMISSION_ALLOW_FULL;
    }

    protected function getModelAccessKey(): string {
        return 'citizenship';
    }
}
