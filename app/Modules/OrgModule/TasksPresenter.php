<?php

namespace FKSDB\Modules\OrgModule;

use FKSDB\Model\Astrid\Downloader;
use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Model\Exceptions\BadTypeException;
use Fykosak\Utils\Logging\FlashMessageDump;
use FKSDB\Model\Pipeline\PipelineException;
use FKSDB\Model\SeriesCalculator;
use FKSDB\Model\Submits\UploadException;
use FKSDB\Model\Tasks\PipelineFactory;
use FKSDB\Model\Tasks\SeriesData;
use FKSDB\Model\UI\PageTitle;
use Fykosak\Utils\Logging\Message;
use Fykosak\Utils\ORM\Exceptions\ModelException;
use Nette\Application\AbortException;
use Nette\Application\UI\Form;
use Nette\DeprecatedException;
use Nette\InvalidStateException;
use SimpleXMLElement;
use Tracy\Debugger;

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

    final public function injectQuarterly(
        SeriesCalculator $seriesCalculator,
        PipelineFactory $pipelineFactory,
        Downloader $downloader
    ): void {
        $this->seriesCalculator = $seriesCalculator;
        $this->pipelineFactory = $pipelineFactory;
        $this->downloader = $downloader;
    }

    public function authorizedImport(): void {
        $this->setAuthorized($this->contestAuthorizator->isAllowed('task', 'insert', $this->getSelectedContest()));
    }

    public function titleImport(): void {
        $this->setPageTitle(new PageTitle(_('Task import'), 'fa fa-upload'));
    }

    /**
     * @return FormControl
     * @throws BadTypeException
     */
    protected function createComponentSeriesForm(): FormControl {
        $control = new FormControl($this->getContext());
        $form = $control->getForm();

        $source = $form->addRadioList('source', _('Problem source'), [
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
                $this->flashMessage(_('Tasks successfully imported.'), Message::LVL_SUCCESS);
            }
        } catch (PipelineException $exception) {
            $this->flashMessage(sprintf(_('Error during import. %s'), $exception->getMessage()), Message::LVL_ERROR);
            Debugger::log($exception);
        } catch (ModelException $exception) {
            $this->flashMessage(sprintf(_('Error during import.')), Message::LVL_ERROR);
            Debugger::log($exception);
        } catch (DeprecatedException $exception) {
            $this->flashMessage(_('Legacy XML format is deprecated'), Message::LVL_ERROR);
        } finally {
            unlink($file);
        }
        $this->redirect('this');
    }
}
