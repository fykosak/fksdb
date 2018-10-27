<?php

namespace FKSDB\Components\Forms\Factories;

use FKSDB\Components\Forms\Containers\ModelContainer;
use FKSDB\Components\Forms\Factories\Org\ContributionField;
use FKSDB\Components\Forms\Factories\Org\DomainAliasField;
use FKSDB\Components\Forms\Factories\Org\OrderField;
use FKSDB\Components\Forms\Factories\Org\RoleField;
use FKSDB\Components\Forms\Factories\Org\SinceField;
use FKSDB\Components\Forms\Factories\Org\TexSignatureField;
use FKSDB\Components\Forms\Factories\Org\UntilField;
use FKSDB\ORM\ModelContest;
use http\Exception\InvalidArgumentException;
use Nette\Forms\Form;
use YearCalculator;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class OrgFactory {

    /**
     * @var YearCalculator
     */
    private $yearCalculator;

    function __construct(YearCalculator $yearCalculator) {
        $this->yearCalculator = $yearCalculator;
    }

    public function createOrg(ModelContest $contest): ModelContainer {
        $container = new ModelContainer();

        $container->addComponent(new SinceField($this->yearCalculator, $contest), 'since');

        $untilField = new UntilField($this->yearCalculator, $contest);
        $untilField->addCondition(Form::FILLED)
            ->addRule(function ($until, $since) {
                return $since->value <= $until->value;
            }, _('Konec nesmí být dříve než začátek'), $container['since']); // TODO to validation
        $container->addComponent($untilField, 'until');

        $container->addComponent(new RoleField(), 'role');

        $container->addComponent(new TexSignatureField(), 'tex_signature');

        $container->addComponent(new DomainAliasField(), 'domain_alias');

        $container->addComponent(new OrderField(), 'order');

        $container->addComponent(new ContributionField(), 'contribution');

        return $container;
    }

    public function createField(string $fieldName, ModelContest $contest) {
        switch ($fieldName) {
            case 'since':
                return new SinceField($this->yearCalculator, $contest);
            case 'until':
                return new UntilField($this->yearCalculator, $contest);
            case 'role':
                return new RoleField();
            case 'tex_signature':
                return new TexSignatureField();
            case 'domain_alias':
                return new DomainAliasField();
            case 'order':
                return new OrderField();
            case 'contribution':
                return new ContributionField();
            default:
                throw new InvalidArgumentException('Field ' . $fieldName . ' not exists');
        }
    }
}
