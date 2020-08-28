<?php

namespace FKSDB\Components\Events;

use FKSDB\Exceptions\BadTypeException;
use FKSDB\Modules\Core\BasePresenter;
use FKSDB\Components\Controls\BaseComponent;
use FKSDB\Config\NeonSchemaException;
use FKSDB\Events\Machine\Machine;
use FKSDB\Events\Model\ApplicationHandler;
use FKSDB\Events\Model\Grid\SingleEventSource;
use FKSDB\Events\Model\ImportHandler;
use FKSDB\Events\Model\ImportHandlerException;
use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Logging\FlashMessageDump;
use FKSDB\Utils\CSVParser;
use Nette\Application\AbortException;
use Nette\Application\UI\Form;
use Nette\DI\Container;
use Nette\Utils\JsonException;
use Tracy\Debugger;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class ImportComponent extends BaseComponent {

    private Machine $machine;

    private SingleEventSource $source;

    private ApplicationHandler $handler;

    /**
     * ImportComponent constructor.
     * @param Machine $machine
     * @param SingleEventSource $source
     * @param ApplicationHandler $handler
     * @param Container $container
     */
    public function __construct(Machine $machine, SingleEventSource $source, ApplicationHandler $handler, Container $container) {
        parent::__construct($container);
        $this->machine = $machine;
        $this->source = $source;
        $this->handler = $handler;
    }

    /**
     * @return FormControl
     * @throws BadTypeException
     */
    protected function createComponentFormImport(): FormControl {
        $control = new FormControl();
        $form = $control->getForm();

        $form->addUpload('file', _('Soubor s přihláškami'))
            ->addRule(Form::FILLED)
            ->addRule(Form::MIME_TYPE, _('Only CSV files are accepted.'), 'text/plain'); //TODO verify this check at production server

        $form->addRadioList('errorMode', _('Chování při chybě'))
            ->setItems([
                ApplicationHandler::ERROR_ROLLBACK => _('Zastavit import a rollbackovat.'),
                ApplicationHandler::ERROR_SKIP => _('Přeskočit přihlášku a pokračovat.'),
            ])
            ->setDefaultValue(ApplicationHandler::ERROR_SKIP);

        $form->addRadioList('stateless', _('Přihlášky bez uvedeného stavu'))
            ->setItems([
                ImportHandler::STATELESS_IGNORE => _('Ignore.'),
                ImportHandler::STATELESS_KEEP => _('Ponechat původní stav.'),
            ])
            ->setDefaultValue(ImportHandler::STATELESS_IGNORE);


        $form->addSubmit('import', _('Import'));

        $form->onSuccess[] = function (Form $form) {
            $this->handleFormImport($form);
        };

        return $control;
    }

    public function render(): void {
        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'layout.import.latte');
        $this->template->render();
    }

    /**
     * @param Form $form
     * @throws AbortException
     * @throws JsonException
     * @throws NeonSchemaException
     */
    private function handleFormImport(Form $form): void {
        $values = $form->getValues();
        try {
            // process form values
            $filename = $values['file']->getTemporaryFile();
            $parser = new CSVParser($filename, CSVParser::INDEX_FROM_HEADER);


            $errorMode = $values['errorMode'];
            $stateless = $values['stateless'];

            // initialize import handler
            $importHandler = new ImportHandler($this->getContext(), $parser, $this->source);

            Debugger::timer();
            $result = $importHandler->import($this->handler, $errorMode, $stateless);
            $elapsedTime = Debugger::timer();

            FlashMessageDump::dump($this->handler->getLogger(), $this->getPresenter());
            if ($result) {
                $this->getPresenter()->flashMessage(sprintf(_('Import úspěšně proběhl (%.2f s).'), $elapsedTime), BasePresenter::FLASH_SUCCESS);
            } else {
                $this->getPresenter()->flashMessage(sprintf(_('Import proběhl s chybami (%.2f s).'), $elapsedTime), BasePresenter::FLASH_WARNING);
            }

            $this->redirect('this');
        } catch (ImportHandlerException $exception) {
            FlashMessageDump::dump($this->handler->getLogger(), $this->getPresenter());
            $this->getPresenter()->flashMessage($exception->getMessage(), BasePresenter::FLASH_ERROR);
        }
    }
}
