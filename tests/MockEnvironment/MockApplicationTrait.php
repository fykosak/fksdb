<?php

namespace MockEnvironment;

use ModelLogin;
use Nette\DI\Container;
use Tester\Assert;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
trait MockApplicationTrait {

    protected function mockApplication(Container $container) {
        $mockPresenter = new MockPresenter($container);
        $container->callMethod(array($mockPresenter, 'injectTranslator'));
        $application = new MockApplication($mockPresenter);

        $mailFactory = $container->getByType('Mail\MailTemplateFactory');
        $mailFactory->injectApplication($application);
    }

    protected function authenticate(Container $container, $login) {
        if (!$login instanceof ModelLogin) {
            $login = $container->getService('ServiceLogin')->findByPrimary($login);
            Assert::type('ModelLogin', $login);
        }
        $storage = $container->getByType('Authentication\LoginUserStorage');
        $storage->setIdentity($login);
        $storage->setAuthenticated(true);
    }

}
