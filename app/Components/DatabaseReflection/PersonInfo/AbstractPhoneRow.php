<?php

namespace FKSDB\Components\DatabaseReflection\PersonInfo;

use FKSDB\Components\Controls\Helpers\ValuePrinters\AbstractValue;
use FKSDB\Components\Controls\Helpers\ValuePrinters\PhoneValueControl;
use FKSDB\Components\Controls\PhoneNumber\PhoneNumberFactory;
use FKSDB\Components\DatabaseReflection\AbstractRow;
use FKSDB\Components\Forms\Controls\WriteOnlyInput;
use FKSDB\ORM\Services\ServiceRegion;
use Nette\Forms\Form;
use Nette\Forms\IControl;
use Nette\Localization\ITranslator;

/**
 * Class IPhoneField
 * @package FKSDB\Components\Forms\Factories\PersonInfo
 */
abstract class AbstractPhoneRow extends AbstractRow {
    /**
     * @var ServiceRegion
     */
    private $serviceRegion;

    /**
     * IPhoneField constructor.
     * @param ServiceRegion $serviceRegion
     * @param ITranslator $translator
     */
    public function __construct(ServiceRegion $serviceRegion, ITranslator $translator) {
        parent::__construct($translator);
        $this->serviceRegion = $serviceRegion;
    }

    /**
     * @return IControl
     */
    public function createField(): IControl {
        $control = new WriteOnlyInput($this->getTitle());
        $control->setAttribute("placeholder", _('ve tvaru +420123456789'));
        $control->addRule(Form::MAX_LENGTH, null, 32);
        $control->addCondition(Form::FILLED)
            ->addRule(PhoneNumberFactory::getFormValidationCallback(), _('Phone number is not valid. Please use internation format, starting with "+"'));
        return $control;
    }

    /**
     * @param string $mode
     * @param int $userPermissionsLevel
     * @return AbstractValue
     */
    protected function createValuePrinter(string $mode, int $userPermissionsLevel): AbstractValue {
        return new PhoneValueControl($this->translator, $mode, $this->getTitle(), $this->hasPermissions($userPermissionsLevel));
    }

}
