<?php

namespace FKSDB\Components\Grids\Deduplicate;

use FKSDB\Components\DatabaseReflection\ValuePrinters\PersonLink;
use FKSDB\Components\Grids\BaseGrid;
use FKSDB\ORM\Models\ModelPerson;
use FKSDB\ORM\Tables\TypedTableSelection;
use Nette\Utils\Html;
use NiftyGrid\DataSource\NDataSource;
use Persons\Deduplication\DuplicateFinder;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class PersonsGrid extends BaseGrid {

    /**
     * @var TypedTableSelection
     */
    private $trunkPersons;

    /**
     * @var array trunkId => ModelPerson
     */
    private $pairs;

    /**
     * PersonsGrid constructor.
     * @param TypedTableSelection $trunkPersons
     * @param $pairs
     */
    function __construct(TypedTableSelection $trunkPersons, $pairs) {
        parent::__construct();
        $this->trunkPersons = $trunkPersons;
        $this->pairs = $pairs;
    }

    /**
     * @param \AuthenticatedPresenter $presenter
     * @throws \NiftyGrid\DuplicateButtonException
     * @throws \NiftyGrid\DuplicateColumnException
     */
    protected function configure($presenter) {
        parent::configure($presenter);

        /***** data ****/

        $dataSource = new NDataSource($this->trunkPersons);
        $this->setDataSource($dataSource);

        /***** columns ****/

        $this->addColumn('display_name_a', _('Osoba A'))->setRenderer(function ($row) {

            return $this->renderPerson($row);
        })
            ->setSortable(false);
        $pairs = &$this->pairs;
        $this->addColumn('display_name_b', _('Osoba B'))->setRenderer(function ($row) use ($pairs) {
            return $this->renderPerson($pairs[$row->person_id][DuplicateFinder::IDX_PERSON]);
        })
            ->setSortable(false);
        $this->addColumn('score', _('Podobnost'))->setRenderer(function ($row) use ($pairs) {
            return sprintf("%0.2f", $pairs[$row->person_id][DuplicateFinder::IDX_SCORE]);
        })
            ->setSortable(false);

        /**** operations *****/

        $this->addButton("mergeAB", _('Sloučit A<-B'))
            ->setText(_('Sloučit A<-B'))
            ->setClass("btn btn-sm btn-primary")
            ->setLink(function ($row) use ($presenter, $pairs) {
                return $presenter->link("Person:merge", array(
                    'trunkId' => $row->person_id,
                    'mergedId' => $pairs[$row->person_id][DuplicateFinder::IDX_PERSON]->person_id,
                ));
            })
            ->setShow(function ($row) use ($presenter, $pairs) {
                return $presenter->authorized("Person:merge", array(
                    'trunkId' => $row->person_id,
                    'mergedId' => $pairs[$row->person_id][DuplicateFinder::IDX_PERSON]->person_id,
                ));
            });
        $this->addButton("mergeBA", _('Sloučit B<-A'))
            ->setText(_('Sloučit B<-A'))
            ->setLink(function ($row) use ($presenter, $pairs) {
                return $presenter->link("Person:merge", array(
                    'trunkId' => $pairs[$row->person_id][DuplicateFinder::IDX_PERSON]->person_id,
                    'mergedId' => $row->person_id,
                ));
            })
            ->setShow(function ($row) use ($presenter, $pairs) {
                return $presenter->authorized("Person:merge", array(
                    'trunkId' => $pairs[$row->person_id][DuplicateFinder::IDX_PERSON]->person_id,
                    'mergedId' => $row->person_id,
                ));
            });
        $this->addButton("dontMerge", _('Nejde o duplicitu'))
            ->setText(_('Nejde o duplicitu'))
            ->setClass("btn btn-sm btn-primary")
            ->setLink(function ($row) use ($presenter, $pairs) {
                return $presenter->link("Person:dontMerge", array(
                    'trunkId' => $pairs[$row->person_id][DuplicateFinder::IDX_PERSON]->person_id,
                    'mergedId' => $row->person_id,
                ));
            })
            ->setShow(function ($row) use ($presenter, $pairs) {
                return $presenter->authorized("Person:dontMerge", array(
                    'trunkId' => $pairs[$row->person_id][DuplicateFinder::IDX_PERSON]->person_id,
                    'mergedId' => $row->person_id,
                ));
            });
    }

    /**
     * @param \FKSDB\ORM\Models\ModelPerson $person
     * @return Html
     */
    private function renderPerson(ModelPerson $person) {
        return (new PersonLink($this->getPresenter()))($person);
    }
}
