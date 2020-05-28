<?php

namespace OrgModule;

use FKSDB\Astrid\Downloader;
use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Logging\FlashMessageDump;
use FKSDB\SeriesCalculator;
use FKSDB\Submits\UploadException;
use FKSDB\Exceptions\ModelException;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use Nette\DeprecatedException;
use Nette\Http\FileUpload;
use Tracy\Debugger;
use Pipeline\PipelineException;
use SimpleXMLElement;
use FKSDB\Tasks\PipelineFactory;
use FKSDB\Tasks\SeriesData;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class TasksPresenter extends BasePresenter {

    const SOURCE_ASTRID = 'astrid';
    const SOURCE_FILE = 'file';

    /**
     * @var SeriesCalculator
     */
    private $seriesCalculator;

    /**
     * @var PipelineFactory
     */
    private $pipelineFactory;

    /**
     * @var Downloader
     */
    private $downloader;

    /**
     * @param SeriesCalculator $seriesCalculator
     * @return void
     */
    public function injectSeriesCalculator(SeriesCalculator $seriesCalculator) {
        $this->seriesCalculator = $seriesCalculator;
    }

    /**
     * @param PipelineFactory $pipelineFactory
     * @return void
     */
    public function injectPipelineFactory(PipelineFactory $pipelineFactory) {
        $this->pipelineFactory = $pipelineFactory;
    }

    /**
     * @param Downloader $downloader
     * @return void
     */
    public function injectDownloader(Downloader $downloader) {
        $this->downloader = $downloader;
    }

    /**
     * @return void
     * @throws BadRequestException
     */
    public function authorizedImport() {
        $this->setAuthorized($this->getContestAuthorizator()->isAllowed('task', 'insert', $this->getSelectedContest()));
    }

    /**
     * @return void
     */
    public function titleImport() {
        $this->setTitle(_('Tasks import'), 'fa fa-upload');
    }

    /**
     * @return FormControl
     * @throws BadRequestException
     */
    protected function createComponentSeriesForm(): FormControl {
        $control = new FormControl();
        $form = $control->getForm();

        $source = $form->addRadioList('source', _('Source'), [
            self::SOURCE_ASTRID => _('Astrid'),
            self::SOURCE_FILE => _('XML file (new XML)'),
        ]);
        $source->setDefaultValue(self::SOURCE_ASTRID);

        // Astrid download
        $seriesItems = range(1, $this->seriesCalculator->getTotalSeries($this->getSelectedContest(), $this->getSelectedYear()));
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

    private function isLegacyXml(SimpleXMLElement $xml): bool {
        return $xml->getName() === 'problems';
    }

    /**
     * @param Form $seriesForm
     * @return void
     * @throws AbortException
     * @throws BadRequestException
     */
    private function validSubmitSeriesForm(Form $seriesForm) {
        $values = $seriesForm->getValues();
        $series = $values['series'];
        $file = null;

        switch ($values['source']) {
            case self::SOURCE_ASTRID:
                $file = $this->downloader->downloadSeriesTasks($this->getSelectedContest(), $this->getSelectedYear(), $series);
                break;
            case self::SOURCE_FILE:
                /** @var FileUpload $file */
                $file = $values['file'];
                if (!$file->isOk()) {
                    throw new UploadException();
                }
                $file = $file->getTemporaryFile();
                break;
            default:
                throw new BadRequestException();
        }

        try {
            $xml = simplexml_load_file($file);

            if ($this->isLegacyXml($xml)) {
                throw new DeprecatedException();
            } else {
                $data = new SeriesData($this->getSelectedContest(), $this->getSelectedYear(), $series, $xml);
                $pipeline = $this->pipelineFactory->create();
                $pipeline->setInput($data);
                $pipeline->run();
                FlashMessageDump::dump($pipeline->getLogger(), $this);
                $this->flashMessage(_('Úlohy pro úspěšně importovány.'), self::FLASH_SUCCESS);
            }
        } catch (PipelineException $exception) {
            $this->flashMessage(sprintf(_('Při ukládání úloh došlo k chybě. %s'), $exception->getMessage()), self::FLASH_ERROR);
            Debugger::log($exception);
        } catch (ModelException $exception) {
            $this->flashMessage(sprintf(_('Při ukládání úloh došlo k chybě.')), self::FLASH_ERROR);
            Debugger::log($exception);
        } catch (DeprecatedException $exception) {
            $this->flashMessage(_('Legacy XML format is deprecated'), self::FLASH_ERROR);
        } finally {
            unlink($file);
        }
        $this->redirect('this');
    }
}
