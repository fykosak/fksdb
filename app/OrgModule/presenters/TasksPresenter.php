<?php

namespace OrgModule;

use Astrid\Downloader;
use Astrid\DownloadException;
use FKS\Application\UploadException;
use FKSDB\Components\Controls\FormControl\FormControl;
use Kdyby\BootstrapFormRenderer\BootstrapRenderer;
use Logging\FlashDumpFactory;
use ModelException;
use Nette\Application\UI\Form;
use Nette\Diagnostics\Debugger;
use Pipeline\PipelineException;
use SeriesCalculator;
use SimpleXMLElement;
use Tasks\PipelineFactory;
use Tasks\SeriesData;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class TasksPresenter extends BasePresenter {

    const SOURCE_ASTRID = 'astrid';

    const SOURCE_ASTRID_2 = 'astrid_2';

    const SOURCE_FILE = 'file';

    const LANG_ALL = '_all';

    private static $languages = array('cs', 'en');

    /**
     * @var SeriesCalculator
     */
    private $seriesCalculator;

    /**
     * @var PipelineFactory
     */
    private $pipelineFactory;

    /**
     * @var FlashDumpFactory
     */
    private $flashDumpFactory;

    /**
     * @var Downloader
     */
    private $downloader;

    public function injectSeriesCalculator(SeriesCalculator $seriesCalculator) {
        $this->seriesCalculator = $seriesCalculator;
    }

    public function injectPipelineFactory(PipelineFactory $pipelineFactory) {
        $this->pipelineFactory = $pipelineFactory;
    }

    function injectFlashDumpFactory(FlashDumpFactory $flashDumpFactory) {
        $this->flashDumpFactory = $flashDumpFactory;
    }

    function injectDownloader(Downloader $downloader) {
        $this->downloader = $downloader;
    }

    public function authorizedImport() {
        $this->setAuthorized($this->getContestAuthorizator()->isAllowed('task', 'insert', $this->getSelectedContest()));
    }

    public function titleImport() {
        $this->setTitle(_('Import úloh'));
        $this->setIcon('fa fa-upload');
    }

    protected function createComponentSeriesForm() {
        $control = new FormControl();
        $form = $control->getForm();
        $form->setRenderer(new BootstrapRenderer());

        $source = $form->addRadioList('source', _('Zdroj úloh'), array(
            self::SOURCE_ASTRID => _('Astrid'),
            self::SOURCE_ASTRID_2 => _('Astrid (nové XML)'),
            self::SOURCE_FILE => _('XML soubor'),
        ));
        $source->setDefaultValue(self::SOURCE_ASTRID_2);

        // Astrid download
        $seriesItems = range(1, $this->seriesCalculator->getTotalSeries($this->getSelectedContest(), $this->getSelectedYear()));
        $form->addSelect('series', _('Série'))
            ->setItems($seriesItems, false);

        // File upload
        $language = $form->addSelect('lang', _('Jazyk'));
        $language->setItems(self::$languages, false);
        $language->addConditionOn($source, Form::EQUAL, self::SOURCE_FILE)->toggle($language->getHtmlId() . '-pair');

        $upload = $form->addUpload('file', _('XML soubor úloh'));
        $upload->addConditionOn($source, Form::EQUAL, self::SOURCE_FILE)->toggle($upload->getHtmlId() . '-pair');


        $form->addSubmit('submit', _('Importovat'));

        $form->onSuccess[] = callback($this, 'validSubmitSeriesForm');

        return $control;
    }

    private function isLegacyXml(SimpleXMLElement $xml) {
        return $xml->getName() == 'problems';
    }

    public function validSubmitSeriesForm(Form $seriesForm) {
        $values = $seriesForm->getValues();
        $series = $values['series'];
        $files = [];

        switch ($values['source']) {
            case self::SOURCE_ASTRID:
                foreach (self::$languages as $language) {
                    try {
                        $file = $this->downloader->downloadSeriesTasks($this->getSelectedContest(), $this->getSelectedYear(), $series, $language);
                        $files[$language] = $file;
                    } catch (DownloadException $e) {
                        $this->flashMessage(sprintf(_('Úlohy pro jazyk %s se nepodařilo stáhnout.'), $language), self::FLASH_WARNING);
                    }
                }
                break;
            case self::SOURCE_ASTRID_2:
                $file = $this->downloader->downloadSeriesTasks2($this->getSelectedContest(), $this->getSelectedYear(), $series);
                $files[self::LANG_ALL] = $file;
                break;
            case self::SOURCE_FILE:
                if (!$values['file']->isOk()) {
                    throw new UploadException();
                }
                $file = $values['file']->getTemporaryFile();
                $lang = $values['lang'];
                $files[$lang] = $file;
                break;
        }

        $dump = $this->flashDumpFactory->createDefault();
        foreach ($files as $language => $file) {
            try {
                $xml = simplexml_load_file($file);

                if ($this->isLegacyXml($xml)) {
                    $data = new SeriesData($this->getSelectedContest(), $this->getSelectedYear(), $series, $language, $xml);
                    $pipeline = $this->pipelineFactory->create($language);
                    $pipeline->setInput($data);
                    $pipeline->run();

                    $dump->dump($pipeline->getLogger(), $this);
                    $this->flashMessage(sprintf(_('Úlohy pro jazyk %s úspěšně importovány.'), $language), self::FLASH_SUCCESS);
                } else {
                    if ($language != self::LANG_ALL) {
                        $this->flashMessage(sprintf(_('Jazyk %s je ignorován, budou importovány známé jazyky.'), $language));
                    }

                    $data = new SeriesData($this->getSelectedContest(), $this->getSelectedYear(), $series, self::LANG_ALL, $xml);
                    $pipeline = $this->pipelineFactory->create2();
                    $pipeline->setInput($data);
                    $pipeline->run();

                    $dump->dump($pipeline->getLogger(), $this);
                    $this->flashMessage(_('Úlohy pro úspěšně importovány.'), self::FLASH_SUCCESS);
                }
            } catch (PipelineException $e) {
                $this->flashMessage(sprintf('Při ukládání úloh pro jazyk %s došlo k chybě. %s', $language, $e->getMessage()), self::FLASH_ERROR);
                Debugger::log($e);
            } catch (ModelException $e) {
                $this->flashMessage(sprintf('Při ukládání úloh pro jazyk %s došlo k chybě.', $language), self::FLASH_ERROR);
                Debugger::log($e);
            } finally {

                unlink($file);
            }
        }
        $this->redirect('this');
    }

}
