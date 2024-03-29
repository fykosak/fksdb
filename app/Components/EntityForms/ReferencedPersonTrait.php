<?php

declare(strict_types=1);

namespace FKSDB\Components\EntityForms;

use FKSDB\Components\Forms\Containers\Models\ReferencedPersonContainer;
use FKSDB\Components\Forms\Containers\SearchContainer\PersonSearchContainer;
use FKSDB\Components\Forms\Controls\ReferencedId;
use FKSDB\Components\Forms\Factories\ReferencedPerson\ReferencedPersonFactory;
use FKSDB\Models\ORM\Models\ContestYearModel;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\Persons\Resolvers\Resolver;
use Nette\Forms\Form;

/**
 * @phpstan-import-type EvaluatedFieldsDefinition from ReferencedPersonContainer
 */
trait ReferencedPersonTrait
{
    private ReferencedPersonFactory $referencedPersonFactory;

    /**
     * @phpstan-return ReferencedId<PersonModel>
     * @phpstan-param EvaluatedFieldsDefinition $fieldDefinition
     */
    protected function createPersonId(
        ContestYearModel $contestYear,
        bool $allowClear,
        Resolver $resolver,
        array $fieldDefinition
    ): ReferencedId {
        $referencedId = $this->referencedPersonFactory->createReferencedPerson(
            $fieldDefinition,
            $contestYear,
            PersonSearchContainer::SEARCH_ID,
            $allowClear,
            $resolver
        );
        $referencedId->addRule(Form::FILLED, _('Person is required.'));
        $referencedId->referencedContainer->setOption('label', _('Person'));
        $referencedId->searchContainer->setOption('label', _('Person'));
        return $referencedId;
    }

    final public function injectPersonTrait(ReferencedPersonFactory $referencedPersonFactory): void
    {
        $this->referencedPersonFactory = $referencedPersonFactory;
    }
}
