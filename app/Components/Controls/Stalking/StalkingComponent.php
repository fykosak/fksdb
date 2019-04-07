<?php

namespace FKSDB\Components\Controls\Stalking;

use FKSDB\Components\Controls\Helpers\Badges\ContestBadge;
use FKSDB\Components\Controls\Helpers\Badges\NoRecordsBadge;
use FKSDB\Components\Controls\Helpers\Badges\NotSetBadge;
use FKSDB\Components\Controls\Helpers\Badges\PermissionDeniedBadge;
use FKSDB\Components\Controls\Helpers\ValuePrinters\AbstractValue;
use FKSDB\Components\Controls\Helpers\ValuePrinters\BinaryValueControl;
use FKSDB\Components\Controls\Helpers\ValuePrinters\IsSetValueControl;
use FKSDB\Components\Controls\Helpers\ValuePrinters\PhoneValueControl;
use FKSDB\Components\Controls\Helpers\ValuePrinters\StringValueControl;
use FKSDB\Components\Controls\Stalking\Helpers\EventLabelControl;
use FKSDB\ORM\Models\ModelPerson;
use Nette\Application\UI\Control;
use Nette\Localization\ITranslator;
use Nette\Templating\FileTemplate;

/**
 * Class StalkingComponent
 * @package FKSDB\Components\Controls\Stalking
 * @property FileTemplate $template
 */
abstract class StalkingComponent extends Control {
    const PERMISSION_FULL = 'full';
    const PERMISSION_RESTRICT = 'restrict';
    const PERMISSION_BASIC = 'basic';
    /**
     * @var string
     */
    protected $mode;
    /**
     * @var ModelPerson;
     */
    protected $modelPerson;
    /**
     * @var ITranslator
     */
    protected $translator;

    /**
     * StalkingComponent constructor.
     * @param ModelPerson $modelPerson
     * @param ITranslator $translator
     * @param $mode
     */
    public function __construct(ModelPerson $modelPerson, ITranslator $translator, $mode) {
        parent::__construct();
        $this->mode = $mode;
        $this->modelPerson = $modelPerson;
        $this->translator = $translator;
    }

    public function beforeRender() {
        $this->template->setTranslator($this->translator);
        $this->template->mode = $this->mode;
        $this->template->headline = $this->getHeadline();
        $this->template->allowedModes = $this->getAllowedPermissions();
        $this->template->gender = $this->modelPerson->gender;
    }

    /**
     * @return ContestBadge
     */
    public function createComponentContestBadge(): ContestBadge {
        return new ContestBadge();
    }

    /**
     * @return PermissionDeniedBadge
     */
    public function createComponentPermissionDenied(): PermissionDeniedBadge {
        return new PermissionDeniedBadge($this->translator);
    }

    /**
     * @return EventLabelControl
     */
    public function createComponentEventLabel(): EventLabelControl {
        return new EventLabelControl();
    }

    /**
     * @return \FKSDB\Components\Controls\Helpers\Badges\NoRecordsBadge
     */
    public function createComponentNoRecords(): NoRecordsBadge {
        return new NoRecordsBadge($this->translator);
    }

    /**
     * @return NotSetBadge
     */
    public function createComponentNotSet(): NotSetBadge {
        return new NotSetBadge($this->translator);
    }

    /************* VALUES *****************/
    /**
     * @return PhoneValueControl
     */
    public function createComponentPhoneValue(): PhoneValueControl {
        return new PhoneValueControl($this->translator, AbstractValue::LAYOUT_STALKING);
    }

    /**
     * @return IsSetValueControl
     */
    public function createComponentIsSetValue(): IsSetValueControl {
        return new IsSetValueControl($this->translator, AbstractValue::LAYOUT_STALKING);
    }

    /**
     * @return BinaryValueControl
     */
    public function createComponentBinaryValue(): BinaryValueControl {
        return new BinaryValueControl($this->translator, AbstractValue::LAYOUT_STALKING);
    }

    /**
     * @return \FKSDB\Components\Controls\Helpers\ValuePrinters\StringValueControl
     */
    public function createComponentStringValue(): StringValueControl {
        return new StringValueControl($this->translator, AbstractValue::LAYOUT_STALKING);
    }

    /**
     * @return string
     */
    abstract protected function getHeadline(): string;

    /**
     * @return string[]
     */
    abstract protected function getAllowedPermissions(): array;
}
