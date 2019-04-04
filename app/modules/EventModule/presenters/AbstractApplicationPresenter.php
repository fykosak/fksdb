<?php

namespace EventModule;

use Events\Model\ApplicationHandlerFactory;
use Events\Model\Grid\SingleEventSource;
use FKSDB\Components\Controls\Helpers\Badges\NotSetBadge;
use FKSDB\Components\Controls\Helpers\ValuePrinters\BinaryValueControl;
use FKSDB\Components\Controls\Helpers\ValuePrinters\IsSetValueControl;
use FKSDB\Components\Controls\Helpers\ValuePrinters\PersonValueControl;
use FKSDB\Components\Controls\Helpers\ValuePrinters\PhoneValueControl;
use FKSDB\Components\Controls\Helpers\ValuePrinters\PriceValueControl;
use FKSDB\Components\Controls\Helpers\ValuePrinters\StringValueControl;
use FKSDB\Components\Controls\Stalking\Helpers\PersonLinkControl;
use FKSDB\Components\Events\ApplicationComponent;
use FKSDB\Components\Grids\BaseGrid;
use FKSDB\Logging\FlashDumpFactory;
use FKSDB\Logging\MemoryLogger;
use FKSDB\ORM\Models\Fyziklani\ModelFyziklaniTeam;
use FKSDB\ORM\Models\ModelEventParticipant;
use Nette\Application\BadRequestException;
use Nette\Application\ForbiddenRequestException;
use Tracy\Debugger;

/**
 * Class ApplicationPresenter
 * @package EventModule
 */
abstract class AbstractApplicationPresenter extends BasePresenter {
    /**
     * @var ModelEventParticipant|ModelFyziklaniTeam
     */
    protected $model;
    /**
     * @var ApplicationHandlerFactory
     */
    private $applicationHandlerFactory;
    /**
     * @var FlashDumpFactory
     */
    private $dumpFactory;

    /**
     * @param ApplicationHandlerFactory $applicationHandlerFactory
     */
    public function injectHandlerFactory(ApplicationHandlerFactory $applicationHandlerFactory) {
        $this->applicationHandlerFactory = $applicationHandlerFactory;
    }

    /**
     * @param FlashDumpFactory $dumpFactory
     */
    public function injectFlashDumpFactory(FlashDumpFactory $dumpFactory) {
        $this->dumpFactory = $dumpFactory;
    }

    /**
     * @return ApplicationComponent
     * @throws BadRequestException
     * @throws \Nette\Application\AbortException
     */
    public function createComponentApplicationComponent() {
        $holders = [];
        $handlers = [];
        $flashDump = $this->dumpFactory->createApplication();
        $source = new SingleEventSource($this->getEvent(), $this->container);
        foreach ($source as $key => $holder) {
            $holders[$key] = $holder;
            $handlers[$key] = $this->applicationHandlerFactory->create($this->getEvent(), new MemoryLogger()); //TODO it's a bit weird to create new logger for each handler
        }

        $component = new ApplicationComponent($handlers[$this->model->getPrimary()], $holders[$this->model->getPrimary()], $flashDump);
        return $component;
    }

    /**
     * @return \FKSDB\Components\Controls\Helpers\ValuePrinters\BinaryValueControl
     */
    public function createComponentBinaryValue(): BinaryValueControl {
        return new BinaryValueControl($this->getTranslator());
    }

    /**
     * @return PersonLinkControl
     */
    public function createComponentPersonLink(): PersonLinkControl {
        return new PersonLinkControl();
    }

    /**
     * @return PersonValueControl
     */
    public function createComponentPersonValue(): PersonValueControl {
        return new PersonValueControl($this->getTranslator());
    }

    /**
     * @return \FKSDB\Components\Controls\Helpers\ValuePrinters\StringValueControl
     */
    public function createComponentStringValue(): StringValueControl {
        return new StringValueControl($this->getTranslator());
    }

    /**
     * @return \FKSDB\Components\Controls\Helpers\Badges\NotSetBadge
     */
    public function createComponentNotSet(): NotSetBadge {
        return new NotSetBadge($this->getTranslator());
    }

    /**
     * @return PhoneValueControl
     */
    public function createComponentPhoneValue(): PhoneValueControl {
        return new PhoneValueControl($this->getTranslator());
    }

    /**
     * @return \FKSDB\Components\Controls\Helpers\ValuePrinters\IsSetValueControl
     */
    public function createComponentIsSetValue(): IsSetValueControl {
        return new IsSetValueControl($this->getTranslator());
    }

    /**
     * @return \FKSDB\Components\Controls\Helpers\ValuePrinters\PriceValueControl
     */
    public function createComponentPriceValue(): PriceValueControl {
        return new PriceValueControl($this->getTranslator());
    }

    /**
     * @param $id
     * @throws BadRequestException
     * @throws ForbiddenRequestException
     * @throws \Nette\Application\AbortException
     */
    public function actionDetail($id) {
        $this->loadModel($id);
    }

    /**
     * @return void
     */
    abstract public function titleList();

    /**
     * @return void
     */
    abstract public function titleDetail();

    /**
     * @throws \Nette\Application\AbortException
     * @throws \Nette\Application\BadRequestException
     * @return void;
     */
    abstract public function authorizedDetail();

    /**
     * @throws \Nette\Application\AbortException
     * @throws \Nette\Application\BadRequestException
     * @return void;
     */
    abstract public function authorizedList();

    /**
     * @param int $id
     * @throws BadRequestException
     * @throws ForbiddenRequestException
     * @throws \Nette\Application\AbortException
     */
    abstract protected function loadModel(int $id);

    /**
     * @return ModelEventParticipant|ModelFyziklaniTeam
     */
    abstract protected function getModel();

    /**
     * @return BaseGrid
     * @throws \Nette\Application\AbortException
     * @throws \Nette\Application\BadRequestException
     */
    abstract function createComponentGrid(): BaseGrid;
}
