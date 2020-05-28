<?php

namespace FKSDB\Events\Model;

use Nette\Application\Request;
use Nette\Application\Responses\TextResponse;
use Nette\Templating\ITemplate;
use Tester\Assert;
use Tester\DomQuery;

$container = require '../../bootstrap.php';

class ResourceAvailabilityTest extends ResourceAvailabilityTestCase {

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

        $html = (string) $source;
        $dom = DomQuery::fromHtml($html);
        Assert::true((bool) $dom->xpath('//input[@name="participant[accomodation]"]'));
        Assert::false((bool) $dom->xpath('//input[@name="participant[accomodation]"][@disabled="disabled"]'));
    }



    public function getCapacity() {
        return 3;
    }

}

$testCase = new ResourceAvailabilityTest($container);
$testCase->run();
