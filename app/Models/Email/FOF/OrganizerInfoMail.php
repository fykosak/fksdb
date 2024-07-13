<?php

declare(strict_types=1);

namespace FKSDB\Models\Email\FOF;

use FKSDB\Components\DataTest\DataTestFactory;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\Transitions\Holder\TeamHolder;
use FKSDB\Modules\Core\Language;
use Nette\DI\Container;

class OrganizerInfoMail extends InfoEmail
{
    private Container $container;

    public function injectContainer(Container $container): void
    {
        $this->container = $container;
    }

    protected function getTemplatePath(TeamHolder $holder): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'organizer.info.cs.latte';
    }

    protected function getData(TeamHolder $holder): array
    {
        return MemberTransitionMail::getStaticData($holder);
    }

    /**
     * @throws BadTypeException
     */
    public function __invoke(...$args): void
    {
        /**
         * @var TeamHolder $holder
         */
        [$holder] = $args;
        $data = $this->getData($holder);
        $data['recipient'] = 'Fyziklání <fyziklani@fykos.cz>';
        $data = array_merge(
            $data,
            $this->mailTemplateFactory->renderWithParameters(
                $this->getTemplatePath($holder),
                [
                    'tests' => DataTestFactory::getTeamTests($this->container),
                    'holder' => $holder,
                ],
                Language::tryFrom(Language::CS)
            )
        );
        $this->emailMessageService->addMessageToSend($data);
    }

    protected function getPersons(TeamHolder $holder): array
    {
        return [];
    }
}
