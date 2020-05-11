<?php

namespace FKSDB\Components\Controls\PhoneNumber;

use FKSDB\ORM\Models\ModelRegion;
use FKSDB\ORM\Services\ServiceRegion;
use FKSDB\ORM\Tables\TypedTableSelection;
use Nette\Utils\Html;

/**
 * Class PhoneNumberFactory
 * @package FKSDB\Components\Controls
 */
class PhoneNumberFactory {
    /**
     * @var ServiceRegion
     */
    private $serviceRegion;
    /**
     * @var TypedTableSelection
     */
    private $table;

    /**
     * PhoneNumberFactory constructor.
     * @param ServiceRegion $serviceRegion
     */
    public function __construct(ServiceRegion $serviceRegion) {
        $this->serviceRegion = $serviceRegion;
        $this->table = $this->serviceRegion->getTable();
    }

    /**
     * @return TypedTableSelection
     */
    private function getAllRegions(): TypedTableSelection {
        return $this->table;
    }

    /**
     * @param string $number
     * @return Html
     */
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

    /**
     * @param string $number
     * @return ModelRegion|null
     */
    private function getRegion(string $number) {
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

    /**
     * @param string $number
     * @return bool
     */
    public function isValid(string $number): bool {
        return !!$this->getRegion($number);
    }
}
