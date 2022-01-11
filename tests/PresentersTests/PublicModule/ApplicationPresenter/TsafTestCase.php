<?php

declare(strict_types=1);

namespace FKSDB\Tests\PresentersTests\PublicModule\ApplicationPresenter;

use FKSDB\Models\ORM\Models\ModelEvent;

abstract class TsafTestCase extends DsefTestCase
{
    protected ModelEvent $dsefEvent;
    protected ModelEvent $tsafEvent;

    protected function getEvent(): ModelEvent
    {
        return $this->event;
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->dsefEvent = $this->event;

        $this->tsafEvent = $this->createEvent([
            'event_type_id' => 7,
            'event_year' => 7,
            'registration_end' => new \DateTime(date('c', time() + 1000)),
            'parameters' => <<<EOT
capacity: 5
EOT
            ,
        ]);
    }
}
