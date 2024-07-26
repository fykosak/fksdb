<?php

declare(strict_types=1);

namespace FKSDB\Models\Email\Source\FOF\OrganizerInfo;

use FKSDB\Components\DataTest\DataTestFactory;
use FKSDB\Models\Email\EmailSource;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use FKSDB\Models\ORM\Tests\Test;
use FKSDB\Models\Transitions\Holder\TeamHolder;
use FKSDB\Modules\Core\Language;

/**
 * @phpstan-extends EmailSource<array{tests:Test<TeamModel2>[],model:TeamModel2},array{holder:TeamHolder}>
 */
class OrganizerInfoEmail extends EmailSource
{
    protected function getSource(array $params): array
    {
        $holder = $params['holder'];
        return [
            [
                'template' => [
                    'file' => __DIR__ . DIRECTORY_SEPARATOR . 'layout.latte',
                    'data' => [
                        'tests' => DataTestFactory::getTeamTests($this->container),
                        'model' => $holder->getModel(),
                    ],
                ],
                'lang' => Language::from(Language::CS),
                'data' => [
                    'sender' => 'Fyziklání <fyziklani@fykos.cz>',
                    'recipient' => 'Fyziklání <fyziklani@fykos.cz>',
                ]
            ]
        ];
    }
}
