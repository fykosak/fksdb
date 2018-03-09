<?php

namespace FKSDB\Components\Grids;

use Nette\Database\Table\Selection;
use Nette\Utils\Html;
use ServiceContestant;
use ServiceOrg;
use SQL\SearchableDataSource;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class TeachersGrid extends BaseGrid {

    /**
     * @var \ServiceTeacher
     */
    private $serviceTeacher;

    function __construct(\ServiceTeacher $serviceTeacher) {
        parent::__construct();

        $this->serviceTeacher = $serviceTeacher;
    }

    protected function configure($presenter) {
        parent::configure($presenter);

        //
        // data
        //
        $orgs = $this->serviceTeacher->getTable()->select('teacher.*, person.family_name AS display_name');

        $dataSource = new SearchableDataSource($orgs);
        $dataSource->setFilterCallback(function (Selection $table, $value) {
            $tokens = preg_split('/\s+/', $value);
            foreach ($tokens as $token) {
                $table->where('CONCAT(person.family_name, person.other_name, IFNULL(org.role,\'\'), IFNULL(org.contribution,\'\'))
                            LIKE CONCAT(\'%\', ? , \'%\')', $token);
            }
        });
        $this->setDataSource($dataSource);
        $this->setDefaultOrder('since DESC');

        //
        // columns
        //
        $this->addColumn('display_name', _('Jméno'))->setRenderer(function (\ModelTeacher $row) {
            $person = $row->getPerson();
            return $person->getFullname();
        });
        $this->addColumn('since', _('Since'));
        $this->addColumn('until', _('Until'))->setRenderer(function (\ModelTeacher $row) {
            if ($row->until === null) {
                return Html::el('span')->addAttributes(['class' => 'badge badge-success'])->add(_('Still teaches'));
            }
            return $row->until;
        });
        $this->addColumn('school_id', _('School'))->setRenderer(function (\ModelTeacher $row) {
            return $row->getSchool()->name_abbrev;
        });

        //
        // operations
        //
        $that = $this;
        $this->addButton('edit', _('Edit'))
            ->setText('Upravit')//todo i18n
            ->setLink(function ($row) use ($that) {
                return $that->getPresenter()->link('edit', $row->teacher_id);
            })
            ->setShow(function ($row) use ($presenter) {
                return $presenter->authorized('edit', ['id' => $row->teacher_id]);
            });

        if ($presenter->authorized('create')) {
            $this->addGlobalButton('add')
                ->setLabel('Create new teacher')
                ->setLink($this->getPresenter()->link('create'));
        }
    }
}
