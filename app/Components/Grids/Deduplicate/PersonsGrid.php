<?php

namespace FKSDB\Components\Grids\Deduplicate;

use FKSDB\Components\DatabaseReflection\AbstractRow;
use FKSDB\Components\Grids\BaseGrid;
use FKSDB\Exceptions\BadTypeException;
use FKSDB\ORM\Models\ModelPerson;
use FKSDB\ORM\Tables\TypedTableSelection;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Presenter;
use Nette\DI\Container;
use Nette\Utils\Html;
use NiftyGrid\DataSource\IDataSource;
use NiftyGrid\DataSource\NDataSource;
use NiftyGrid\DuplicateButtonException;
use NiftyGrid\DuplicateColumnException;
use Persons\Deduplication\DuplicateFinder;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class PersonsGrid extends BaseGrid {

    private TypedTableSelection $trunkPersons;

    /**
     * @var ModelPerson[] trunkId => ModelPerson
     */
    private array $pairs;

    private AbstractRow $personRowFactory;

    /**
     * PersonsGrid constructor.
     * @param TypedTableSelection $trunkPersons
     * @param array $pairs
     * @param Container $container
     * @throws BadTypeException
     */
    public function __construct(TypedTableSelection $trunkPersons, array $pairs, Container $container) {
        parent::__construct($container);
        $this->trunkPersons = $trunkPersons;
        $this->pairs = $pairs;
        $this->personRowFactory = $this->tableReflectionFactory->loadRowFactory('person.full_name');
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

        $this->addColumn('display_name_a', _('Osoba A'))->setRenderer(function (ModelPerson $row) {
            return $this->renderPerson($row);
        })
            ->setSortable(false);
        $pairs = &$this->pairs;
        $this->addColumn('display_name_b', _('Osoba B'))->setRenderer(function (ModelPerson $row) use ($pairs) {
            return $this->renderPerson($pairs[$row->person_id][DuplicateFinder::IDX_PERSON]);
        })
            ->setSortable(false);
        $this->addColumn('score', _('Podobnost'))->setRenderer(function (ModelPerson $row) use ($pairs) {
            return sprintf("%0.2f", $pairs[$row->person_id][DuplicateFinder::IDX_SCORE]);
        })
            ->setSortable(false);

        /**** operations *****/

        $this->addButton("mergeAB", _('Sloučit A<-B'))
            ->setText(_('Sloučit A<-B'))
            ->setClass("btn btn-sm btn-primary")
            ->setLink(function (ModelPerson $row) use ($presenter, $pairs) {
                return $presenter->link("Person:merge", [
                    'trunkId' => $row->person_id,
                    'mergedId' => $pairs[$row->person_id][DuplicateFinder::IDX_PERSON]->person_id,
                ]);
            })
            ->setShow(function (ModelPerson $row) use ($presenter, $pairs) {
                return $presenter->authorized("Person:merge", [
                    'trunkId' => $row->person_id,
                    'mergedId' => $pairs[$row->person_id][DuplicateFinder::IDX_PERSON]->person_id,
                ]);
            });
        $this->addButton("mergeBA", _('Sloučit B<-A'))
            ->setText(_('Sloučit B<-A'))
            ->setLink(function (ModelPerson $row) use ($presenter, $pairs) {
                return $presenter->link("Person:merge", [
                    'trunkId' => $pairs[$row->person_id][DuplicateFinder::IDX_PERSON]->person_id,
                    'mergedId' => $row->person_id,
                ]);
            })
            ->setShow(function (ModelPerson $row) use ($presenter, $pairs) {
                return $presenter->authorized("Person:merge", [
                    'trunkId' => $pairs[$row->person_id][DuplicateFinder::IDX_PERSON]->person_id,
                    'mergedId' => $row->person_id,
                ]);
            });
        $this->addButton("dontMerge", _('Nejde o duplicitu'))
            ->setText(_('Nejde o duplicitu'))
            ->setClass("btn btn-sm btn-primary")
            ->setLink(function (ModelPerson $row) use ($presenter, $pairs) {
                return $presenter->link("Person:dontMerge", [
                    'trunkId' => $pairs[$row->person_id][DuplicateFinder::IDX_PERSON]->person_id,
                    'mergedId' => $row->person_id,
                ]);
            })
            ->setShow(function (ModelPerson $row) use ($presenter, $pairs) {
                return $presenter->authorized("Person:dontMerge", [
                    'trunkId' => $pairs[$row->person_id][DuplicateFinder::IDX_PERSON]->person_id,
                    'mergedId' => $row->person_id,
                ]);
            });
    }

    /**
     * @param ModelPerson $person
     * @return Html
     * @throws BadRequestException
     */
    private function renderPerson(ModelPerson $person): Html {
        return $this->personRowFactory->renderValue($person, AbstractRow::PERMISSION_ALLOW_FULL);
    }
}
