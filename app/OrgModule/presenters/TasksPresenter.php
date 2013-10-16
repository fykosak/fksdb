<?php

namespace OrgModule;

use ModelException;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use Nette\Diagnostics\Debugger;
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

    public function actionPull() {
        if (!$this->getContestAuthorizator()->isAllowed('task', 'insert', $this->getSelectedContest())) {
            throw new BadRequestException('Nedostatečné oprávnění.', 403);
        }
    }

    protected function createComponentSeriesForm() {
        $seriesForm = new Form();

        $seriesItems = range(1, $this->seriesCalculator->getTotalSeries($this->getSelectedContest(), $this->getSelectedYear()));
        $seriesForm->addSelect('series', 'Série')
                ->setItems($seriesItems, false);

        $seriesForm->addSubmit('submit', 'Stáhnout');

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
                    $this->flashMessage($message);
                }
                $this->flashMessage(sprintf('Úlohy pro jazyk %s úspěšně staženy.', $language));
            } catch (DownloadException $e) {
                $this->flashMessage(sprintf('Úlohy pro jazyk %s se nepodařilo stáhnout.', $language), 'error');
            } catch (ModelException $e) {
                $this->flashMessage(sprintf('Při ukládání úloh pro jazyk %s došlo k chybě.', $language), 'error');
                Debugger::log($e);
            }
        }
    }

}
