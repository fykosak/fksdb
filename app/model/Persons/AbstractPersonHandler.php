<?php

namespace Persons;

use Authentication\AccountManager;
use BasePresenter;
use FKS\Components\Forms\Controls\ModelDataConflictException;
use FKS\Config\GlobalParameters;
use Mail\MailTemplateFactory;
use Mail\SendFailedException;
use ModelException;
use Nette\ArrayHash;
use Nette\Database\Connection;
use Nette\Diagnostics\Debugger;
use Nette\Forms\Form;
use OrgModule\ContestantPresenter;
use OrgModule\EntityPresenter;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
abstract class AbstractPersonHandler {

    /**
     *
     * @var Connection
     */
    protected $connection;

    /**
     * @var MailTemplateFactory
     */
    protected $mailTemplateFactory;

    /**
     * @var AccountManager
     */
    protected $accountManager;

    /**
     * @var GlobalParameters
     */
    protected $globalParameters;

    function __construct(Connection $connection, MailTemplateFactory $mailTemplateFactory, AccountManager $accountManager, GlobalParameters $globalParameters) {
        $this->connection = $connection;
        $this->mailTemplateFactory = $mailTemplateFactory;
        $this->accountManager = $accountManager;
        $this->globalParameters = $globalParameters;
    }

    abstract protected function getReferencedPerson(Form $form);

    abstract protected function storeExtendedModel(ArrayHash $values);

    public final function handleForm(Form $form, EntityPresenter $presenter) {
        $connection = $this->connection;
        try {
            if (!$connection->beginTransaction()) {
                throw new ModelException();
            }

            $values = $form->getValues();
            $this->storeExtendedModel($values);

            // create login
            $person = $this->getReferencedPerson($form);
            $email = $person->getInfo() ? $person->getInfo()->email : null;
            $login = $person->getLogin();
            if ($email && !$login) {
                $template = $presenter->mailTemplateFactory->createLoginInvitation($presenter, $this->globalParameters['invitation']['defaultLang']);
                try {
                    $presenter->accountManager->createLoginWithInvitation($template, $person, $email);
                    $presenter->flashMessage(_('Zvací e-mail odeslán.'), self::FLASH_INFO);
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

            $presenter->flashMessage(sprintf('Osoba %s založena.', $person->getFullname()), ContestantPresenter::FLASH_SUCCESS);

            $presenter->backlinkRedirect();
            $presenter->redirect('list'); // if there's no backlink
        } catch (ModelException $e) {
            $connection->rollBack();
            Debugger::log($e, Debugger::ERROR);
            $presenter->flashMessage(_('Chyba při zakládání osoby.'), ContestantPresenter::FLASH_ERROR);
        } catch (ModelDataConflictException $e) {
            $form->addError(_('Zadaná data se neshodují s již uloženými.'));
            $e->getReferencedId()->getReferencedContainer()->setConflicts($e->getConflicts());
            $e->getReferencedId()->rollback();
            $connection->rollBack();
        }
    }

}
