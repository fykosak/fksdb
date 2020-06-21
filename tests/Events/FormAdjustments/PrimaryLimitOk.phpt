<?php

namespace FKSDB\Tests\Events\FormAdjustments;

use Nette\Application\Request;
use Nette\Application\Responses\TextResponse;
use Nette\Application\UI\ITemplate;
use Tester\Assert;
use Tester\DomQuery;

$container = require '../../bootstrap.php';

class PrimaryLimitOk extends ResourceAvailabilityTestCase {

    public function testDisplay() {
        $request = new Request('Public:Application', 'GET', [
            'action' => 'default',
            'lang' => 'cs',
            'contestId' => 1,
            'year' => 1,
            'eventId' => $this->eventId,
        ]);

        $response = $this->fixture->run($request);
        Assert::type(TextResponse::class, $response);

        $source = $response->getSource();
        Assert::type(ITemplate::class, $source);

        $html = (string)$source;
        $dom = DomQuery::fromHtml($html);
        Assert::true((bool)$dom->xpath('//input[@name="participant[accomodation]"]'));
        Assert::false((bool)$dom->xpath('//input[@name="participant[accomodation]"][@disabled="disabled"]'));
    }

    protected function getCapacity(): int {
        return 3;
    }

}

$testCase = new PrimaryLimitOk($container);
$testCase->run();
