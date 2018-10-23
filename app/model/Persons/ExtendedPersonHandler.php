<?php

namespace Persons;

use Authentication\AccountManager;
use BasePresenter;
use FKSDB\Components\Forms\Controls\ModelDataConflictException;
use FKSDB\ORM\ModelContest;
use FKSDB\ORM\ModelPerson;
use FormUtils;
use Mail\MailTemplateFactory;
use Mail\SendFailedException;
use ModelException;
use Nette\Database\Connection;
use Nette\Diagnostics\Debugger;
use Nette\Forms\Form;
use Nette\InvalidStateException;
use Nette\Object;
use OrgModule\ContestantPresenter;
use ORM\IService;
use ServicePerson;

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
    const RESULT_OK_EXISTING_LOGIN = 1;
    const RESULT_OK_NEW_LOGIN = 2;
    const RESULT_ERROR = false;

    /**
     * @var IService
     */
    protected $service;

    /**
     * @var ServicePerson
     */
    protected $servicePerson;

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
     * @var ModelContest
     */
    private $contest;

    /**
     * @var int
     */
    private $year;

    /**
     * @var string
     */
    private $invitationLang;

    /**
     * @var ModelPerson
     */
    private $person;

    function __construct(IService $service, ServicePerson $servicePerson, Connection $connection, MailTemplateFactory $mailTemplateFactory, AccountManager $accountManager) {
        $this->service = $service;
        $this->servicePerson = $servicePerson;
        $this->connection = $connection;
        $this->mailTemplateFactory = $mailTemplateFactory;
        $this->accountManager = $accountManager;
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

    public function getInvitationLang() {
        return $this->invitationLang;
    }

    public function setInvitationLang($invitationLang) {
        $this->invitationLang = $invitationLang;
    }

    public function getPerson() {
        return $this->person;
    }

    protected final function getReferencedPerson(Form $form) {
        return $form[self::CONT_AGGR][self::EL_PERSON]->getModel();
    }

    public final function handleForm(Form $form, IExtendedPersonPresenter $presenter) {
        $connection = $this->connection;
        try {
            if (!$connection->beginTransaction()) {
                throw new ModelException();
            }
            $values = $form->getValues();
            $create = !$presenter->getModel();

            $person = $this->person = $this->getReferencedPerson($form);
            $this->storeExtendedModel($person, $values, $presenter);

            // create login
            $email = $person->getInfo() ? $person->getInfo()->email : null;
            $login = $person->getLogin();
            $hasLogin = (bool) $login;
            if ($email && !$login) {
                $template = $this->mailTemplateFactory->createLoginInvitation($presenter, $this->getInvitationLang());
                try {
                    $this->accountManager->createLoginWithInvitation($template, $person, $email);
                    $presenter->flashMessage(_('Zvací e-mail odeslán.'), BasePresenter::FLASH_INFO);
                } catch (SendFailedException $e) {
                    $presenter->flashMessage(_('Zvací e-mail se nepodařilo odeslat.'), BasePresenter::FLASH_ERROR);
                }
            }
            // reload the model (this is workaround to avoid caching of empty but newly created referenced/related models)
            $person = $this->person = $this->servicePerson->findByPrimary($this->getReferencedPerson($form)->getPrimary());

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

            if (!$hasLogin) {
                return self::RESULT_OK_NEW_LOGIN;
            } else {
                return self::RESULT_OK_EXISTING_LOGIN;
            }
        } catch (ModelException $e) {
            $connection->rollBack();
            if ($e->getPrevious() && $e->getPrevious()->getCode() == 23000) {
                $presenter->flashMessage($presenter->messageExists(), ContestantPresenter::FLASH_ERROR);
            } else {
                Debugger::log($e, Debugger::ERROR);
                $presenter->flashMessage($presenter->messageError(), ContestantPresenter::FLASH_ERROR);
            }

            return self::RESULT_ERROR;
        } catch (ModelDataConflictException $e) {
            $form->addError(_('Zadaná data se neshodují s již uloženými.'));
            $e->getReferencedId()->getReferencedContainer()->setConflicts($e->getConflicts());
            $e->getReferencedId()->rollback();
            $connection->rollBack();
            return self::RESULT_ERROR;
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
