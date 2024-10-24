<?php

declare(strict_types=1);

namespace FKSDB\Components\Charts;

use FKSDB\Components\Charts\Core\Chart;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Services\PersonService;
use Fykosak\NetteFrontendComponent\Components\FrontEndComponent;
use Fykosak\Utils\UI\Title;
use Nette\DI\Container;

class TotalPersonsChart extends FrontEndComponent implements Chart
{
    private PersonService $personService;

    public function __construct(Container $container)
    {
        parent::__construct($container, 'chart.total-person');
    }

    final public function injectServicePerson(PersonService $personService): void
    {
        $this->personService = $personService;
    }

    /**
     * @phpstan-return array<int,array{created:string,gender:string,personId:int}>
     */
    public function getData(): array
    {
        $query = $this->personService->getTable()->order('created');
        $data = [];
        /** @var PersonModel $person */
        foreach ($query as $person) {
            $data[] = [
                'created' => $person->created->format('c'),
                'gender' => $person->gender->value,
                'personId' => $person->person_id,
            ];
        }
        return $data;
    }

    public function getTitle(): Title
    {
        return new Title(null, _('Total persons in FKSDB'), 'fas fa-chart-line');
    }

    public function getDescription(): string
    {
        return _('Graph shows the progress in the number of people in FKSDB and the number of assigned person_ids.');
    }
}
