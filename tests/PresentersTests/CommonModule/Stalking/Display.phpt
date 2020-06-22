<?php

namespace FKSDB\Tests\PresentersTests\CommonModule\Stalking;

$container = require '../../../bootstrap.php';

use MockEnvironment\MockApplicationTrait;
use Nette\Application\Responses\TextResponse;
use Nette\Application\UI\ITemplate;
use Nette\DI\Container;
use Tester\Assert;

/**
 * Class Stalking
 * @package Persons
 */
class Display extends StalkingTestCase {
    use MockApplicationTrait;

    /**
     * Stalking constructor.
     * @param Container $container
     */
    public function __construct(Container $container) {
        parent::__construct($container);
        $this->setContainer($container);
    }

    public function testDisplay() {

        $request = $this->createRequest();

        $response = $this->fixture->run($request);
        Assert::type(TextResponse::class, $response);

        $source = $response->getSource();
        Assert::type(ITemplate::class, $source);
        $html = (string)$source;

        Assert::contains('Base info', $html); // contains headline
        Assert::contains('class="fa fa-mars"', $html); // check gender
        Assert::contains('cz.svg', $html); // phone number flag
        Assert::contains('sk.svg', $html); // phone number flag
        Assert::contains('+420 123 456 789', $html); // check phone number formating
        Assert::contains('href="mailto:tester&#64;example.com"', $html); // email
        Assert::contains('(111) Všeobecná zdravotní pojišťovna ČR', $html); // Health insurance mapping
        Assert::contains('data-react-id="person.detail.timeline"', $html); // timeline working?
    }

    protected function getUserRoleId(): int {
        return 1000;
    }
}

$testCase = new Display($container);
$testCase->run();
