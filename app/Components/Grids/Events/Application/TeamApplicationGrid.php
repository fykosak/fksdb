<?php

namespace FKSDB\Components\Grids\Events;

use Closure;
use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Components\Controls\PhoneNumber\PhoneNumberFactory;
use FKSDB\Components\Forms\Containers\Models\ContainerWithOptions;
use FKSDB\Components\Grids\BaseGrid;
use FKSDB\ORM\Models\Fyziklani\ModelFyziklaniTeam;
use FKSDB\ORM\Models\ModelEvent;
use Nette\Application\UI\Presenter;
use Nette\Database\Table\Selection;
use Nette\Forms\Form;
use SQL\SearchableDataSource;

/**
 * Class TeamApplicationGrid
 * @package FKSDB\Components\Grids\Events
 */
class TeamApplicationGrid extends BaseGrid {
    /**
     * @var ModelEvent
     */
    protected $event;
    /**
     * @var string[]
     */
    private $columns = [];

    /**
     * ParticipantGrid constructor.
     * @param ModelEvent $event
     */
    public function __construct(ModelEvent $event) {
        parent::__construct();
        $this->event = $event;
    }

    /**
     * @param Presenter $presenter
     * @throws \NiftyGrid\DuplicateColumnException
     * @throws \NiftyGrid\DuplicateButtonException
     */
    protected function configure($presenter) {
        parent::configure($presenter);
        $participants = $this->event->getTeams();
        $this->paginate = false;

        $source = new SearchableDataSource($participants);
        $source->setFilterCallback($this->getFilterCallBack());
        $this->setDataSource($source);


        $this->addColumn('e_fyziklani_team_id');
        $this->addColumn('name', _('Team name'));

        $this->addColumn('status', _('Status'));
        $this->addColumn('category', _('Category'));
        $this->addColumn('game_lang', _('Game language'));

        $this->addColumn('points', _('Points'));
        $this->addColumn('rank_category', _('Rank in category'));
        $this->addColumn('rank_total', _('Total rank'));


        $this->addColumn('created', _('Created'));
        $this->addColumn('phone', _('Phone'))->setRenderer(function ($row) {
            $model = ModelFyziklaniTeam::createFromTableRow($row);
            if ($model->phone) {
                return PhoneNumberFactory::format($model->phone);
            }
            return 'NA';

        });
        $this->addColumn('note', _('Note'));
        $this->addColumn('password', _('Password'));

        $this->addColumns();
        /*
                $this->addColumn('status', _('Status'))->setRenderer(function ($row) use ($presenter) {
                    $model = ModelEventParticipant::createFromTableRow($row);
                    switch ($model->status) {
                        case'participated':
                            $className = 'badge badge-success';
                            break;
                        case 'applied':
                        case 'applied.nodsef':
                        case 'applied.notsaf':
                        case 'applied.tsaf':
                        case 'approved':
                        case 'paid':
                            $className = 'badge badge-primary';
                            break;
                        case 'interested':
                            $className = 'badge badge-info';
                            break;
                        case 'rejected':
                        case 'missed':
                        case 'cancelled':
                            $className = 'badge badge-danger';
                            break;
                        case 'out_of_db':
                            $className = 'badge badge-light';
                            break;
                        case 'auto.invited':
                        case 'invited':
                        case 'invited1':
                        case 'invited2':
                        case 'invited3':
                        case 'auto.spare':
                        case 'spare':
                        case 'spare1':
                        case 'spare2':
                        case 'spare3':
                        case 'spare.tsaf':
                        case 'pending':
                            $className = 'badge badge-warning';
                            break;
                        case 'disqualified':
                            $className = 'badge badge-dark';
                            break;
                        default:
                            $className = 'badge badge-secondary';
                    }
                    return Html::el('span')
                        ->addAttributes(['class' => $className])
                        ->add(_($model->status));
                });

*/
        $this->addButton('detail')->setShow(function ($row) {
            $model = ModelFyziklaniTeam::createFromTableRow($row);
            // return true;
            return \in_array($model->getEvent()->event_type_id, [1, 9]);
        })->setText(_('Detail'))
            ->setLink(function ($row) {
                $model = ModelFyziklaniTeam::createFromTableRow($row);
                return $this->getPresenter()->link('detail', [
                    'id' => $model->e_fyziklani_team_id,
                ]);
            });
    }

    /**
     * @return FormControl
     * @throws \Nette\Application\BadRequestException
     */
    protected function createComponentSearchForm(): FormControl {
        // TODo from DB
        $states = [
            'participated',
            'applied',
            'applied.nodsef',
            'applied.notsaf',
            'applied.tsaf',
            'approved',
            'paid',
            'interested',
            'rejected',
            'missed',
            'cancelled',
            'out_of_db',
            'auto.invited',
            'invited',
            'invited1',
            'invited2',
            'invited3',
            'auto.spare',
            'spare',
            'spare1',
            'spare2',
            'spare3',
            'spare.tsaf',
            'pending',
            'disqualified',
        ];
        $control = new FormControl();
        $form = $control->getForm();
        $stateContainer = new ContainerWithOptions();
        $stateContainer->setOption('label', _('States'));
        foreach ($states as $state) {
            // TODO read default value from URL
            $stateContainer->addCheckbox(\str_replace('.', '__', $state), _($state));
        }
        $form->addComponent($stateContainer, 'status');
        $form->addSubmit('submit', _('Apply'));
        $form->onSuccess[] = function (Form $form) {
            $values = $form->getValues();
            $this->searchTerm = $values;
            $this->dataSource->applyFilter($values);
            $count = $this->dataSource->getCount();
            $this->getPaginator()->itemCount = $count;
        };
        return $control;
    }

    /**
     * @return Closure
     */
    public function getFilterCallBack(): Closure {
        return function (Selection $table, $value) {
            $states = [];
            foreach ($value->status as $state => $value) {
                if ($value) {
                    $states[] = \str_replace('__', '.', $state);
                }
            }
            if (\count($states)) {
                $table->where('status IN ?', $states);
            }
        };
    }


    /**
     * @throws \NiftyGrid\DuplicateColumnException
     */
    private function addColumns() {
        $this->addColumn('teacher_id');

        $this->addColumn('teacher_accomodation');
        $this->addColumn('teacher_present');
        $this->addColumn('teacher_schedule');
    }
}
