<?php

declare(strict_types=1);

namespace FKSDB\Components\EntityForms\Spam;

use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Models\ORM\Services\PersonMailService;
use FKSDB\Models\Utils\CSVParser;
use Fykosak\Utils\BaseComponent\BaseComponent;
use Fykosak\Utils\Logging\Message;
use Nette\Database\Connection;
use Nette\DI\Container;
use Nette\Forms\Form;
use Nette\Http\FileUpload;

final class MailImportComponent extends BaseComponent
{
    private PersonMailService $personMailService;
    private Connection $connection;

    public function __construct(Container $container)
    {
        parent::__construct($container);
    }

    public function inject(PersonMailService $personMailService, Connection $connection): void
    {
        $this->personMailService = $personMailService;
        $this->connection = $connection;
    }

    final public function render(): void
    {
        $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'importLayout.latte');
    }

    protected function createComponentFormImport(): FormControl
    {
        $control = new FormControl($this->getContext());
        $form = $control->getForm();

        $form->addUpload('file', _('File with mail'))
            ->addRule(Form::FILLED);

        $form->addSubmit('import', _('Import'));

        $form->onSuccess[] = fn (Form $form) => $this->handleFormImport($form);
        return $control;
    }

    private function handleFormImport(Form $form): void
    {
        /** @phpstan-var array{file:FileUpload} $values */
        $values = $form->getValues();
        try {
            // process form values
            $filename = $values['file']->getTemporaryFile();
            $parser = new CSVParser($filename, CSVParser::INDEX_FROM_HEADER);
            $this->connection->transaction(function () use ($parser): void {
                foreach ($parser as $data) {
                    $this->personMailService->storeModel($data);
                }
            });
            $this->getPresenter()->flashMessage(_('Import successful.'), Message::LVL_SUCCESS);
        } catch (\Throwable $exception) {
            $this->getPresenter()->flashMessage(_('Import completed with errors.'), Message::LVL_WARNING);
        }
        $this->getPresenter()->redirect('this');
    }
}
