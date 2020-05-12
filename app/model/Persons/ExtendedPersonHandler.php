<?php

namespace Persons;

use FKSDB\Authentication\AccountManager;
use BasePresenter;
use FKSDB\Components\Forms\Controls\ModelDataConflictException;
use FKSDB\ORM\AbstractServiceMulti;
use FKSDB\ORM\AbstractServiceSingle;
use FKSDB\ORM\IService;
use FKSDB\ORM\Models\ModelContest;
use FKSDB\ORM\Models\ModelPerson;
use FKSDB\ORM\Services\ServicePerson;
use FormUtils;
use Mail\MailTemplateFactory;
use Mail\SendFailedException;
use FKSDB\Exceptions\ModelException;
use Nette\Database\Connection;
use Nette\Forms\Form;
use Nette\InvalidStateException;
use Nette\SmartObject;
use OrgModule\ContestantPresenter;
use Tracy\Debugger;
use Traversable;

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
     * @var \FKSDB\ORM\IService|AbstractServiceMulti|AbstractServiceSingle
     */
    protected $service;

    /**
     * @var \FKSDB\ORM\Services\ServicePerson
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
     * @var \FKSDB\ORM\Models\ModelContest
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
     * @param \FKSDB\ORM\IService $service
     * @param \FKSDB\ORM\Services\ServicePerson $servicePerson
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
     * @return \FKSDB\ORM\Models\ModelContest
     */
    public function getContest() {
        return $this->contest;
    }

    /**
     * @param \FKSDB\ORM\Models\ModelContest $contest
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
     * @return \FKSDB\ORM\Models\ModelPerson
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
    public final function handleForm(Form $form, IExtendedPersonPresenter $presenter, bool $sendEmail) {

        try {
            $this->connection->beginTransaction();
            $values = $form->getValues();
            $create = !$presenter->getModel();

            $person = $this->person = $this->getReferencedPerson($form);
            $this->storeExtendedModel($person, $values, $presenter);

            // create login
            $email = $person->getInfo() ? $person->getInfo()->email : null;
            $login = $person->getLogin();
            $hasLogin = (bool)$login;
            if ($sendEmail && ($email && !$login)) {
                // $template = $this->mailTemplateFactory->createLoginInvitation($presenter, $this->getInvitationLang());
                try {
                    $this->accountManager->createLoginWithInvitation($person, $email);
                    $presenter->flashMessage(_('Zvací e-mail odeslán.'), BasePresenter::FLASH_INFO);
                } catch (SendFailedException $exception) {
                    $presenter->flashMessage(_('Zvací e-mail se nepodařilo odeslat.'), BasePresenter::FLASH_ERROR);
                }
            }
            // reload the model (this is workaround to avoid caching of empty but newly created referenced/related models)
            $person = $this->person = $this->servicePerson->findByPrimary($this->getReferencedPerson($form)->getPrimary());

            /*
             * Finalize
             */
            $this->connection->commit();

            if ($create) {
                $msg = $presenter->messageCreate();
            } else {
                $msg = $presenter->messageEdit();
            }
            $presenter->flashMessage(sprintf($msg, $person->getFullName()), ContestantPresenter::FLASH_SUCCESS);

            if (!$hasLogin) {
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
     * @param ModelPerson $person
     * @param array|Traversable $values
     * @param IExtendedPersonPresenter $presenter
     */
    protected function storeExtendedModel(ModelPerson $person, $values, IExtendedPersonPresenter $presenter) {
        if ($this->contest === null || $this->year === null) {
            throw new InvalidStateException('Must set contest and year before storing contestant.');
        }
        // initialize model
        $model = $presenter->getModel();

        if (!$model) {
            $data = [
                'contest_id' => $this->getContest()->contest_id,
                'person_id' => $person->getPrimary(),
                'year' => $this->getYear(),
            ];
            $model = $this->service->createNewModel($data);
        }

        // update data
        if (isset($values[self::CONT_MODEL])) {
            $data = FormUtils::emptyStrToNull($values[self::CONT_MODEL]);
            $this->service->updateModel2($model, $data);
        }
    }
}
