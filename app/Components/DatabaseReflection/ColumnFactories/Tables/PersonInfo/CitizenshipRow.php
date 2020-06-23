<?php

namespace FKSDB\Components\DatabaseReflection\PersonInfo;

use FKSDB\Components\DatabaseReflection\ColumnFactories\AbstractColumnFactory;
use FKSDB\Components\DatabaseReflection\FieldLevelPermission;
use FKSDB\Components\DatabaseReflection\ValuePrinters\StringPrinter;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\Models\ModelRegion;
use FKSDB\ORM\Services\ServiceRegion;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\SelectBox;
use Nette\Utils\Html;

/**
 * Class CitizenshipRow
 * @author Michal Červeňák <miso@fykos.cz>
 */
class CitizenshipRow extends AbstractColumnFactory {

    /**
     * @var ServiceRegion
     */
    private $serviceRegion;

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

    public function getPermission(): FieldLevelPermission {
        return new FieldLevelPermission(self::PERMISSION_ALLOW_FULL, self::PERMISSION_ALLOW_FULL);
    }

    protected function createHtmlValue(AbstractModelSingle $model): Html {
        return (new StringPrinter())($model->citizenship);
    }
}
