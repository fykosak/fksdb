<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Events;

use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Models\Events\Exceptions\ConfigurationNotFoundException;
use FKSDB\Models\Events\Model\ImportHandler;
use FKSDB\Models\Events\Model\ImportHandlerException;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\Utils\CSVParser;
use Fykosak\Utils\BaseComponent\BaseComponent;
use Fykosak\Utils\Logging\Message;
use Nette\DI\Container;
use Nette\Forms\Form;
use Nette\Http\FileUpload;

class ImportComponent extends BaseComponent
{
    private EventModel $event;

    public function __construct(Container $container, EventModel $event)
    {
        parent::__construct($container);
        $this->event = $event;
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

        $form->addSubmit('import', _('Import'));

        $form->onSuccess[] = fn(Form $form) => $this->handleFormImport($form);
        return $control;
    }

    final public function render(): void
    {
        $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'import.latte');
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

            try {
                (new ImportHandler($this->container, $parser, $this->event))->import();
                $this->getPresenter()->flashMessage(_('Import successful.'), Message::LVL_SUCCESS);
            } catch (\Throwable $exception) {
                $this->getPresenter()->flashMessage(_('Import ran with errors.'), Message::LVL_WARNING);
            }
        } catch (ImportHandlerException $exception) {
            $this->getPresenter()->flashMessage($exception->getMessage(), Message::LVL_ERROR);
        }
        $this->getPresenter()->redirect('this');
    }
}
