<?php

namespace FKSDB\Components\Controls\Stalking;

use FKSDB\Components\Controls\DetailHelpers\AbstractValue;
use FKSDB\Components\Controls\DetailHelpers\BinaryValueControl;
use FKSDB\Components\Controls\DetailHelpers\IsSetValueControl;
use FKSDB\Components\Controls\DetailHelpers\PhoneValueControl;
use FKSDB\Components\Controls\DetailHelpers\StringValueControl;
use FKSDB\Components\Controls\Stalking\Helpers\ContestBadge;
use FKSDB\Components\Controls\Stalking\Helpers\EventLabelControl;
use FKSDB\Components\Controls\Stalking\Helpers\NoRecordsControl;
use FKSDB\Components\Controls\Stalking\Helpers\NotSetControl;
use FKSDB\Components\Controls\Stalking\Helpers\PermissionDenied;
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
    }

    /**
     * @return ContestBadge
     */
    public function createComponentContestBadge(): ContestBadge {
        return new ContestBadge();
    }

    /**
     * @return PermissionDenied
     */
    public function createComponentPermissionDenied(): PermissionDenied {
        return new PermissionDenied($this->translator);
    }

    /**
     * @return EventLabelControl
     */
    public function createComponentEventLabel(): EventLabelControl {
        return new EventLabelControl();
    }

    /**
     * @return NoRecordsControl
     */
    public function createComponentNoRecords(): NoRecordsControl {
        return new NoRecordsControl($this->translator);
    }

    /**
     * @return NotSetControl
     */
    public function createComponentNotSet(): NotSetControl {
        return new NotSetControl($this->translator);
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
     * @return StringValueControl
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
