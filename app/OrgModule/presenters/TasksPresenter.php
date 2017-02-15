<?php

namespace OrgModule;

use Astrid\Downloader;
use Astrid\DownloadException;
use FKS\Application\UploadException;
use Kdyby\BootstrapFormRenderer\BootstrapRenderer;
use Logging\FlashDumpFactory;
use ModelException;
use Nette\Application\UI\Form;
use Nette\Diagnostics\Debugger;
use Pipeline\PipelineException;
use SeriesCalculator;
use Tasks\PipelineFactory;
use Tasks\SeriesData;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
class TasksPresenter extends BasePresenter {

    const SOURCE_ASTRID = 'astrid';

    const SOURCE_FILE = 'file';

    private static $languages = array('cs', 'en');

    /**
     * @var SeriesCalculator
     */
    private $seriesCalculator;

    /**
     * @var Downloader
     */
    private $downloader;

    /**
     * @var PipelineFactory
     */
    private $pipelineFactory;

    /**
     * @var FlashDumpFactory 
     */
    private $flashDumpFactory;

    public function injectSeriesCalculator(SeriesCalculator $seriesCalculator) {
        $this->seriesCalculator = $seriesCalculator;
    }

    public function injectDownloader(Downloader $downloader) {
        $this->downloader = $downloader;
    }

    public function injectPipelineFactory(PipelineFactory $pipelineFactory) {
        $this->pipelineFactory = $pipelineFactory;
    }

    function injectFlashDumpFactory(FlashDumpFactory $flashDumpFactory) {
        $this->flashDumpFactory = $flashDumpFactory;
    }

    public function authorizedImport() {
        $this->setAuthorized($this->getContestAuthorizator()->isAllowed('task', 'insert', $this->getSelectedContest()));
    }

    public function titleImport() {
        $this->setTitle(_('Import úloh'));
    }

    protected function createComponentSeriesForm() {
        $seriesForm = new Form();
        $seriesForm->setRenderer(new BootstrapRenderer());

        $source = $seriesForm->addRadioList('source', _('Zdroj úloh'), array(
            self::SOURCE_ASTRID => _('Astrid'),
            self::SOURCE_FILE => _('XML soubor'),
        ));
        $source->setDefaultValue(self::SOURCE_ASTRID);

        // Astrid downoald
        $seriesItems = range(1, $this->seriesCalculator->getTotalSeries($this->getSelectedContest(), $this->getSelectedYear()));
        $seriesEnum = $seriesForm->addSelect('series', _('Série'))
                ->setItems($seriesItems, false);
        $seriesEnum->addConditionOn($source, Form::EQUAL, self::SOURCE_ASTRID)->toggle($seriesEnum->getHtmlId() . '-pair');

        // File upload
        $seriesFree = $seriesForm->addText('series_free', _('Série'));
        $seriesFree->addCondition(Form::FILLED)->addRule(Form::INTEGER, _('Označení série musí být číslo.'));
        $seriesFree->addConditionOn($source, Form::EQUAL, self::SOURCE_FILE)->toggle($seriesFree->getHtmlId() . '-pair');

        $language = $seriesForm->addSelect('lang', _('Jazyk'));
        $language->setItems(self::$languages, false);
        $language->addConditionOn($source, Form::EQUAL, self::SOURCE_FILE)->toggle($language->getHtmlId() . '-pair');

        $upload = $seriesForm->addUpload('file', _('XML soubor úloh'));
        $upload->addConditionOn($source, Form::EQUAL, self::SOURCE_FILE)->toggle($upload->getHtmlId() . '-pair');


        $seriesForm->addSubmit('submit', _('Importovat'));

        $seriesForm->onSuccess[] = callback($this, 'validSubmitSeriesForm');

        return $seriesForm;
    }

    public function validSubmitSeriesForm(Form $seriesForm) {
        $values = $seriesForm->getValues();

        switch ($values['source']) {
            case self::SOURCE_ASTRID:
                $series = $values['series'];
                $languages = self::$languages;
                break;
            case self::SOURCE_FILE:
                $series = $values['series_free'];
                $languages = array($values['lang']);
        }

        foreach ($languages as $language) {
            try {
                // obtain file
                switch ($values['source']) {
                    case self::SOURCE_ASTRID:
                        $file = $this->downloader->downloadSeriesTasks($this->getSelectedContest(), $this->getSelectedYear(), $series, $language);
                        break;
                    case self::SOURCE_FILE:
                        if (!$values['file']->isOk()) {
                            throw new UploadException();
                        }
                        $file = $values['file']->getTemporaryFile();
                        $series = $values['series_free'];
                        $languages = array($values['lang']);
                }


                // process file
                $pipeline = $this->pipelineFactory->create($language);
                $data = new SeriesData($this->getSelectedContest(), $this->getSelectedYear(), $series, simplexml_load_file($file));

                $pipeline->setInput($data);
                $pipeline->run();
                unlink($file);

                $dump = $this->flashDumpFactory->createDefault();
                $dump->dump($pipeline->getLogger(), $this);
                $this->flashMessage(sprintf('Úlohy pro jazyk %s úspěšně importovány.', $language), self::FLASH_SUCCESS);
            } catch (DownloadException $e) {
                $this->flashMessage(sprintf('Úlohy pro jazyk %s se nepodařilo stáhnout.', $language), self::FLASH_WARNING);
            } catch (PipelineException $e) {
                $this->flashMessage(sprintf('Při ukládání úloh pro jazyk %s došlo k chybě. %s', $language, $e->getMessage()), self::FLASH_ERROR);
                Debugger::log($e);
            } catch (ModelException $e) {
                $this->flashMessage(sprintf('Při ukládání úloh pro jazyk %s došlo k chybě.', $language), self::FLASH_ERROR);
                Debugger::log($e);
            }
        }

        $this->redirect('this');
    }

}
