<?php

namespace FKSDB\Components\Forms\Factories;

use FKSDB\Components\Forms\Containers\ModelContainer;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\Models\ModelContest;
use FKSDB\YearCalculator;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class OrgFactory extends SingleReflectionFactory {

    /**
     * @var YearCalculator
     */
    private $yearCalculator;

    /**
     * OrgFactory constructor.
     * @param YearCalculator $yearCalculator
     * @param TableReflectionFactory $tableReflectionFactory
     */
    public function __construct(YearCalculator $yearCalculator, TableReflectionFactory $tableReflectionFactory) {
        parent::__construct($tableReflectionFactory);
        $this->yearCalculator = $yearCalculator;
    }

    /**
     * @param ModelContest $contest
     * @return ModelContainer
     * @throws \Exception
     */
    public function createOrg(ModelContest $contest): ModelContainer {
        $container = new ModelContainer();
        $min = $this->yearCalculator->getFirstYear($contest);
        $max = $this->yearCalculator->getLastYear($contest);

        foreach (['since', 'until'] as $field) {
            $control = $this->createField($field, $min, $max);
            $container->addComponent($control, $field);
        }

        foreach (['role', 'tex_signature', 'domain_alias', 'order', 'contribution'] as $field) {
            $control = $this->createField($field);
            $container->addComponent($control, $field);
        }
        return $container;
    }

    protected function getTableName(): string {
        return DbNames::TAB_ORG;
    }
}
