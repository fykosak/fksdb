<?php

namespace OrgModule;

use Kdyby\BootstrapFormRenderer\BootstrapRenderer;
use ModelException;
use Nette\Application\UI\Form;
use Nette\Diagnostics\Debugger;
use Pipeline\PipelineException;
use SeriesCalculator;
use Tasks\DownloaderFactory;
use Tasks\DownloadException;
use Tasks\PipelineFactory;
use Tasks\SeriesData;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
class TasksPresenter extends BasePresenter {

    /**
     * @var SeriesCalculator
     */
    private $seriesCalculator;

    /**
     * @var DownloaderFactory
     */
    private $downloaderFactory;

    /**
     * @var PipelineFactory
     */
    private $pipelineFactory;

    public function injectSeriesCalculator(SeriesCalculator $seriesCalculator) {
        $this->seriesCalculator = $seriesCalculator;
    }

    public function injectDownloaderFactory(DownloaderFactory $downloaderFactory) {
        $this->downloaderFactory = $downloaderFactory;
    }

    public function injectPipelineFactory(PipelineFactory $pipelineFactory) {
        $this->pipelineFactory = $pipelineFactory;
    }

    public function authorizedPull() {
        $this->setAuthorized($this->getContestAuthorizator()->isAllowed('task', 'insert', $this->getSelectedContest()));
    }

    public function titlePull() {
        $this->setTitle(_('Stažení úloh'));
    }

    protected function createComponentSeriesForm() {
        $seriesForm = new Form();
        $seriesForm->setRenderer(new BootstrapRenderer());

        $seriesItems = range(1, $this->seriesCalculator->getTotalSeries($this->getSelectedContest(), $this->getSelectedYear()));
        $seriesForm->addSelect('series', _('Série'))
                ->setItems($seriesItems, false);

        $seriesForm->addSubmit('submit', _('Stáhnout'));

        $seriesForm->onSuccess[] = callback($this, 'validSubmitSeriesForm');

        return $seriesForm;
    }

    public function validSubmitSeriesForm(Form $seriesForm) {
        $values = $seriesForm->getValues();
        $series = $values['series'];

        $this->pullSeries($series);
        $this->redirect('this');
    }

    private function pullSeries($series) {
        $languages = array('cs', 'en');

        foreach ($languages as $language) {
            $downloader = $this->downloaderFactory->create($language);
            $pipeline = $this->pipelineFactory->create($language);
            try {
                $XMLfilename = $downloader->download($this->getSelectedContest(), $this->getSelectedYear(), $series);
                $data = new SeriesData($this->getSelectedContest(), $this->getSelectedYear(), $series, simplexml_load_file($XMLfilename));

                $pipeline->setInput($data);
                $pipeline->run();
                unlink($XMLfilename);

                foreach ($pipeline->getLog() as $message) {
                    $this->flashMessage($message, self::FLASH_INFO);
                }
                $this->flashMessage(sprintf('Úlohy pro jazyk %s úspěšně staženy.', $language), self::FLASH_SUCCESS);
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
    }

}
