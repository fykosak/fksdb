<?php

namespace MockEnvironment;
/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
trait MockApplicationTrait {

    protected function mockApplication($container) {
        $mockPresenter = new MockPresenter($container);
        $container->callMethod(array($mockPresenter, 'injectTranslator'));
        $application = new MockApplication($mockPresenter);

        $mailFactory = $container->getByType('Mail\MailTemplateFactory');
        $mailFactory->injectApplication($application);
    }

}
