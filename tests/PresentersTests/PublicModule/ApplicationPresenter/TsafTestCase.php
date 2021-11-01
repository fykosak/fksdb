<?php

declare(strict_types=1);

namespace FKSDB\Tests\PresentersTests\PublicModule\ApplicationPresenter;

use Nette\Utils\DateTime;

abstract class TsafTestCase extends DsefTestCase
{

    protected int $dsefEventId;

    protected int $tsafEventId;

    protected function getEventId(): int
    {
        return $this->eventId;
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->dsefEventId = $this->eventId;

        $this->tsafEventId = $this->createEvent([
            'event_type_id' => 7,
            'event_year' => 7,
            'registration_end' => new DateTime(date('c', time() + 1000)),
            'parameters' => <<<EOT
capacity: 5
EOT
            ,
        ]);
    }
}
