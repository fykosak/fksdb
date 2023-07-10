<?php

declare(strict_types=1);

namespace FKSDB\Tests\ModelsTests;

// phpcs:disable
$container = require '../Bootstrap.php';

// phpcs:enable
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Services\OrgService;
use FKSDB\Models\WebService\WebServiceModel;
use Tester\Assert;

class WebServiceModelTest extends DatabaseTestCase
{
    private WebServiceModel $fixture;
    private PersonModel $person;

    protected function setUp(): void
    {
        parent::setUp();
        $this->fixture = $this->container->getService('webServiceModel');
        $this->person = $this->createPerson('Homer', 'Simpson', null, ['login' => 'homer', 'hash' => '123456']);

        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
    }

    public function testResults(): void
    {
        $this->container->getByType(OrgService::class)->storeModel(
            [
                'person_id' => $this->person->person_id,
                'contest_id' => 1,
                'since' => 1,
                'order' => 1,
            ]
        );
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

    public function testUnauthorized(): void
    {
        Assert::exception(function () {
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
            $this->fixture->GetResults((object)$resultsReq);
        }, \SoapFault::class);
    }
}

// phpcs:disable
$testCase = new WebServiceModelTest($container);
$testCase->run();
// phpcs:enable
