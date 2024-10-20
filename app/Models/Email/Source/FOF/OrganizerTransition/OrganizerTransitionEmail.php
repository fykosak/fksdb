<?php

declare(strict_types=1);

namespace FKSDB\Models\Email\Source\FOF\OrganizerTransition;

use FKSDB\Components\DataTest\DataTestFactory;
use FKSDB\Models\Email\TransitionEmailSource;
use FKSDB\Models\ORM\Models\EmailMessageTopic;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use FKSDB\Models\ORM\Tests\Test;
use FKSDB\Models\Transitions\Holder\TeamHolder;
use FKSDB\Models\Transitions\Transition\Transition;
use FKSDB\Modules\Core\Language;

/**
 * @phpstan-extends TransitionEmailSource<TeamModel2,array{tests:Test<TeamModel2>[],model:TeamModel2}>
 */
final class OrganizerTransitionEmail extends TransitionEmailSource
{
    protected function getSource(array $params): array
    {
        /** @var TeamHolder $holder */
        $holder = $params['holder'];
        /** @phpstan-var  Transition<TeamHolder> $transition */
        $transition = $params['transition'];
        $transitionId = self::resolveLayoutName($transition);
        return [
            [
                'template' => [
                    'file' => __DIR__ . DIRECTORY_SEPARATOR . "organizer.$transitionId.cs.latte",
                    'data' => [
                        'tests' => DataTestFactory::getTeamTests($this->container),
                        'model' => $holder->getModel(),
                    ],
                ],
                'data' => [
                    'sender' => 'Fyziklání <fyziklani@fykos.cz>',
                    'recipient' => 'Fyziklání <fyziklani@fykos.cz>',
                    'topic' => EmailMessageTopic::from(EmailMessageTopic::Internal),
                    'lang' => Language::CS,
                ]
            ]
        ];
    }
}
