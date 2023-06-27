<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Events;

use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Models\Events\EventDispatchFactory;
use FKSDB\Models\Events\Exceptions\ConfigurationNotFoundException;
use FKSDB\Models\Events\Model\ApplicationHandler;
use FKSDB\Models\Events\Model\ImportHandler;
use FKSDB\Models\Events\Model\ImportHandlerException;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Services\EventParticipantService;
use FKSDB\Models\Utils\CSVParser;
use Fykosak\Utils\BaseComponent\BaseComponent;
use Fykosak\Utils\Logging\FlashMessageDump;
use Fykosak\Utils\Logging\Message;
use Nette\DI\Container;
use Nette\Forms\Form;
use Nette\Http\FileUpload;
use Tracy\Debugger;

class ImportComponent extends BaseComponent
{
    private ApplicationHandler $handler;
    private EventDispatchFactory $eventDispatchFactory;
    private EventParticipantService $eventParticipantService;
    private EventModel $event;

    public function __construct(
        ApplicationHandler $handler,
        Container $container,
        EventDispatchFactory $eventDispatchFactory,
        EventParticipantService $eventParticipantService,
        EventModel $event
    ) {
        parent::__construct($container);
        $this->event = $event;
        $this->handler = $handler;
        $this->eventDispatchFactory = $eventDispatchFactory;
        $this->eventParticipantService = $eventParticipantService;
    }

    /**
     * @throws BadTypeException
     */
    protected function createComponentFormImport(): FormControl
    {
        $control = new FormControl($this->getContext());
        $form = $control->getForm();

        $form->addUpload('file', _('File with applications'))
            ->addRule(Form::FILLED)
            ->addRule(
                Form::MIME_TYPE,
                _('Only CSV files are accepted.'),
                'text/plain'
            );

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

        $form->onSuccess[] = fn(Form $form) => $this->handleFormImport($form);
        return $control;
    }

    final public function render(): void
    {
        $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'layout.import.latte');
    }

    /**
     * @throws ConfigurationNotFoundException
     * @throws \Throwable
     */
    private function handleFormImport(Form $form): void
    {
        /** @var FileUpload[]|string[] $values */
        $values = $form->getValues();
        try {
            // process form values
            $filename = $values['file']->getTemporaryFile();
            $parser = new CSVParser($filename, CSVParser::INDEX_FROM_HEADER);

            $errorMode = $values['errorMode'];
            $stateless = $values['stateless'];

            // initialize import handler
            $importHandler = new ImportHandler(
                $parser,
                $this->eventDispatchFactory,
                $this->eventParticipantService,
                $this->event
            );

            Debugger::timer();
            $result = $importHandler->import($this->handler, $errorMode, $stateless);
            $elapsedTime = Debugger::timer();

            FlashMessageDump::dump($this->handler->getLogger(), $this->getPresenter());
            if ($result) {
                $this->getPresenter()->flashMessage(
                    sprintf(_('Import successful (%.2f s).'), $elapsedTime),
                    Message::LVL_SUCCESS
                );
            } else {
                $this->getPresenter()->flashMessage(
                    sprintf(_('Import ran with errors (%.2f s).'), $elapsedTime),
                    Message::LVL_WARNING
                );
            }

            $this->redirect('this');
        } catch (ImportHandlerException $exception) {
            FlashMessageDump::dump($this->handler->getLogger(), $this->getPresenter());
            $this->getPresenter()->flashMessage($exception->getMessage(), Message::LVL_ERROR);
        }
    }
}
