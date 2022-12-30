<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\Deduplicate;

use FKSDB\Components\Grids\BaseGrid;
use FKSDB\Components\Grids\ListComponent\Button\PresenterButton;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\Persons\Deduplication\DuplicateFinder;
use Fykosak\NetteORM\TypedSelection;
use Fykosak\Utils\UI\Title;
use Nette\Application\UI\Presenter;
use Nette\DI\Container;

class PersonsGrid extends BaseGrid
{
    /** @var PersonModel[] trunkId => ModelPerson */
    private array $pairs;

    public function __construct(TypedSelection $trunkPersons, array $pairs, Container $container)
    {
        parent::__construct($container);
        $this->data = $trunkPersons;
        $this->pairs = $pairs;
    }

    protected function configure(Presenter $presenter): void
    {
        $this->addColumn(
            'display_name_a',
            new Title(null, _('Person A')),
            fn(PersonModel $row): string => $this->renderPerson($row)
        );
        $this->addColumn(
            'display_name_b',
            new Title(null, _('Person B')),
            fn(PersonModel $row): string => $this->renderPerson(
                $this->pairs[$row->person_id][DuplicateFinder::IDX_PERSON]
            )
        );
        $this->addColumn('score', new Title(null, _('Similarity')), fn(PersonModel $row): string => sprintf(
            '%0.2f',
            $this->pairs[$row->person_id][DuplicateFinder::IDX_SCORE]
        ));
        $this->getColumnsContainer()->getButtonContainer()->addComponent(
            new PresenterButton(
                $this->container,
                new Title(null, _('Merge A<-B')),
                fn(PersonModel $row): array => [
                    'Deduplicate:merge',
                    [
                        'trunkId' => $row->person_id,
                        'mergedId' => $this->pairs[$row->person_id][DuplicateFinder::IDX_PERSON]->person_id,
                    ],
                ],
                'btn btn-sm btn-outline-primary'
            ),
            'mergeAB'
        );
        $this->getColumnsContainer()->getButtonContainer()->addComponent(
            new PresenterButton(
                $this->container,
                new Title(null, _('Merge B<-A')),
                fn(PersonModel $row): array => [
                    'Deduplicate:merge',
                    [
                        'trunkId' => $this->pairs[$row->person_id][DuplicateFinder::IDX_PERSON]->person_id,
                        'mergedId' => $row->person_id,
                    ],
                ]
            ),
            'mergeBA'
        );
        $this->getColumnsContainer()->getButtonContainer()->addComponent(
            new PresenterButton(
                $this->container,
                new Title(null, _('It\'s not a duplicity')),
                fn(PersonModel $row): array => [
                    'Deduplicate:dontMerge',
                    [
                        'trunkId' => $this->pairs[$row->person_id][DuplicateFinder::IDX_PERSON]->person_id,
                        'mergedId' => $row->person_id,
                    ],
                ],
                'btn btn-sm btn-outline-primary'
            ),
            'dontMerge'
        );
    }

    private function renderPerson(PersonModel $person): string
    {
        return $person->getFullName();
        // return (new PersonLink($this->getPresenter()))($person);
    }
}
