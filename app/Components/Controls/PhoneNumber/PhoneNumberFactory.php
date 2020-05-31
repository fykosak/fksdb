<?php

namespace FKSDB\Components\Controls\PhoneNumber;

use FKSDB\ORM\Models\ModelRegion;
use FKSDB\ORM\Services\ServiceRegion;
use FKSDB\ORM\Tables\TypedTableSelection;
use Nette\Utils\Html;

/**
 * Class PhoneNumberFactory
 * @author Michal Červeňák <miso@fykos.cz>
 */
class PhoneNumberFactory {

    private ServiceRegion $serviceRegion;

    private TypedTableSelection $table;

    /**
     * PhoneNumberFactory constructor.
     * @param ServiceRegion $serviceRegion
     */
    public function __construct(ServiceRegion $serviceRegion) {
        $this->serviceRegion = $serviceRegion;
        $this->table = $this->serviceRegion->getTable();
    }

    private function getAllRegions(): TypedTableSelection {
        return $this->table;
    }

    public function formatPhone(string $number): Html {
        try {
            $region = $this->getRegion($number);
            if ($region) {
                $flag = Html::el('span')
                    ->addAttributes(['class' => 'phone-flag mr-3'])
                    ->addHtml(Html::el('img')
                        ->addAttributes(['src' => '/images/flags/4x3/' . \strtolower($region->country_iso) . '.svg']));
                return Html::el('span')->addHtml($flag)->addText($region->formatPhoneNumber($number));
            }
        } catch (InvalidPhoneNumberException $exception) {
        }
        return Html::el('span')->addAttributes(['class' => 'badge badge-danger'])->addText($number);
    }

    private function getRegion(string $number): ?ModelRegion {
        /**
         * @var ModelRegion $region
         */
        foreach ($this->getAllRegions() as $region) {
            if ($region->matchPhone($number)) {
                return $region;
            }
        }
        return null;
    }

    public function isValid(string $number): bool {
        return !!$this->getRegion($number);
    }
}
