<?php

namespace FKSDB\Components\Controls\Events;

use FKSDB\Models\Events\Exceptions\ConfigurationNotFoundException;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Modules\Core\BasePresenter;
use FKSDB\Components\Controls\BaseComponent;
use FKSDB\Models\Expressions\NeonSchemaException;
use FKSDB\Models\Events\Machine\Machine;
use FKSDB\Models\Events\Model\ApplicationHandler;
use FKSDB\Models\Events\Model\Grid\SingleEventSource;
use FKSDB\Models\Events\Model\ImportHandler;
use FKSDB\Models\Events\Model\ImportHandlerException;
use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Models\Logging\FlashMessageDump;
use FKSDB\Models\Utils\CSVParser;
use Nette\Application\UI\Form;
use Nette\DI\Container;
use Nette\DI\MissingServiceException;
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
        $control = new FormControl($this->getContext());
        $form = $control->getForm();

        $form->addUpload('file', _('Soubor s přihláškami'))
            ->addRule(Form::FILLED)
            ->addRule(Form::MIME_TYPE, _('Only CSV files are accepted.'), 'text/plain'); //TODO verify this check at production server

        $form->addRadioList('errorMode', _('Error mode'))
            ->setItems([
                ApplicationHandler::ERROR_ROLLBACK => _('Stop import and rollback.'),
                ApplicationHandler::ERROR_SKIP => _('Skip the application and continue.'),
            ])
            ->setDefaultValue(ApplicationHandler::ERROR_SKIP);

        $form->addRadioList('stateless', _('Stateless applications.'))
            ->setItems([
                ImportHandler::STATELESS_IGNORE => _('Ignore.'),
                ImportHandler::STATELESS_KEEP => _('Keep original state.'),
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
     * @throws NeonSchemaException
     * @throws ConfigurationNotFoundException
     * @throws MissingServiceException
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
                $this->getPresenter()->flashMessage(sprintf(_('Import succesfull (%.2f s).'), $elapsedTime), BasePresenter::FLASH_SUCCESS);
            } else {
                $this->getPresenter()->flashMessage(sprintf(_('Import ran with errors (%.2f s).'), $elapsedTime), BasePresenter::FLASH_WARNING);
            }

            $this->redirect('this');
        } catch (ImportHandlerException $exception) {
            FlashMessageDump::dump($this->handler->getLogger(), $this->getPresenter());
            $this->getPresenter()->flashMessage($exception->getMessage(), BasePresenter::FLASH_ERROR);
        }
    }
}
