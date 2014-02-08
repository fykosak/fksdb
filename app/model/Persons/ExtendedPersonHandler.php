<?php

namespace Persons;

use Authentication\AccountManager;
use BasePresenter;
use FKS\Components\Forms\Controls\ModelDataConflictException;
use FKS\Config\GlobalParameters;
use FormUtils;
use Mail\MailTemplateFactory;
use Mail\SendFailedException;
use ModelContest;
use ModelException;
use ModelPerson;
use Nette\Database\Connection;
use Nette\Diagnostics\Debugger;
use Nette\Forms\Form;
use Nette\InvalidStateException;
use Nette\Object;
use OrgModule\ContestantPresenter;
use OrgModule\ExtendedPersonPresenter;
use ORM\IService;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
class ExtendedPersonHandler extends Object {

    const CONT_AGGR = 'aggr';
    const CONT_PERSON = 'person';
    const CONT_MODEL = 'model';
    const EL_PERSON = 'person_id';

    /**
     * @var IService
     */
    protected $service;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var MailTemplateFactory
     */
    private $mailTemplateFactory;

    /**
     * @var AccountManager
     */
    private $accountManager;

    /**
     * @var GlobalParameters
     */
    private $globalParameters;

    /**
     * @var ModelContest
     */
    private $contest;

    /**
     * @var int
     */
    private $year;

    function __construct(IService $service, Connection $connection, MailTemplateFactory $mailTemplateFactory, AccountManager $accountManager, GlobalParameters $globalParameters) {
        $this->service = $service;
        $this->connection = $connection;
        $this->mailTemplateFactory = $mailTemplateFactory;
        $this->accountManager = $accountManager;
        $this->globalParameters = $globalParameters;
    }

    public function getContest() {
        return $this->contest;
    }

    public function setContest(ModelContest $contest) {
        $this->contest = $contest;
    }

    public function getYear() {
        return $this->year;
    }

    public function setYear($year) {
        $this->year = $year;
    }

    protected final function getReferencedPerson(Form $form) {
        return $form[self::CONT_AGGR][self::EL_PERSON]->getModel();
    }

    public final function handleForm(Form $form, ExtendedPersonPresenter $presenter) {
        $connection = $this->connection;
        try {
            if (!$connection->beginTransaction()) {
                throw new ModelException();
            }
            $values = $form->getValues();
            $create = !$presenter->getModel();

            $person = $this->getReferencedPerson($form);
            $this->storeExtendedModel($person, $values, $presenter);

            // create login
            $email = $person->getInfo() ? $person->getInfo()->email : null;
            $login = $person->getLogin();
            if ($email && !$login) {
                $template = $this->mailTemplateFactory->createLoginInvitation($presenter, $this->globalParameters['invitation']['defaultLang']);
                try {
                    $this->accountManager->createLoginWithInvitation($template, $person, $email);
                    $presenter->flashMessage(_('Zvací e-mail odeslán.'), BasePresenter::FLASH_INFO);
                } catch (SendFailedException $e) {
                    $presenter->flashMessage(_('Zvací e-mail se nepodařilo odeslat.'), BasePresenter::FLASH_ERROR);
                }
            }

            /*
             * Finalize
             */
            if (!$connection->commit()) {
                throw new ModelException();
            }

            if ($create) {
                $msg = $presenter->messageCreate();
            } else {
                $msg = $presenter->messageEdit();
            }
            $presenter->flashMessage(sprintf($msg, $person->getFullname()), ContestantPresenter::FLASH_SUCCESS);

            $presenter->backlinkRedirect();
            $presenter->redirect('list'); // if there's no backlink
        } catch (ModelException $e) {
            $connection->rollBack();
            Debugger::log($e, Debugger::ERROR);
            $presenter->flashMessage($presenter->messageError(), ContestantPresenter::FLASH_ERROR);
        } catch (ModelDataConflictException $e) {
            $form->addError(_('Zadaná data se neshodují s již uloženými.'));
            $e->getReferencedId()->getReferencedContainer()->setConflicts($e->getConflicts());
            $e->getReferencedId()->rollback();
            $connection->rollBack();
        }
    }

    protected function storeExtendedModel(ModelPerson $person, $values, $presenter) {
        if ($this->contest === null || $this->year === null) {
            throw new InvalidStateException('Must set contest and year before storing contestant.');
        }
        // initialize model
        $model = $presenter->getModel();
        if (!$model) {
            $data = array(
                'contest_id' => $this->getContest()->contest_id,
                'year' => $this->getYear(),
            );
            $model = $this->service->createNew($data);
            $model->person_id = $person->getPrimary();
        }

        // update data
        if (isset($values[self::CONT_MODEL])) {
            $data = FormUtils::emptyStrToNull($values[self::CONT_MODEL]);
            $this->service->updateModel($model, $data);
        }

        // store model
        $this->service->save($model);
    }

}
