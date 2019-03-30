<?php

namespace MockEnvironment;

use FKSDB\ORM\Models\ModelLogin;
use Nette\DI\Container;
use Tester\Assert;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
trait MockApplicationTrait {

    private $container;

    protected function setContainer(Container $container) {
        $this->container = $container;
    }

    protected function getContainer() {
        return $this->container;
    }

    protected function mockApplication() {
        $container = $this->getContainer();
        $mockPresenter = new MockPresenter($container);
        $container->callMethod(array($mockPresenter, 'injectTranslator'));
        $application = new MockApplication($mockPresenter);

        $mailFactory = $container->getByType('Mail\MailTemplateFactory');
        $mailFactory->injectApplication($application);
    }

    protected function fakeProtection($token, $timeout = null) {
        $container = $this->getContainer();
        $session = $container->getService('session');
        $section = $session->getSection('Nette.Forms.Form/CSRF');
        $key = "key$timeout";
        $section->$key = $token;
    }

    protected function authenticate($login) {
        $container = $this->getContainer();
        if (!$login instanceof ModelLogin) {
            $login = $container->getService('ServiceLogin')->findByPrimary($login);
            Assert::type('FKSDB\ORM\Models\ModelLogin', $login);
        }
        $storage = $container->getByType('Authentication\LoginUserStorage');
        $storage->setIdentity($login);
        $storage->setAuthenticated(true);
    }

    protected function createPresenter($presenterName) {
        $presenterFactory = $this->getContainer()->getByType('Nette\Application\IPresenterFactory');
        $presenter = $presenterFactory->createPresenter($presenterName);
        $presenter->autoCanonicalize = false;

        $this->getContainer()->getByType('Authentication\LoginUserStorage')->setPresenter($presenter);
        return $presenter;
    }

}
