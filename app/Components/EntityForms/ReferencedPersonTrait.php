<?php

declare(strict_types=1);

namespace FKSDB\Components\EntityForms;

use FKSDB\Components\Forms\Containers\SearchContainer\PersonSearchContainer;
use FKSDB\Components\Forms\Controls\ReferencedId;
use FKSDB\Components\Forms\Factories\ReferencedPerson\ReferencedPersonFactory;
use FKSDB\Models\ORM\Models\ContestYearModel;
use FKSDB\Models\Persons\ModifiabilityResolver;
use FKSDB\Models\Persons\VisibilityResolver;
use Nette\Forms\Form;

trait ReferencedPersonTrait
{
    private ReferencedPersonFactory $referencedPersonFactory;

    protected function createPersonId(
        ContestYearModel $contestYear,
        bool $allowClear,
        VisibilityResolver $visibilityResolver,
        ModifiabilityResolver $resolver
    ): ReferencedId {
        $referencedId = $this->referencedPersonFactory->createReferencedPerson(
            $this->getContext()->getParameters()[$contestYear->contest->getContestSymbol()]['adminTeacher'],
            $contestYear,
            PersonSearchContainer::SEARCH_ID,
            $allowClear,
            $resolver,
            $visibilityResolver
        );
        $referencedId->addRule(Form::FILLED, _('Person is required.'));
        $referencedId->getReferencedContainer()->setOption('label', _('Person'));
        $referencedId->getSearchContainer()->setOption('label', _('Person'));
        return $referencedId;
    }

    final public function injectPersonTrait(ReferencedPersonFactory $referencedPersonFactory): void
    {
        $this->referencedPersonFactory = $referencedPersonFactory;
    }
}
