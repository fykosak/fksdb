<?php

declare(strict_types=1);

namespace FKSDB\Tests\PresentersTests\OrganizerModule;

// phpcs:disable
$container = require '../../Bootstrap.php';

// phpcs:enable
use FKSDB\Components\EntityForms\EventFormComponent;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Services\EventService;
use FKSDB\Models\ORM\Services\OrganizerService;
use Nette\Application\Responses\RedirectResponse;
use Tester\Assert;

class EventPresenterTest extends AbstractOrganizerPresenterTestCase
{

    private EventModel $event;

    protected function setUp(): void
    {
        parent::setUp();
        $this->loginUser();
        $this->container->getByType(OrganizerService::class)->storeModel(
            ['person_id' => $this->cartesianPerson->person_id, 'contest_id' => 1, 'since' => 1, 'order' => 1]
        );

        $this->event = $this->container->getByType(EventService::class)->storeModel([
            'event_type_id' => 1,
            'year' => 1,
            'event_year' => 1,
            'begin' => new \DateTime(),
            'end' => new \DateTime(),
            'name' => 'Dummy Event',
            'registration_begin' => new \DateTime(),
            'registration_end' => new \DateTime(),
        ]);
    }

    public function testList(): void
    {
        $request = $this->createGetRequest('list', []);
        $response = $this->fixture->run($request);
        $html = $this->assertPageDisplay($response);
        // Assert::contains('Dummy Event', $html);
        Assert::contains('FYKOSí Fyziklání', $html);
        Assert::contains('#' . $this->event->event_id, $html);
    }

    public function testCreate(): void
    {
        $init = $this->countEvents();
        $response = $this->createFormRequest('create', [
            EventFormComponent::CONT_EVENT => [
                'event_type_id' => '2',
                'year' => '1',
                'event_year' => '1',
                'begin' => (new \DateTime())->format('c'),
                'end' => (new \DateTime())->format('c'),
                'name' => 'Dummy Event',
                'registration_begin' => (new \DateTime())->format('c'),
                'registration_end' => (new \DateTime())->format('c'),
            ],
        ]);
        Assert::type(RedirectResponse::class, $response);
        $after = $this->countEvents();
        Assert::equal($init + 1, $after);
    }

    public function testCreateDuplicate(): void
    {
        $init = $this->countEvents();
        $response = $this->createFormRequest('create', [
            EventFormComponent::CONT_EVENT => [
                'event_type_id' => '1',
                'year' => '1',
                'event_year' => '1',
                'begin' => (new \DateTime())->format('c'),
                'end' => (new \DateTime())->format('c'),
                'name' => 'Dummy Event',
                'registration_begin' => (new \DateTime())->format('c'),
                'registration_end' => (new \DateTime())->format('c'),
            ],
        ]);

        $html = $this->assertPageDisplay($response);
        Assert::contains('alert-danger', $html);
        $after = $this->countEvents();
        Assert::equal($init, $after);
    }

    public function testEdit(): void
    {
        $response = $this->createFormRequest('edit', [
            EventFormComponent::CONT_EVENT => [
                'event_type_id' => '1',
                'year' => '1',
                'event_year' => '1',
                'begin' => (new \DateTime())->format('c'),
                'end' => (new \DateTime())->format('c'),
                'name' => 'Dummy Event edited',
                'registration_begin' => (new \DateTime())->format('c'),
                'registration_end' => (new \DateTime())->format('c'),
            ],
        ], [
            'id' => $this->event->event_id,
        ]);
        Assert::type(RedirectResponse::class, $response);
        /** @var EventModel $event */
        $event = $this->container
            ->getByType(EventService::class)
            ->findByPrimary($this->event->event_id);

        Assert::equal('Dummy Event edited', $event->name);
    }

    protected function getPresenterName(): string
    {
        return 'Organizer:Event';
    }

    private function countEvents(): int
    {
        return $this->container->getByType(EventService::class)->getTable()->count('*');
    }
}
// phpcs:disable
$testCase = new EventPresenterTest($container);
$testCase->run();
// phpcs:enable
