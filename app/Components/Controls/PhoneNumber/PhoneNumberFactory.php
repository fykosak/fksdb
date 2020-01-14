<?php

namespace FKSDB\Components\Controls\PhoneNumber;

use Closure;
use FKSDB\ORM\Models\ModelRegion;
use FKSDB\ORM\Services\ServiceRegion;
use FKSDB\ORM\Tables\TypedTableSelection;
use Nette\Forms\Controls\BaseControl;
use Nette\Utils\Html;
use function strtolower;

/**
 * Class PhoneNumberFactory
 * @package FKSDB\Components\Controls
 */
class PhoneNumberFactory {
    private $serviceRegion;

    /**
     * PhoneNumberFactory constructor.
     * @param ServiceRegion $serviceRegion
     */
    public function __construct(ServiceRegion $serviceRegion) {
        $this->serviceRegion = $serviceRegion;
    }

    /**
     * @return TypedTableSelection
     */
    private function getAllRegions(): TypedTableSelection {
        return $this->serviceRegion->getTable();
    }

    /**
     * @param string $number
     * @return Html
     */
    public function formatPhone(string $number): Html {
        /**
         * @var ModelRegion $region
         */
        try {
            $region = $this->getRegion($number);
            if ($region) {
                $flag = Html::el('span')
                    ->addAttributes(['class' => 'phone-flag mr-3'])
                    ->addHtml(Html::el('img')
                        ->addAttributes(['src' => '/images/flags/4x3/' . strtolower($region->country_iso) . '.svg']));
                return Html::el('span')->addAttributes([])->addHtml($flag)->addText($region->formatPhoneNumber($number));
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

    /**
     * @return Closure
     */
    public function getFormValidationCallback(): Closure {
        return function (BaseControl $control): bool {
            $value = $control->getValue();
            return $this->isValid($value);
        };
    }
}
