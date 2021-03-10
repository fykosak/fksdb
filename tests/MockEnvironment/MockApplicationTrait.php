<?php

namespace FKSDB\Tests\MockEnvironment;

use FKSDB\Models\Authentication\LoginUserStorage;
use FKSDB\Models\ORM\Models\ModelLogin;
use FKSDB\Models\ORM\Services\ServiceLogin;
use FKSDB\Models\Mail\MailTemplateFactory;
use FKSDB\Models\YearCalculator;
use Nette\Application\IPresenterFactory;
use Nette\Application\UI\Presenter;
use Nette\DI\Container;
use Nette\Http\Session;
use Tester\Assert;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
trait MockApplicationTrait {

    protected Container $container;

    /**
     * @param Container $container
     * @return void
     */
    protected function setContainer(Container $container) {
        $this->container = $container;
    }

    protected function getContainer(): Container {
        return $this->container;
    }

    protected function mockApplication(): void {
        $mockPresenter = new MockPresenter();
        $application = new MockApplication($mockPresenter);

        $this->container->callInjects($mockPresenter);
        $mailFactory = $this->getContainer()->getByType(MailTemplateFactory::class);
        $mailFactory->injectApplication($application);
    }

    /**
     * @param $token
     * @param null $timeout
     * @return void
     */
    protected function fakeProtection($token, $timeout = null): void {
        $container = $this->getContainer();
        /** @var Session $session */
        $session = $container->getService('session');
        $section = $session->getSection('Nette.Forms.Form/CSRF');
        $key = "key$timeout";
        $section->$key = $token;
    }

    protected function authenticate($login, ?Presenter $presenter = null): void {
        $container = $this->getContainer();
        if (!$login instanceof ModelLogin) {
            $login = $container->getByType(ServiceLogin::class)->findByPrimary($login);
            Assert::type(ModelLogin::class, $login);
        }
        $storage = $container->getByType(LoginUserStorage::class);
        $storage->setIdentity($login);
        $storage->setAuthenticated(true);

        if ($presenter) {
            $login->injectYearCalculator($this->getContainer()->getByType(YearCalculator::class));
            $presenter->getUser()->login($login);
        }
    }

    protected function createPresenter(string $presenterName): Presenter {
        $_COOKIE['_nss'] = '1';
        $presenterFactory = $this->getContainer()->getByType(IPresenterFactory::class);
        $presenter = $presenterFactory->createPresenter($presenterName);
        $presenter->autoCanonicalize = false;

        $this->getContainer()->getByType(LoginUserStorage::class)->setPresenter($presenter);
        return $presenter;
    }

}
