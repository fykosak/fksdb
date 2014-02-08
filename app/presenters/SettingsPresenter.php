<?php

use Authentication\PasswordAuthenticator;
use FKSDB\Components\Forms\Factories\LoginFactory;
use FKSDB\Components\Forms\Rules\UniqueEmailFactory;
use FKSDB\Components\Forms\Rules\UniqueLoginFactory;
use Kdyby\BootstrapFormRenderer\BootstrapRenderer;
use Nette\Application\UI\Form;
use Nette\Forms\Controls\BaseControl;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
class SettingsPresenter extends AuthenticatedPresenter {

    const CONT_LOGIN = 'login';

    /**
     * @var LoginFactory
     */
    private $loginFactory;

    /**
     * @var ServiceLogin
     */
    private $loginService;

    /**
     * @var UniqueEmailFactory
     */
    private $uniqueEmailFactory;

    /**
     * @var UniqueLoginFactory
     */
    private $uniqueLoginFactory;

    public function injectLoginFactory(LoginFactory $loginFactory) {
        $this->loginFactory = $loginFactory;
    }

    public function injectLoginService(ServiceLogin $loginService) {
        $this->loginService = $loginService;
    }

    public function injectUniqueEmailFactory(UniqueEmailFactory $uniqueEmailFactory) {
        $this->uniqueEmailFactory = $uniqueEmailFactory;
    }

    public function injectUniqueLoginFactory(UniqueLoginFactory $uniqueLoginFactory) {
        $this->uniqueLoginFactory = $uniqueLoginFactory;
    }

    public function titleDefault() {
        $this->setTitle(_('Nastavení'));
    }

    public function renderDefault() {
        $login = $this->getUser()->getIdentity();

        $defaults = array(
            self::CONT_LOGIN => $login->toArray(),
        );
        $this->getComponent('settingsForm')->setDefaults($defaults);

        if ($this->getTokenAuthenticator()->isAuthenticatedByToken(ModelAuthToken::TYPE_INITIAL_LOGIN)) {
            $this->flashMessage(_('Nastavte si nové heslo.'), self::FLASH_WARNING);
        }

        if ($this->getTokenAuthenticator()->isAuthenticatedByToken(ModelAuthToken::TYPE_RECOVERY)) {
            $this->flashMessage(_('Nastavte si nové heslo.'), self::FLASH_WARNING);
        }
    }

    protected function createComponentSettingsForm($name) {
        $form = new Form();
        $form->setRenderer(new BootstrapRenderer());

        $login = $this->getUser()->getIdentity();
        $tokenAuthentication =
                $this->getTokenAuthenticator()->isAuthenticatedByToken(ModelAuthToken::TYPE_INITIAL_LOGIN) ||
                $this->getTokenAuthenticator()->isAuthenticatedByToken(ModelAuthToken::TYPE_RECOVERY);

        $group = $form->addGroup(_('Autentizace'));
        $emailRule = $this->uniqueEmailFactory->create($login->getPerson(), $login); //TODO em use it somewhere
        $loginRule = $this->uniqueLoginFactory->create($login);

        if ($tokenAuthentication) {
            $options = LoginFactory::SHOW_PASSWORD | LoginFactory::REQUIRE_PASSWORD;
        } else if (!$login->hash) {
            $options = LoginFactory::SHOW_PASSWORD;
        } else {
            $options = LoginFactory::SHOW_PASSWORD | LoginFactory::VERIFY_OLD_PASSWORD;
        }
        $loginContainer = $this->loginFactory->createLogin($options, $group, $loginRule);
        $form->addComponent($loginContainer, self::CONT_LOGIN);

        if ($loginContainer->getComponent('old_password', false)) {
            $loginContainer['old_password']
                    ->addCondition(Form::FILLED)
                    ->addRule(function(BaseControl $control) use($login) {
                                $hash = PasswordAuthenticator::calculateHash($control->getValue(), $login);
                                return $hash == $login->hash;
                            }, 'Špatně zadané staré heslo.');
        }

        $form->setCurrentGroup();

        $form->addSubmit('send', _('Uložit'));

        $form->onSuccess[] = array($this, 'handleSettingsFormSuccess');
        return $form;
    }

    /**
     * @internal
     * @param Form $form
     */
    public function handleSettingsFormSuccess(Form $form) {
        $values = $form->getValues();
        $tokenAuthentication =
                $this->getTokenAuthenticator()->isAuthenticatedByToken(ModelAuthToken::TYPE_INITIAL_LOGIN) ||
                $this->getTokenAuthenticator()->isAuthenticatedByToken(ModelAuthToken::TYPE_RECOVERY);
        $login = $this->getUser()->getIdentity();

        $loginData = FormUtils::emptyStrToNull($values[self::CONT_LOGIN]);
        if ($loginData['password']) {
            $login->setHash($loginData['password']);
        }

        $this->loginService->updateModel($login, $loginData);
        $this->loginService->save($login);
        $this->flashMessage(_('Uživatelské informace upraveny.'), self::FLASH_SUCCESS);
        if ($tokenAuthentication) {
            $this->flashMessage(_('Heslo nastaveno.'), self::FLASH_SUCCESS); //TODO here may be Facebook ID            
            $this->getTokenAuthenticator()->disposeAuthToken(); // from now on same like password authentication
        }
        $this->redirect('this');
    }

}
