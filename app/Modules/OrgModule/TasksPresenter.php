<?php

declare(strict_types=1);

namespace FKSDB\Modules\OrgModule;

use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Components\Controls\Inbox\HandoutFormComponent;
use FKSDB\Models\Astrid\Downloader;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\Submits\SeriesTable;
use Fykosak\Utils\Logging\FlashMessageDump;
use FKSDB\Models\Pipeline\PipelineException;
use FKSDB\Models\Submits\UploadException;
use FKSDB\Models\Tasks\PipelineFactory;
use FKSDB\Models\Tasks\SeriesData;
use Fykosak\Utils\Logging\Message;
use Fykosak\Utils\UI\PageTitle;
use Fykosak\NetteORM\Exceptions\ModelException;
use Nette\Application\BadRequestException;
use Nette\Application\ForbiddenRequestException;
use Nette\Application\UI\Form;
use Nette\DeprecatedException;
use Nette\Http\FileUpload;
use Nette\InvalidStateException;
use Tracy\Debugger;

class TasksPresenter extends BasePresenter
{
    public const SOURCE_ASTRID = 'astrid';
    public const SOURCE_FILE = 'file';

    private PipelineFactory $pipelineFactory;
    private Downloader $downloader;
    private SeriesTable $seriesTable;

    final public function injectQuarterly(
        PipelineFactory $pipelineFactory,
        Downloader $downloader,
        SeriesTable $seriesTable
    ): void {
        $this->pipelineFactory = $pipelineFactory;
        $this->downloader = $downloader;
        $this->seriesTable = $seriesTable;
    }

    public function authorizedImport(): void
    {
        $this->setAuthorized($this->contestAuthorizator->isAllowed('task', 'insert', $this->getSelectedContest()));
    }

    public function authorizedDispatch(): void
    {
        $this->setAuthorized($this->contestAuthorizator->isAllowed('task', 'dispatch', $this->getSelectedContest()));
    }

    public function titleImport(): PageTitle
    {
        return new PageTitle(null, _('Task import'), 'fas fa-download');
    }

    public function titleDispatch(): PageTitle
    {
        return new PageTitle(null, _('Handout'), 'fa fa-folder-open');
    }

    /**
     * @throws BadTypeException
     */
    public function actionDispatch(): void
    {
        /** @var HandoutFormComponent $control */
        $control = $this->getComponent('handoutForm');
        $control->setDefaults();
    }

    /**
     * @throws ForbiddenRequestException
     * @throws BadRequestException
     */
    protected function startup(): void
    {
        parent::startup();
        $this->seriesTable->contestYear = $this->getSelectedContestYear();
        $this->seriesTable->series = $this->getSelectedSeries();
    }


    /**
     * @throws BadTypeException
     * TODO to separate Component
     */
    protected function createComponentSeriesForm(): FormControl
    {
        $control = new FormControl($this->getContext());
        $form = $control->getForm();

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
        $seriesItems = range(1, $this->getSelectedContestYear()->getTotalSeries());
        if ($this->getSelectedContestYear()->hasHolidaySeries()) {
            $key = array_search('7', $seriesItems);
            unset($seriesItems[$key]);
        }
        $form->addSelect('series', _('Series'))
            ->setItems($seriesItems, false);

        $upload = $form->addUpload('file', _('XML file'));
        $upload->addConditionOn($source, Form::EQUAL, self::SOURCE_FILE)->toggle($upload->getHtmlId() . '-pair');

        $form->addSubmit('submit', _('Import'));

        $form->onSuccess[] = fn(Form $seriesForm) => $this->validSubmitSeriesForm($seriesForm);

        return $control;
    }

    protected function createComponentHandoutForm(): HandoutFormComponent
    {
        return new HandoutFormComponent($this->getContext(), $this->seriesTable);
    }

    /**
     * @throws UploadException
     */
    private function validSubmitSeriesForm(Form $seriesForm): void
    {
        /** @var FileUpload[]|int[] $values */
        $values = $seriesForm->getValues();
        $series = $values['series'];
        switch ($values['source']) {
            case self::SOURCE_ASTRID:
                $file = $this->downloader->downloadSeriesTasks($this->getSelectedContestYear(), $series);
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
            $xml = simplexml_load_file($file);

            if ($xml->getName() === 'problems') {
                throw new DeprecatedException();
            } else {
                $data = new SeriesData($this->getSelectedContestYear(), $series, $xml);
                $pipeline = $this->pipelineFactory->create();
                $pipeline($data);
                FlashMessageDump::dump($pipeline->logger, $this);
                $this->flashMessage(_('Tasks successfully imported.'), Message::LVL_SUCCESS);
            }
        } catch (PipelineException $exception) {
            $this->flashMessage(sprintf(_('Error during import. %s'), $exception->getMessage()), Message::LVL_ERROR);
            Debugger::log($exception);
        } catch (ModelException $exception) {
            $this->flashMessage(_('Error during import.'), Message::LVL_ERROR);
            Debugger::log($exception);
        } catch (DeprecatedException $exception) {
            $this->flashMessage(_('Legacy XML format is deprecated'), Message::LVL_ERROR);
        } finally {
            unlink($file);
        }
        $this->redirect('this');
    }
}
