<?php

namespace FKSDB\Components\Controls\Stalking;

use FKSDB\Components\Controls\Helpers\Badges\ContestBadge;
use FKSDB\Components\Controls\Helpers\Badges\NoRecordsBadge;
use FKSDB\Components\Controls\Helpers\Badges\PermissionDeniedBadge;
use FKSDB\Components\Controls\Stalking\Helpers\EventLabelControl;
use FKSDB\Components\DatabaseReflection\ValuePrinterComponent;
use FKSDB\Components\Forms\Factories\TableReflectionFactory;
use FKSDB\ORM\Models\ModelPerson;
use Nette\Application\UI\Control;
use Nette\ComponentModel\IComponent;
use Nette\DI\Container;
use Nette\Localization\ITranslator;
use Nette\Templating\FileTemplate;

/**
 * Class StalkingControl
 * @package FKSDB\Components\Controls\Stalking
 * @property FileTemplate $template
 */
abstract class StalkingControl extends Control {

    const PERMISSION_FULL = 1024;
    const PERMISSION_RESTRICT = 128;
    const PERMISSION_BASIC = 16;
    const PERMISSION_USE_FIELD_LEVEL = 2048;

    /**
     * @var int
     */
    protected $userPermissions;
    /**
     * @var ModelPerson;
     */
    protected $modelPerson;
    /**
     * @var ITranslator
     */
    protected $translator;

    /**
     * @var TableReflectionFactory
     */
    protected $tableReflectionFactory;

    /**
     * StalkingComponent constructor.
     * @param Container $container
     * @param ModelPerson $modelPerson
     * @param int $userPermissions
     */
    public function __construct(Container $container, ModelPerson $modelPerson, int $userPermissions) {
        parent::__construct();
        $this->userPermissions = $userPermissions;
        $this->modelPerson = $modelPerson;
        $this->translator = $container->getByType(ITranslator::class);
        $this->tableReflectionFactory = $container->getByType(TableReflectionFactory::class);
    }

    public function beforeRender() {
        $this->template->setTranslator($this->translator);
        $this->template->userPermissions = $this->userPermissions;
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
     * @return NoRecordsBadge
     */
    public function createComponentNoRecords(): NoRecordsBadge {
        return new NoRecordsBadge($this->translator);
    }

    /**
     * @return ValuePrinterComponent
     */
    public function createComponentValuePrinter(): ValuePrinterComponent {
        return new ValuePrinterComponent($this->translator, $this->tableReflectionFactory);
    }
}
