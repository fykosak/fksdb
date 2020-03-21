<?php

namespace Persons;

use FKSDB\Authentication\AccountManager;
use BasePresenter;
use FKSDB\Components\Forms\Controls\ModelDataConflictException;
use FKSDB\ORM\IService;
use FKSDB\ORM\Models\ModelContest;
use FKSDB\ORM\Models\ModelPerson;
use FKSDB\ORM\Services\ServicePerson;
use FormUtils;
use Mail\MailTemplateFactory;
use Mail\SendFailedException;
use ModelException;
use Nette\Application\IPresenter;
use Nette\Application\UI\Presenter;
use Nette\Database\Connection;
use Nette\Forms\Form;
use Nette\InvalidStateException;
use Nette\SmartObject;
use Nette\Utils\ArrayHash;
use OrgModule\ContestantPresenter;
use Tracy\Debugger;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class ExtendedPersonHandler {
    use SmartObject;
    const CONT_AGGR = 'aggr';
    const CONT_PERSON = 'person';
    const CONT_MODEL = 'model';
    const EL_PERSON = 'person_id';
    const RESULT_OK_EXISTING_LOGIN = 1;
    const RESULT_OK_NEW_LOGIN = 2;
    const RESULT_ERROR = 0;

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

    /**
     * ExtendedPersonHandler constructor.
     * @param IService $service
     * @param ServicePerson $servicePerson
     * @param Connection $connection
     * @param MailTemplateFactory $mailTemplateFactory
     * @param AccountManager $accountManager
     */
    function __construct(IService $service, ServicePerson $servicePerson, Connection $connection, MailTemplateFactory $mailTemplateFactory, AccountManager $accountManager) {
        $this->service = $service;
        $this->servicePerson = $servicePerson;
        $this->connection = $connection;
        $this->mailTemplateFactory = $mailTemplateFactory;
        $this->accountManager = $accountManager;
    }

    /**
     * @return ModelContest
     */
    public function getContest() {
        return $this->contest;
    }

    /**
     * @param ModelContest $contest
     */
    public function setContest(ModelContest $contest) {
        $this->contest = $contest;
    }

    /**
     * @return int
     */
    public function getYear() {
        return $this->year;
    }

    /**
     * @param $year
     */
    public function setYear($year) {
        $this->year = $year;
    }

    /**
     * @return string
     */
    public function getInvitationLang() {
        return $this->invitationLang;
    }

    /**
     * @param $invitationLang
     */
    public function setInvitationLang($invitationLang) {
        $this->invitationLang = $invitationLang;
    }

    /**
     * @return ModelPerson
     */
    public function getPerson() {
        return $this->person;
    }

    /**
     * @param Form $form
     * @return mixed
     */
    protected final function getReferencedPerson(Form $form) {
        return $form[self::CONT_AGGR][self::EL_PERSON]->getModel();
    }

    /**
     * @param Form $form
     * @param IExtendedPersonPresenter $presenter
     * @param bool $sendEmail
     * @return int
     * @throws \Exception
     */
    public final function handleForm(Form $form, IExtendedPersonPresenter $presenter, bool $sendEmail): int {

        try {
            if (!$this->connection->beginTransaction()) {
                throw new ModelException();
            }
            $create = !$presenter->getModel();

            $this->person = $this->getReferencedPerson($form);
            $this->storeExtendedModel($form->getValues(), $presenter);

            // create login
            $email = $this->person->getInfo() ? $this->person->getInfo()->email : null;

            if ($sendEmail && ($email && !$this->person->getLogin())) {
                $this->sendLoginInvitation($presenter, $email);
            }
            // reload the model (this is workaround to avoid caching of empty but newly created referenced/related models)
            $this->person = $this->servicePerson->findByPrimary($this->getReferencedPerson($form)->getPrimary());

            /*
             * Finalize
             */
            if (!$this->connection->commit()) {
                throw new ModelException();
            }

            $presenter->flashMessage(sprintf($create ? $presenter->messageCreate() : $presenter->messageEdit(), $this->person->getFullname()), ContestantPresenter::FLASH_SUCCESS);

            if (!$this->person->getLogin()) {
                return self::RESULT_OK_NEW_LOGIN;
            } else {
                return self::RESULT_OK_EXISTING_LOGIN;
            }
        } catch (ModelException $exception) {
            $this->connection->rollBack();
            if ($exception->getPrevious() && $exception->getPrevious()->getCode() == 23000) {
                $presenter->flashMessage($presenter->messageExists(), ContestantPresenter::FLASH_ERROR);
            } else {
                Debugger::log($exception, Debugger::ERROR);
                $presenter->flashMessage($presenter->messageError(), ContestantPresenter::FLASH_ERROR);
            }

            return self::RESULT_ERROR;
        } catch (ModelDataConflictException $exception) {
            $form->addError(_('Zadaná data se neshodují s již uloženými.'));
            $exception->getReferencedId()->getReferencedContainer()->setConflicts($exception->getConflicts());
            $exception->getReferencedId()->rollback();
            $this->connection->rollBack();
            return self::RESULT_ERROR;
        }
    }

    /**
     * @param Presenter $presenter
     * @param string $email
     * @throws \Exception
     */
    private function sendLoginInvitation(Presenter $presenter, string $email) {
        try {
            $this->accountManager->createLoginWithInvitation($this->person, $email);
            $presenter->flashMessage(_('Zvací e-mail odeslán.'), BasePresenter::FLASH_INFO);
        } catch (SendFailedException $exception) {
            $presenter->flashMessage(_('Zvací e-mail se nepodařilo odeslat.'), BasePresenter::FLASH_ERROR);
        }
    }

    /**
     * @param array|ArrayHash $values
     * @param IExtendedPersonPresenter $presenter
     */
    protected function storeExtendedModel($values, $presenter) {
        if ($this->contest === null || $this->year === null) {
            throw new InvalidStateException('Must set contest and year before storing contestant.');
        }
        // initialize model
        $model = $presenter->getModel();
        $data = [];
        if (isset($values[self::CONT_MODEL])) {
            $data = FormUtils::emptyStrToNull($values[self::CONT_MODEL]);
        }
        if (!$model) {
            $data = array_merge([
                'contest_id' => $this->getContest()->contest_id,
                'year' => $this->getYear(),
                'person_id' => $this->person->getPrimary(),
            ], $data);
            $this->service->createNewModel($data);
        } else {
            $this->service->updateModel($model, $data);
        }
    }
}
