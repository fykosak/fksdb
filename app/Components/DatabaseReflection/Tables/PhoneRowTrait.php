<?php

namespace FKSDB\Components\DatabaseReflection;

use FKSDB\Components\Controls\PhoneNumber\PhoneNumberFactory;
use FKSDB\Components\DatabaseReflection\ValuePrinters\PhonePrinter;
use FKSDB\Components\Forms\Controls\WriteOnlyInput;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\Services\ServiceRegion;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Form;
use Nette\Utils\Html;

/**
 * Class IPhoneField
 * @package FKSDB\Components\Forms\Factories\PersonInfo
 */
trait PhoneRowTrait {
    /**
     * @var ServiceRegion
     */
    private $traitServiceRegion;

    /**
     * IPhoneField constructor.
     * @param ServiceRegion $serviceRegion
     */
    public function registerPhoneRowTrait(ServiceRegion $serviceRegion) {
        $this->traitServiceRegion = $serviceRegion;
    }

    /**
     * @return BaseControl
     */
    public function createField(): BaseControl {
        $control = new WriteOnlyInput($this->getTitle());
        $control->setAttribute('placeholder', _('ve tvaru +420123456789'));
        $control->addRule(Form::MAX_LENGTH, null, 32);
        $control->addCondition(Form::FILLED)
            ->addRule(PhoneNumberFactory::getFormValidationCallback(), _('Phone number is not valid. Please use internation format, starting with "+"'));
        return $control;
    }

    /**
     * @param AbstractModelSingle $model
     * @param string $accessKey
     * @return Html
     */
    public function createHtmlValue(AbstractModelSingle $model, string $accessKey): Html {
        return (new PhonePrinter)($model->{$accessKey});
    }

}
