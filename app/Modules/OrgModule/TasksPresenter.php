<?php

declare(strict_types=1);

namespace FKSDB\Modules\OrgModule;

use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Models\Astrid\Downloader;
use FKSDB\Models\Exceptions\BadTypeException;
use Fykosak\Utils\Logging\FlashMessageDump;
use FKSDB\Models\Pipeline\PipelineException;
use FKSDB\Models\SeriesCalculator;
use FKSDB\Models\Submits\UploadException;
use FKSDB\Models\Tasks\PipelineFactory;
use FKSDB\Models\Tasks\SeriesData;
use Fykosak\Utils\UI\PageTitle;
use Fykosak\NetteORM\Exceptions\ModelException;
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

    final public function injectQuarterly(
        PipelineFactory $pipelineFactory,
        Downloader $downloader
    ): void {
        $this->pipelineFactory = $pipelineFactory;
        $this->downloader = $downloader;
    }

    public function authorizedImport(): void
    {
        $this->setAuthorized($this->contestAuthorizator->isAllowed('task', 'insert', $this->getSelectedContest()));
    }

    public function titleImport(): PageTitle
    {
        return new PageTitle(_('Task import'), 'fas fa-download');
    }

    /**
     * @throws BadTypeException
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
        $seriesItems = range(1, SeriesCalculator::getTotalSeries($this->getSelectedContestYear()));
        if (SeriesCalculator::hasHolidaySeries($this->getSelectedContestYear())) {
            $key = array_search('7', $seriesItems);
            unset($seriesItems[$key]);
        }
        $form->addSelect('series', _('Series'))
            ->setItems($seriesItems, false);

        $upload = $form->addUpload('file', _('XML file'));
        $upload->addConditionOn($source, Form::EQUAL, self::SOURCE_FILE)->toggle($upload->getHtmlId() . '-pair');

        $form->addSubmit('submit', _('Import'));

        $form->onSuccess[] = function (Form $seriesForm) {
            $this->validSubmitSeriesForm($seriesForm);
        };

        return $control;
    }

    /**
     * @throws UploadException
     */
    private function validSubmitSeriesForm(Form $seriesForm): void
    {
        /** @var FileUpload[]|int[] $values */
        $values = $seriesForm->getValues();
        $series = $values['series'];
        $file = null;

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

            if ($this->isLegacyXml($xml)) {
                throw new DeprecatedException();
            } else {
                $data = new SeriesData($this->getSelectedContestYear(), $series, $xml);
                $pipeline = $this->pipelineFactory->create();
                $pipeline->setInput($data);
                $pipeline->run();
                FlashMessageDump::dump($pipeline->getLogger(), $this);
                $this->flashMessage(_('Tasks successfully imported.'), self::FLASH_SUCCESS);
            }
        } catch (PipelineException $exception) {
            $this->flashMessage(sprintf(_('Error during import. %s'), $exception->getMessage()), self::FLASH_ERROR);
            Debugger::log($exception);
        } catch (ModelException $exception) {
            $this->flashMessage(sprintf(_('Error during import.')), self::FLASH_ERROR);
            Debugger::log($exception);
        } catch (DeprecatedException $exception) {
            $this->flashMessage(_('Legacy XML format is deprecated'), self::FLASH_ERROR);
        } finally {
            unlink($file);
        }
        $this->redirect('this');
    }

    private function isLegacyXml(\SimpleXMLElement $xml): bool
    {
        return $xml->getName() === 'problems';
    }
}
