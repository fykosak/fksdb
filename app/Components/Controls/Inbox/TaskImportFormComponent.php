<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Inbox;

use FKSDB\Components\Controls\FormComponent\FormComponent;
use FKSDB\Models\Astrid\Downloader;
use FKSDB\Models\ORM\Models\ContestYearModel;
use FKSDB\Models\Pipeline\PipelineException;
use FKSDB\Models\Submits\UploadException;
use FKSDB\Models\Tasks\PipelineFactory;
use FKSDB\Models\Tasks\SeriesData;
use Fykosak\Utils\Logging\FlashMessageDump;
use Fykosak\Utils\Logging\Message;
use Nette\DeprecatedException;
use Nette\DI\Container;
use Nette\Forms\Controls\SubmitButton;
use Nette\Forms\Form;
use Nette\Http\FileUpload;
use Nette\InvalidStateException;
use Tracy\Debugger;

final class TaskImportFormComponent extends FormComponent
{
    public const SOURCE_ASTRID = 'astrid';
    public const SOURCE_FILE = 'file';

    private PipelineFactory $pipelineFactory;
    private Downloader $downloader;
    private ContestYearModel $contestYear;

    public function __construct(Container $container, ContestYearModel $contestYear)
    {
        parent::__construct($container);
        $this->contestYear = $contestYear;
    }

    final public function inject(PipelineFactory $pipelineFactory, Downloader $downloader): void
    {
        $this->pipelineFactory = $pipelineFactory;
        $this->downloader = $downloader;
    }

    protected function handleSuccess(Form $form): void
    {
        /** @phpstan-var array{file:FileUpload,series:int,source:string} $values */
        $values = $form->getValues();
        $series = $values['series'];
        switch ($values['source']) {
            case self::SOURCE_ASTRID:
                $file = $this->downloader->downloadSeriesTasks($this->contestYear, $series);
                break;
            case self::SOURCE_FILE:
                if (!$values['file']->isOk()) {
                    throw new UploadException();
                }
                $file = $values['file']->getTemporaryFile();
                break;
            default:
                throw new InvalidStateException();
        }

        try {
            /** @var \SimpleXMLElement $xml */
            $xml = simplexml_load_file($file);

            if ($xml->getName() === 'problems') {
                throw new DeprecatedException();
            } else {
                $data = new SeriesData($this->contestYear, $series, $xml);
                $pipeline = $this->pipelineFactory->create();
                $pipeline($data);
                FlashMessageDump::dump($pipeline->logger, $this);
                $this->getPresenter()->flashMessage(_('Tasks successfully imported.'), Message::LVL_SUCCESS);
            }
        } catch (PipelineException $exception) {
            $this->getPresenter()->flashMessage(
                sprintf(_('Error during import. %s'), $exception->getMessage()),
                Message::LVL_ERROR
            );
            Debugger::log($exception, 'task-import');
        } catch (\PDOException $exception) {
            $this->getPresenter()->flashMessage(_('Error during import.'), Message::LVL_ERROR);
            Debugger::log($exception, 'task-import');
        } catch (DeprecatedException $exception) {
            $this->getPresenter()->flashMessage(_('Legacy XML format is deprecated'), Message::LVL_ERROR);
        } finally {
            unlink($file);
        }
        $this->getPresenter()->redirect('this');
    }

    protected function appendSubmitButton(Form $form): SubmitButton
    {
        return $form->addSubmit('submit', _('Import'));
    }

    protected function configureForm(Form $form): void
    {
        $source = $form->addRadioList(
            'source',
            _('Problem source'),
            [
                self::SOURCE_ASTRID => _('Astrid'),
                self::SOURCE_FILE => _('XML file (new XML)'),
            ]
        );
        $source->setDefaultValue(self::SOURCE_ASTRID);

        // Astrid download
        $seriesItems = range(1, $this->contestYear->getTotalSeries());
        if ($this->contestYear->hasHolidaySeries()) {
            $key = array_search('7', $seriesItems);
            unset($seriesItems[$key]);
        }
        $form->addSelect('series', _('Series'))
            ->setItems($seriesItems, false);

        $upload = $form->addUpload('file', _('XML file'));
        $upload->addConditionOn($source, Form::EQUAL, self::SOURCE_FILE)->toggle(
            $upload->getHtmlId() . '-pair'
        );
    }
}
