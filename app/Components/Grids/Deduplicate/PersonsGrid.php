<?php

namespace FKSDB\Components\Grids\Deduplicate;

use FKSDB\Components\Grids\BaseGrid;
use FKSDB\ORM\Models\ModelPerson;
use FKSDB\ORM\Tables\TypedTableSelection;
use Nette\Application\UI\Presenter;
use Nette\DI\Container;
use NiftyGrid\DataSource\IDataSource;
use NiftyGrid\DataSource\NDataSource;
use NiftyGrid\DuplicateButtonException;
use NiftyGrid\DuplicateColumnException;
use FKSDB\Persons\Deduplication\DuplicateFinder;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class PersonsGrid extends BaseGrid {

    private TypedTableSelection $trunkPersons;

    /** @var ModelPerson[] trunkId => ModelPerson */
    private array $pairs;

    /**
     * PersonsGrid constructor.
     * @param TypedTableSelection $trunkPersons
     * @param array $pairs
     * @param Container $container
     */
    public function __construct(TypedTableSelection $trunkPersons, array $pairs, Container $container) {
        parent::__construct($container);
        $this->trunkPersons = $trunkPersons;
        $this->pairs = $pairs;
    }

    protected function getData(): IDataSource {
        return new NDataSource($this->trunkPersons);
    }

    /**
     * @param Presenter $presenter
     * @throws DuplicateButtonException
     * @throws DuplicateColumnException
     */
    protected function configure(Presenter $presenter): void {
        parent::configure($presenter);

        /***** columns ****/

        $this->addColumn('display_name_a', _('Person A'))->setRenderer(function (ModelPerson $row): string {
            return $this->renderPerson($row);
        })
            ->setSortable(false);
        $this->addColumn('display_name_b', _('Person B'))->setRenderer(function (ModelPerson $row): string {
            return $this->renderPerson($this->pairs[$row->person_id][DuplicateFinder::IDX_PERSON]);
        })
            ->setSortable(false);
        $this->addColumn('score', _('Podobnost'))->setRenderer(function (ModelPerson $row): string {
            return sprintf('%0.2f', $this->pairs[$row->person_id][DuplicateFinder::IDX_SCORE]);
        })
            ->setSortable(false);

        /**** operations *****/

        $this->addButton('mergeAB', _('Sloučit A<-B'))
            ->setText(_('Sloučit A<-B'))
            ->setClass('btn btn-sm btn-primary')
            ->setLink(function (ModelPerson $row): string {
                return $this->getPresenter()->link('Person:merge', [
                    'trunkId' => $row->person_id,
                    'mergedId' => $this->pairs[$row->person_id][DuplicateFinder::IDX_PERSON]->person_id,
                ]);
            })
            ->setShow(function (ModelPerson $row): bool {
                return $this->getPresenter()->authorized('Person:merge', [
                    'trunkId' => $row->person_id,
                    'mergedId' => $this->pairs[$row->person_id][DuplicateFinder::IDX_PERSON]->person_id,
                ]);
            });
        $this->addButton('mergeBA', _('Sloučit B<-A'))
            ->setText(_('Sloučit B<-A'))
            ->setLink(function (ModelPerson $row): string {
                return $this->getPresenter()->link('Person:merge', [
                    'trunkId' => $this->pairs[$row->person_id][DuplicateFinder::IDX_PERSON]->person_id,
                    'mergedId' => $row->person_id,
                ]);
            })
            ->setShow(function (ModelPerson $row): bool {
                return $this->getPresenter()->authorized('Person:merge', [
                    'trunkId' => $this->pairs[$row->person_id][DuplicateFinder::IDX_PERSON]->person_id,
                    'mergedId' => $row->person_id,
                ]);
            });
        $this->addButton('dontMerge', _('Nejde o duplicitu'))
            ->setText(_('Nejde o duplicitu'))
            ->setClass('btn btn-sm btn-primary')
            ->setLink(function (ModelPerson $row): string {
                return $this->getPresenter()->link('Person:dontMerge', [
                    'trunkId' => $this->pairs[$row->person_id][DuplicateFinder::IDX_PERSON]->person_id,
                    'mergedId' => $row->person_id,
                ]);
            })
            ->setShow(function (ModelPerson $row): bool {
                return $this->getPresenter()->authorized('Person:dontMerge', [
                    'trunkId' => $this->pairs[$row->person_id][DuplicateFinder::IDX_PERSON]->person_id,
                    'mergedId' => $row->person_id,
                ]);
            });
    }

    /**
     * @param ModelPerson $person
     * @return string
     */
    private function renderPerson(ModelPerson $person) {
        return $person->getFullName();
        // return (new PersonLink($this->getPresenter()))($person);
    }
}
