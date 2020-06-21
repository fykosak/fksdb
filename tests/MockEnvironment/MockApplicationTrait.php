<?php

namespace MockEnvironment;

use Authentication\LoginUserStorage;
use FKSDB\ORM\Models\ModelLogin;
use FKSDB\ORM\Services\ServiceLogin;
use Mail\MailTemplateFactory;
use Nette\Application\IPresenter;
use Nette\Application\IPresenterFactory;
use Nette\DI\Container;
use Tester\Assert;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
trait MockApplicationTrait {
    /**
     * @var Container
     */
    private $container;

    /**
     * @param Container $container
     * @return void
     */
    protected function setContainer(Container $container) {
        $this->container = $container;
    }

    /**
     * @return Container
     */
    protected function getContainer() {
        return $this->container;
    }

    protected function mockApplication() {
        $container = $this->getContainer();
        $mockPresenter = new MockPresenter();
        $container->callInjects($mockPresenter);
        $application = new MockApplication($mockPresenter);

        $mailFactory = $container->getByType(MailTemplateFactory::class);
        $mailFactory->injectApplication($application);
    }

    /**
     * @param $token
     * @param null $timeout
     * @return void
     */
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
            $login = $container->getByType(ServiceLogin::class)->findByPrimary($login);
            Assert::type(ModelLogin::class, $login);
        }
        $storage = $container->getByType(LoginUserStorage::class);
        $storage->setIdentity($login);
        $storage->setAuthenticated(true);
    }

    /**
     * @param string $presenterName
     * @return IPresenter
     */
    protected function createPresenter($presenterName): IPresenter {
        $presenterFactory = $this->getContainer()->getByType(IPresenterFactory::class);
        $presenter = $presenterFactory->createPresenter($presenterName);
        $presenter->autoCanonicalize = false;

        $this->getContainer()->getByType(LoginUserStorage::class)->setPresenter($presenter);
        return $presenter;
    }

}
