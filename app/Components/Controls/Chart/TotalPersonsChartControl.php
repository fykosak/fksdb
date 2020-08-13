<?php

namespace FKSDB\Components\Controls\Chart;

use FKSDB\Components\React\ReactComponent;
use FKSDB\Components\React\ReactComponent2;
use FKSDB\ORM\Models\ModelPerson;
use FKSDB\ORM\Services\ServicePerson;
use Nette\Application\UI\Control;
use Nette\DI\Container;

/**
 * Class TotalPersonsChartControl
 * @author Michal Červeňák <miso@fykos.cz>
 */
class TotalPersonsChartControl extends ReactComponent2 implements IChart {

    private ServicePerson $servicePerson;

    /**
     * TotalPersonsChartControl constructor.
     * @param Container $container
     */
    public function __construct(Container $container) {
        parent::__construct($container, 'chart.total-person');
    }

    public function injectServicePerson(ServicePerson $servicePerson): void {
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

    public function getControl(): Control {
        return $this;
    }

    public function getDescription(): ?string {
        return _('Graf zobrazuje vývoj počtu osôb vo FKSDB a priradené person_id v daný čas.');
    }
}
