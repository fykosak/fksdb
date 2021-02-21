<?php

namespace FKSDB\Components\Charts;

use FKSDB\Components\Charts\Core\Chart;
use FKSDB\Components\React\ReactComponent;
use FKSDB\Models\ORM\Models\ModelPerson;
use FKSDB\Models\ORM\Services\ServicePerson;
use Nette\DI\Container;

/**
 * Class TotalPersonsChartControl
 * @author Michal Červeňák <miso@fykos.cz>
 */
class TotalPersonsChartComponent extends ReactComponent implements Chart {

    private ServicePerson $servicePerson;

    public function __construct(Container $container) {
        parent::__construct($container, 'chart.total-person');
    }

    final public function injectServicePerson(ServicePerson $servicePerson): void {
        $this->servicePerson = $servicePerson;
    }

    public function getData(): array {
        $query = $this->servicePerson->getTable()->order('created');
        $data = [];
        /** @var ModelPerson $person */
        foreach ($query as $person) {
            $data[] = [
                'created' => $person->created->format('c'),
                'gender' => $person->gender,
                'personId' => $person->person_id,
            ];
        }
        return $data;
    }

    public function getTitle(): string {
        return _('Total persons in FKSDB');
    }

    public function getControl(): self {
        return $this;
    }

    public function getDescription(): ?string {
        return _('Graf zobrazuje vývoj počtu osôb vo FKSDB a priradené person_id v daný čas.');
    }
}
