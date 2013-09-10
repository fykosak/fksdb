<?php

namespace FKSDB\Components\Grids;

use ModelContestant;
use Nette\Utils\Html;
use NiftyGrid\DataSource\NDataSource;
use ServiceSubmit;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class SubmitsGrid extends BaseGrid {

    /** @var ServiceSubmit */
    private $submitService;

    /**
     * @var ModelContestant 
     */
    private $contestant;

    function __construct(ServiceSubmit $submitService, ModelContestant $contestant) {
        parent::__construct();

        $this->submitService = $submitService;
        $this->contestant = $contestant;
    }

    protected function configure($presenter) {
        parent::configure($presenter);

        //
        // data
        //
        $submits = $this->submitService->getSubmits();
        $submits->where('ct_id = ?', $this->contestant->ct_id); //TODO year + contest? 

        $this->setDataSource(new NDataSource($submits));
        $this->setDefaultOrder('series DESC, tasknr ASC');

        //
        // columns
        //
        $this->addColumn('task', 'Úloha')
                ->setRenderer(function($row) use($presenter) {
                            $row->task_id; // stupid caching...
                            $task = $row->getTask();
                            $FQname = $task->getFQName();

                            $el = Html::el('a');
                            $el->href = $presenter->link(':Public:Submit:download', array('id' => $row->submit_id));
                            $el->setText($FQname);
                            return $el;
                        });
        $this->addColumn('submitted_on', 'Čas odevzdání');
        $this->addColumn('source', 'Způsob odevzdání');

        //
        // appeareance
        //
        $this->paginate = false;
        $this->enableSorting = false;
    }

}
