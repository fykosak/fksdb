<?php

namespace BrawlModule;

use Astrid\Downloader;
use Astrid\DownloadException;
use FKS\Application\UploadException;
use FKSDB\model\Brawl\Rooms\PipelineFactory;
use Kdyby\BootstrapFormRenderer\BootstrapRenderer;
use Logging\FlashDumpFactory;
use ModelException;
use Nette\Application\UI\Form;
use Nette\Diagnostics\Debugger;
use Pipeline\PipelineException;

/**
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class RoomsPresenter extends BasePresenter {

    const SOURCE_ASTRID = 'astrid';

    const SOURCE_FILE = 'file';

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

    public function injectDownloader(Downloader $downloader) {
        $this->downloader = $downloader;
    }

    public function injectPipelineFactory(PipelineFactory $pipelineFactory) {
        $this->pipelineFactory = $pipelineFactory;
    }

    function injectFlashDumpFactory(FlashDumpFactory $flashDumpFactory) {
        $this->flashDumpFactory = $flashDumpFactory;
    }

    public function titleImport() {
        $this->setTitle(_('Import rozdělení do místností'));
    }

    public function authorizedImport() {
        $this->setAuthorized(($this->eventIsAllowed('brawl', 'roomsImport')));
    }

    protected function createComponentRoomsImportForm() {
        $seriesForm = new Form();
        $seriesForm->setRenderer(new BootstrapRenderer());

        $source = $seriesForm->addRadioList('source', _('Zdroj dat'), array(
            self::SOURCE_ASTRID => _('Astrid'),
            self::SOURCE_FILE => _('CSV soubor'),
        ));
        $source->setDefaultValue(self::SOURCE_ASTRID);

        // Astrid download -- has no special form elements
        // File upload
        $upload = $seriesForm->addUpload('file', _('CSV soubor rozdělení'));
        $upload->addConditionOn($source, Form::EQUAL, self::SOURCE_FILE)->toggle($upload->getHtmlId() . '-pair');


        $seriesForm->addSubmit('submit', _('Importovat'));

        $seriesForm->onSuccess[] = callback($this, 'validRoomsImportForm');

        return $seriesForm;
    }

    public function validRoomsImportForm(Form $seriesForm) {
        $values = $seriesForm->getValues();

        try {
            // obtain file
            switch ($values['source']) {
                default:
                case self::SOURCE_ASTRID:
                    $contest = $this->getEvent()->getContest();
                    $year = $this->getEvent()->year;
                    $file = $this->downloader->downloadBrawlRooms($contest, $year);
                    break;
                case self::SOURCE_FILE:
                    if (!$values['file']->isOk()) {
                        throw new UploadException();
                    }
                    $file = $values['file']->getTemporaryFile();
                    break;
            }

            // process file
            $pipeline = $this->pipelineFactory->create($this->getEvent());

            $pipeline->setInput($file);
            $pipeline->run();
            unlink($file);

            $dump = $this->flashDumpFactory->createDefault();
            $dump->dump($pipeline->getLogger(), $this);
            $this->flashMessage(_('Rozdělení týmů importováno.'), self::FLASH_SUCCESS);
        } catch (DownloadException $e) {
            $this->flashMessage(_('Rozdělení týmů se nepodařilo stáhnout.'), self::FLASH_ERROR);
        } catch (UploadException $e) {
            $this->flashMessage(_('Soubor s rozdělením týmů se nepodařilo uploadovat.'), self::FLASH_ERROR);
        } catch (PipelineException $e) {
            $this->flashMessage(_('Rozdělení týmů se nepodařilo uložit.'), self::FLASH_ERROR);
            Debugger::log($e);
        } catch (ModelException $e) {
            $this->flashMessage(_('Rozdělení týmů se nepodařilo uložit.'), self::FLASH_ERROR);
            Debugger::log($e);
        }

        $this->redirect('this');
    }

}
