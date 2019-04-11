<?php

namespace FKSDB\Components\Controls\Stalking;

use FKSDB\Components\Controls\Helpers\Badges\ContestBadge;
use FKSDB\Components\Controls\Helpers\Badges\NoRecordsBadge;
use FKSDB\Components\Controls\Helpers\Badges\NotSetBadge;
use FKSDB\Components\Controls\Helpers\Badges\PermissionDeniedBadge;
use FKSDB\Components\Controls\Helpers\ValuePrinters\AbstractValueControl;
use FKSDB\Components\Controls\Helpers\ValuePrintersTrait;
use FKSDB\Components\Controls\Stalking\Helpers\EventLabelControl;
use FKSDB\Components\Forms\Factories\TableReflectionFactory;
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

    use ValuePrintersTrait;

    const PERMISSION_FULL = 1024;
    const PERMISSION_RESTRICT = 128;
    const PERMISSION_BASIC = 16;
    const PERMISSION_USE_FIELD_LEVEL = 2048;

    const LAYOUT_COUNTABLE = 'countable';
    const LAYOUT_NONE = 'none';
    /**
     * @var int
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
     * @var string
     */
    private $layout;

    /**
     * @var TableReflectionFactory
     */
    protected $tableReflectionFactory;

    /**
     * StalkingComponent constructor.
     * @param ModelPerson $modelPerson
     * @param TableReflectionFactory $tableReflectionFactory
     * @param ITranslator $translator
     * @param int $mode
     * @param string $layout
     */
    public function __construct(ModelPerson $modelPerson, TableReflectionFactory $tableReflectionFactory, ITranslator $translator, int $mode, string $layout = self::LAYOUT_NONE) {
        parent::__construct();
        $this->mode = $mode;
        $this->modelPerson = $modelPerson;
        $this->translator = $translator;
        $this->layout = $layout;
        $this->tableReflectionFactory = $tableReflectionFactory;
        $this->registerTrait($translator, AbstractValueControl::LAYOUT_STALKING);
    }

    public function beforeRender() {
        $this->template->setTranslator($this->translator);
        $this->template->mode = $this->mode;
        $this->template->headline = $this->getHeadline();
        $this->template->allowedModes = $this->getAllowedPermissions();
        $this->template->gender = $this->modelPerson->gender;
        $this->template->layout = $this->layout;
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

    /**
     * @return string
     */
    abstract protected function getHeadline(): string;

    /**
     * @return string[]
     */
    abstract protected function getAllowedPermissions(): array;

    /**
     * @param string $name
     * @return \Nette\ComponentModel\IComponent|null
     * @throws \Exception
     */
    public function createComponent($name) {
        $parts = \explode('__', $name);
        if (\count($parts) === 3) {
            list($prefix, $tableName, $fieldName) = $parts;
            if ($prefix === 'valuePrinter') {
                return $this->tableReflectionFactory->createStalkingComponent($tableName, $fieldName, max($this->getAllowedPermissions()));
            }
        }

        return parent::createComponent($name);
    }
}
