<?php

namespace FKSDB\Modules\OrgModule;

use FKSDB\Astrid\Downloader;
use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Exceptions\BadTypeException;
use FKSDB\Logging\FlashMessageDump;
use FKSDB\SeriesCalculator;
use FKSDB\Submits\UploadException;
use FKSDB\Exceptions\ModelException;
use FKSDB\UI\PageTitle;
use Nette\Application\AbortException;
use Nette\Application\ForbiddenRequestException;
use Nette\Application\UI\Form;
use Nette\DeprecatedException;
use Nette\InvalidStateException;
use Tracy\Debugger;
use FKSDB\Pipeline\PipelineException;
use SimpleXMLElement;
use FKSDB\Tasks\PipelineFactory;
use FKSDB\Tasks\SeriesData;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class TasksPresenter extends BasePresenter {

    public const SOURCE_ASTRID = 'astrid';
    public const SOURCE_FILE = 'file';

    private SeriesCalculator $seriesCalculator;

    private PipelineFactory $pipelineFactory;

    private Downloader $downloader;

    public function injectSeriesCalculator(SeriesCalculator $seriesCalculator): void {
        $this->seriesCalculator = $seriesCalculator;
    }

    public function injectPipelineFactory(PipelineFactory $pipelineFactory): void {
        $this->pipelineFactory = $pipelineFactory;
    }

    public function injectDownloader(Downloader $downloader): void {
        $this->downloader = $downloader;
    }

    /**
     * @throws BadTypeException
     * @throws ForbiddenRequestException
     */
    public function authorizedImport(): void {
        $this->setAuthorized($this->getContestAuthorizator()->isAllowed('task', 'insert', $this->getSelectedContest()));
    }

    public function titleImport(): void {
        $this->setPageTitle(new PageTitle(_('Import úloh'), 'fa fa-upload'));
    }

    /**
     * @return FormControl
     * @throws BadTypeException
     * @throws ForbiddenRequestException
     */
    protected function createComponentSeriesForm(): FormControl {
        $control = new FormControl();
        $form = $control->getForm();

        $source = $form->addRadioList('source', _('Zdroj úloh'), [
            self::SOURCE_ASTRID => _('Astrid'),
            self::SOURCE_FILE => _('XML soubor (nové XML)'),
        ]);
        $source->setDefaultValue(self::SOURCE_ASTRID);

        // Astrid download
        $seriesItems = range(1, $this->seriesCalculator->getTotalSeries($this->getSelectedContest(), $this->getSelectedYear()));
        $form->addSelect('series', _('Série'))
            ->setItems($seriesItems, false);

        $upload = $form->addUpload('file', _('XML soubor úloh'));
        $upload->addConditionOn($source, Form::EQUAL, self::SOURCE_FILE)->toggle($upload->getHtmlId() . '-pair');

        $form->addSubmit('submit', _('Importovat'));

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
     * @throws BadTypeException
     * @throws ForbiddenRequestException
     */
    private function validSubmitSeriesForm(Form $seriesForm): void {
        $values = $seriesForm->getValues();
        $series = $values['series'];
        $file = null;

        switch ($values['source']) {
            case self::SOURCE_ASTRID:
                $file = $this->downloader->downloadSeriesTasks($this->getSelectedContest(), $this->getSelectedYear(), $series);
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
