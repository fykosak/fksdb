<?php

namespace FKSDB\Components\Events;

use BasePresenter;
use Events\Machine\Machine;
use Events\Model\ApplicationHandler;
use Events\Model\Grid\SingleEventSource;
use Events\Model\ImportHandler;
use Events\Model\ImportHandlerException;
use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Logging\FlashMessageDump;
use FKSDB\Utils\CSVParser;
use Kdyby\BootstrapFormRenderer\BootstrapRenderer;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Nette\DI\Container;
use Nette\Diagnostics\Debugger;
use Nette\Forms\Controls\SelectBox;
use Nette\Forms\Controls\SubmitButton;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class ImportComponent extends Control {

    /**
     * @var Machine
     */
    private $machine;

    /**
     * @var SingleEventSource
     */
    private $source;

    /**
     * @var ApplicationHandler
     */
    private $handler;

    /**
     * @var FlashMessageDump
     */
    private $flashDump;

    /**
     * @var Container
     */
    private $container;

    function __construct(Machine $machine, SingleEventSource $source, ApplicationHandler $handler, FlashMessageDump $flashDump, Container $container) {
        parent::__construct();
        $this->machine = $machine;
        $this->source = $source;
        $this->handler = $handler;
        $this->flashDump = $flashDump;
        $this->container = $container;
    }

    protected function createComponentFormImport($name) {
        $control = new FormControl();
        $form = $control->getForm();
        $form->setRenderer(new BootstrapRenderer());

        $form->addUpload('file', _('Soubor s přihláškami'))
                ->addRule(Form::FILLED)
                ->addRule(Form::MIME_TYPE, _('Lze nahrávat pouze CSV soubory.'), 'text/plain'); //TODO verify this check at production server

        $form->addRadioList('errorMode', _('Chování při chybě'))
                ->setItems(array(
                    ApplicationHandler::ERROR_ROLLBACK => _('Zastavit import a rollbackovat.'),
                    ApplicationHandler::ERROR_SKIP => _('Přeskočit přihlášku a pokračovat.'),
                ))
                ->setDefaultValue(ApplicationHandler::ERROR_SKIP);



        $form->addRadioList('transitions', _('Přechody přihlášek'))
                ->setItems(array(
                    ApplicationHandler::STATE_TRANSITION => _('Vykonat přechod, pokud je možný (jinak chyba).'),
                    ApplicationHandler::STATE_OVERWRITE => _('Pouze nastavit stav.'),
                ))
                ->setDefaultValue(ApplicationHandler::STATE_TRANSITION);

        $form->addRadioList('stateless', _('Přihlášky bez uvedeného stavu'))
                ->setItems(array(
                    ImportHandler::STATELESS_IGNORE => _('Ignorovat.'),
                    ImportHandler::STATELESS_KEEP => _('Ponechat původní stav.'),
                ))
                ->setDefaultValue(ImportHandler::STATELESS_IGNORE);

        $form->addComponent($this->createKeyElement(), 'key');

        $form->addSubmit('import', _('Importovat'))->onClick[] = function(SubmitButton $submit) {
                    $this->handleFormImport($submit->getForm());
                };

        return $control;
    }

    public function render() {
        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'ImportComponent.latte');
        $this->template->render();
    }

    private function handleFormImport(Form $form) {
        $values = $form->getValues();
        try {
            // process form values
            $filename = $values['file']->getTemporaryFile();
            $parser = new CSVParser($filename, CSVParser::INDEX_FROM_HEADER);

            $keyName = $values['key'];
            $transitions = $values['transitions'];
            $errorMode = $values['errorMode'];
            $stateless = $values['stateless'];

            // initialize import handler
            $importHandler = new ImportHandler($this->container);
            $importHandler->setInput($parser, $keyName);
            $importHandler->setSource($this->source);

            Debugger::timer();
            $result = $importHandler->import($this->handler, $transitions, $errorMode, $stateless);
            $elapsedTime = Debugger::timer();


            $this->flashDump->dump($this->handler->getLogger(), $this->getPresenter());
            if ($result) {
                $this->getPresenter()->flashMessage(sprintf(_('Import úspěšně proběhl (%.2f s).'), $elapsedTime), BasePresenter::FLASH_SUCCESS);
            } else {
                $this->getPresenter()->flashMessage(sprintf(_('Import proběhl s chybami (%.2f s).'), $elapsedTime), BasePresenter::FLASH_WARNING);
            }

            $this->redirect('this');
        } catch (ImportHandlerException $e) {
            $this->flashDump->dump($this->handler->getLogger(), $this->getPresenter());
            $this->getPresenter()->flashMessage($e->getMessage(), BasePresenter::FLASH_ERROR);
        }
    }

    private function createKeyElement() {
        $baseHolder = $this->source->getDummyHolder()->getPrimaryHolder();
        $options = array();
        foreach ($baseHolder->getFields() as $field) {
            $options[$field->getName()] = $baseHolder->getName() . '.' . $field->getName();
        }
        $primaryKey = $baseHolder->getService()->getTable()->getPrimary();
        $options[$primaryKey] = $baseHolder->getName() . '.' . $primaryKey;

        asort($options);

        $element = new SelectBox(_('Klíčový atribut'), $options);
        $default = isset($options['person_id']) ? 'person_id' : $primaryKey;
        $element->setDefaultValue($default);

        return $element;
    }

}
