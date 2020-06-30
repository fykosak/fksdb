<?php

namespace Persons;

use FKSDB\Authentication\AccountManager;
use FKSDB\Modules\Core\BasePresenter;
use FKSDB\Components\Forms\Controls\ModelDataConflictException;
use FKSDB\Components\Forms\Controls\ReferencedId;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\AbstractServiceMulti;
use FKSDB\ORM\AbstractServiceSingle;
use FKSDB\ORM\IModel;
use FKSDB\ORM\IService;
use FKSDB\ORM\Models\ModelContest;
use FKSDB\ORM\Models\ModelPerson;
use FKSDB\ORM\Services\ServicePerson;
use FKSDB\Utils\FormUtils;
use Mail\SendFailedException;
use FKSDB\Exceptions\ModelException;
use Nette\Database\Connection;
use Nette\Forms\Form;
use Nette\InvalidStateException;
use Nette\SmartObject;
use FKSDB\Modules\OrgModule\ContestantPresenter;
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
     * @var IService|AbstractServiceMulti|AbstractServiceSingle
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
     * @param AccountManager $accountManager
     * @param ModelContest $contest
     * @param int $year
     * @param string $invitationLang
     */
    public function __construct(
        IService $service,
        ServicePerson $servicePerson,
        Connection $connection,
        AccountManager $accountManager,
        ModelContest $contest,
        int $year,
        string $invitationLang
    ) {
        $this->service = $service;
        $this->servicePerson = $servicePerson;
        $this->connection = $connection;
        $this->accountManager = $accountManager;
        $this->contest = $contest;
        $this->year = $year;
        $this->invitationLang = $invitationLang;
    }

    public function getContest(): ModelContest {
        return $this->contest;
    }

    public function getYear(): int {
        return $this->year;
    }

    public function getInvitationLang(): string {
        return $this->invitationLang;
    }

    public function getPerson(): ModelPerson {
        return $this->person;
    }

    /**
     * @param Form $form
     * @return ModelPerson|null|AbstractModelSingle|IModel
     */
    final protected function getReferencedPerson(Form $form) {
        /** @var ReferencedId $input */
        $input = $form[self::CONT_AGGR][self::EL_PERSON];
        return $input->getModel();
    }

    /**
     * @param Form $form
     * @param IExtendedPersonPresenter $presenter
     * @param bool $sendEmail
     * @return int
     * @throws \Exception
     */
    final public function handleForm(Form $form, IExtendedPersonPresenter $presenter, bool $sendEmail): int {

        try {
            $this->connection->beginTransaction();
            $create = !$presenter->getModel();
            $form->getValues();
            $this->person = $this->getReferencedPerson($form);
            $this->storeExtendedModel($this->person, $form->getValues(true), $presenter);

            // create login
            $email = $this->person->getInfo() ? $this->person->getInfo()->email : null;
            $login = $this->person->getLogin();
            $hasLogin = (bool)$login;
            if ($sendEmail && ($email && !$login)) {
                try {
                    $this->accountManager->createLoginWithInvitation($this->person, $email, $this->getInvitationLang());
                    $presenter->flashMessage(_('Zvací e-mail odeslán.'), BasePresenter::FLASH_INFO);
                } catch (SendFailedException $exception) {
                    $presenter->flashMessage(_('Zvací e-mail se nepodařilo odeslat.'), BasePresenter::FLASH_ERROR);
                }
            }
            // reload the model (this is workaround to avoid caching of empty but newly created referenced/related models)
            $this->person = $this->servicePerson->findByPrimary($this->getReferencedPerson($form)->getPrimary());

            /*
             * Finalize
             */
            $this->connection->commit();

            $presenter->flashMessage(sprintf($create ? $presenter->messageCreate() : $presenter->messageEdit(), $this->person->getFullName()), ContestantPresenter::FLASH_SUCCESS);

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
                'contest_id' => $this->contest ? $this->getContest()->contest_id : null,
                'person_id' => $person->getPrimary(),
                'year' => $this->year ? $this->getYear() : null,
            ];
            $model = $this->service->createNewModel((array)$data);
        }

        // update data
        if (isset($values[self::CONT_MODEL])) {
            $data = FormUtils::emptyStrToNull($values[self::CONT_MODEL]);
            $this->service->updateModel2($model, (array)$data);
        }
    }
}
