<?php

declare(strict_types=1);

namespace FKSDB\Models\Persons;

use FKSDB\Models\Authentication\AccountManager;
use FKSDB\Components\Forms\Controls\ReferencedId;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\ModelContestYear;
use Fykosak\NetteORM\AbstractService;
use FKSDB\Modules\Core\BasePresenter;
use Fykosak\NetteORM\AbstractModel;
use FKSDB\Models\ORM\Models\ModelPerson;
use FKSDB\Models\ORM\Services\ServicePerson;
use FKSDB\Models\Utils\FormUtils;
use FKSDB\Models\Mail\SendFailedException;
use Fykosak\NetteORM\Exceptions\ModelException;
use Nette\Database\Connection;
use Nette\Database\Table\ActiveRow;
use Nette\Forms\Form;
use Nette\InvalidStateException;
use Nette\SmartObject;
use FKSDB\Modules\OrgModule\ContestantPresenter;
use Tracy\Debugger;

class ExtendedPersonHandler
{
    use SmartObject;

    public const CONT_AGGR = 'aggr';
    public const CONT_MODEL = 'model';
    public const EL_PERSON = 'person_id';
    public const RESULT_OK_EXISTING_LOGIN = 1;
    public const RESULT_OK_NEW_LOGIN = 2;
    public const RESULT_ERROR = 0;
    protected AbstractService $service;
    protected ServicePerson $servicePerson;
    private Connection $connection;
    private AccountManager $accountManager;
    private ModelContestYear $contestYear;
    private string $invitationLang;
    private ?ModelPerson $person = null;

    public function __construct(
        AbstractService $service,
        ServicePerson $servicePerson,
        Connection $connection,
        AccountManager $accountManager,
        ModelContestYear $contestYear,
        string $invitationLang
    ) {
        $this->service = $service;
        $this->servicePerson = $servicePerson;
        $this->connection = $connection;
        $this->accountManager = $accountManager;
        $this->contestYear = $contestYear;
        $this->invitationLang = $invitationLang;
    }

    public function getInvitationLang(): string
    {
        return $this->invitationLang;
    }

    public function getPerson(): ModelPerson
    {
        return $this->person;
    }

    /**
     * @return ModelPerson|null|AbstractModel|ActiveRow
     */
    final protected function getReferencedPerson(Form $form): ?ActiveRow
    {
        /** @var ReferencedId $input */
        $input = $form[self::CONT_AGGR][self::EL_PERSON];
        return $input->getModel();
    }

    /**
     * @throws BadTypeException
     */
    final public function handleForm(Form $form, ExtendedPersonPresenter $presenter, bool $sendEmail): int
    {
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
                    $presenter->flashMessage(_('E-mail invitation sent.'), BasePresenter::FLASH_INFO);
                } catch (SendFailedException $exception) {
                    $presenter->flashMessage(_('E-mail invitation failed to sent.'), BasePresenter::FLASH_ERROR);
                }
            }
            // reload the model (this is workaround to avoid caching of empty but newly created referenced/related models)
            $this->person = $this->servicePerson->findByPrimary($this->getReferencedPerson($form)->getPrimary());

            /*
             * Finalize
             */
            $this->connection->commit();

            $presenter->flashMessage(
                sprintf(
                    $create ? $presenter->messageCreate() : $presenter->messageEdit(),
                    $this->person->getFullName()
                ),
                ContestantPresenter::FLASH_SUCCESS
            );

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
            $form->addError(_('Data don\'t match already stored data.'));
            $exception->getReferencedId()->getReferencedContainer()->setConflicts($exception->getConflicts());
            $exception->getReferencedId()->rollback();
            $this->connection->rollBack();
            return self::RESULT_ERROR;
        }
    }

    protected function storeExtendedModel(
        ModelPerson $person,
        iterable $values,
        ExtendedPersonPresenter $presenter
    ): void {
        if ($this->contestYear->getContest() === null || $this->contestYear->year === null) {
            throw new InvalidStateException('Must set contest and year before storing contestant.');
        }
        // initialize model
        $model = $presenter->getModel();

        if (!$model) {
            $model = $this->service->createNewModel([
                'contest_id' => $this->contestYear->getContest(),
                'person_id' => $person->getPrimary(),
                'year' => $this->contestYear->year,
            ]);
        }

        // update data
        if (isset($values[self::CONT_MODEL])) {
            $data = FormUtils::emptyStrToNull($values[self::CONT_MODEL]);
            $this->service->updateModel($model, (array)$data);
        }
    }
}
