<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\Deduplicate;

use FKSDB\Components\Grids\Components\BaseGrid;
use FKSDB\Components\Grids\Components\Button\Button;
use FKSDB\Components\Grids\Components\Renderer\RendererItem;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\Persons\Deduplication\DuplicateFinder;
use Fykosak\NetteORM\TypedSelection;
use Fykosak\Utils\UI\Title;
use Nette\DI\Container;

/**
 * @phpstan-extends BaseGrid<PersonModel>
 */
class PersonsGrid extends BaseGrid
{
    /** @phpstan-var PersonModel[] trunkId => ModelPerson */
    private array $pairs;
    /** @phpstan-var TypedSelection<PersonModel> $data */
    private TypedSelection $data;

    /**
     * @phpstan-param TypedSelection<PersonModel> $trunkPersons
     * @phpstan-param PersonModel[] $pairs
     */
    public function __construct(TypedSelection $trunkPersons, array $pairs, Container $container)
    {
        parent::__construct($container);
        $this->data = $trunkPersons;
        $this->pairs = $pairs;
    }

    /**
     * @phpstan-return TypedSelection<PersonModel>
     */
    protected function getModels(): TypedSelection
    {
        return $this->data;
    }

    protected function configure(): void
    {
        $this->addColumn(
            new RendererItem(
                $this->container,
                fn(PersonModel $row): string => $this->renderPerson($row),
                new Title(null, _('Person A'))
            ),
            'display_name_a'
        );
        $this->addColumn(
            new RendererItem(
                $this->container,
                fn(PersonModel $row): string => $this->renderPerson(
                    $this->pairs[$row->person_id][DuplicateFinder::IDX_PERSON]
                ),
                new Title(null, _('Person B'))
            ),
            'display_name_b'
        );
        $this->addColumn(
            new RendererItem(
                $this->container,
                fn(PersonModel $row): string => sprintf(
                    '%0.2f',
                    $this->pairs[$row->person_id][DuplicateFinder::IDX_SCORE]
                ),
                new Title(null, _('Score'))
            ),
            'score'
        );
        $this->addButton(
            new Button(
                $this->container,
                $this->getPresenter(),
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
        $this->addButton(
            new Button(
                $this->container,
                $this->getPresenter(),
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
        $this->addButton(
            new Button(
                $this->container,
                $this->getPresenter(),
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
