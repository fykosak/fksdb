<?php

namespace FKSDB\Tests\PresentersTests\OrgModule;

$container = require '../../bootstrap.php';

use FKSDB\Components\Controls\Entity\SchoolFormComponent;
use FKSDB\ORM\DbNames;
use FKSDB\Tests\PresentersTests\EntityPresenterTestCase;
use Nette\Application\Responses\RedirectResponse;
use Nette\Application\Responses\TextResponse;
use Tester\Assert;

/**
 * Class EventPresenterTest
 * @author Michal Červeňák <miso@fykos.cz>
 */
class SchoolPresenterTest extends EntityPresenterTestCase {

    private int $schoolId;

    protected function setUp(): void {
        parent::setUp();
        $this->loginUser();
        $addressId = $this->insert(DbNames::TAB_ADDRESS, [
            'first_row' => 'PU',
            'second_row' => 'PU',
            'target' => 'PU',
            'city' => 'PU',
            'postal_code' => '02001',
            'region_id' => '1',
        ]);
        $this->schoolId = $this->insert(DbNames::TAB_SCHOOL, [
            'address_id' => $addressId,
            'name' => 'Test school',
            'name_abbrev' => 'T school',
        ]);
    }

    public function testList(): void {
        $request = $this->createGetRequest('list', []);
        $response = $this->fixture->run($request);
        $html = $this->assertPageDisplay($response);
        Assert::contains('Test school', $html);
        Assert::contains('PU', $html);
    }


    public function testCreate(): void {
        $init = $this->countSchools();
        $response = $this->createFormRequest('create', [
                SchoolFormComponent::CONT_ADDRESS => [
                    'first_row' => 'PU',
                    'second_row' => 'PU',
                    'target' => 'PU',
                    'city' => 'PU',
                    'postal_code' => '02001',
                    'region_id' => '1',
                ],
                SchoolFormComponent::CONT_SCHOOL => [
                    'name' => 'Test school',
                    'name_abbrev' => 'T school',
                ],
            ]
        );
        Assert::type(RedirectResponse::class, $response);
        $after = $this->countSchools();
        Assert::equal($init + 1, $after);
    }


    public function testEdit(): void {
        $init = $this->countSchools();
        $response = $this->createFormRequest('edit',
            [
                SchoolFormComponent::CONT_ADDRESS => [
                    'first_row' => 'PU',
                    'second_row' => 'PU',
                    'target' => 'PU',
                    'city' => 'PU edited',
                    'postal_code' => '02001',
                    'region_id' => '1',
                ],
                SchoolFormComponent::CONT_SCHOOL => [
                    'name' => 'Test school edited',
                    'name_abbrev' => 'T school',
                ],
            ],
            [
                'id' => $this->schoolId,
            ]);
        if ($response instanceof TextResponse) {
            file_put_contents('t.html', (string)$response->getSource());
        }
        Assert::type(RedirectResponse::class, $response);
        $after = $this->countSchools();
        Assert::equal($init, $after);

        $school = $this->connection->query('SELECT * FROM school where school_id=?', $this->schoolId)->fetch();
        Assert::equal('Test school edited', $school->name);
        $school = $this->connection->query('SELECT * FROM address where address_id=?', $school->address_id)->fetch();
        Assert::equal('PU edited', $school->city);

    }


    protected function getPresenterName(): string {
        return 'Common:School';
    }

    protected function tearDown(): void {
        $this->connection->query('DELETE FROM school');
        $this->connection->query('DELETE FROM address');
        parent::tearDown();
    }

    private function countSchools(): int {
        return $this->connection->query('SELECT * FROM school')->getRowCount();
    }
}

$testCase = new SchoolPresenterTest($container);
$testCase->run();
