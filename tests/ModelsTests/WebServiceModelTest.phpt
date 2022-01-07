<?php

declare(strict_types=1);

namespace FKSDB\Tests\ModelsTests;

$container = require '../Bootstrap.php';

use FKSDB\Models\WebService\WebServiceModel;
use Tester\Assert;

class WebServiceModelTest extends DatabaseTestCase
{
    private WebServiceModel $fixture;

    protected function setUp(): void
    {
        parent::setUp();
        $this->fixture = $this->getContainer()->getService('webServiceModel');
        $this->createPerson('Homer', 'Simpson', null, ['login' => 'homer', 'hash' => '123456']);
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
    }

    public function testResults(): void
    {
        $header = [
            'username' => 'homer',
            'password' => '123456',
        ];

        $this->fixture->authenticationCredentials((object)$header);

        $resultsReq = [
            'contest' => 'fykos',
            'year' => 1,
            'brojure' => '1 2 3 4 5 6',
        ];
        $result = $this->fixture->GetResults((object)$resultsReq);

        Assert::type(\SoapVar::class, $result);
    }
}

$testCase = new WebServiceModelTest($container);
$testCase->run();
