<?php

namespace FKSDB\ValidationTest\Tests;


use FKSDB\Components\Grids\Validation\ValidationGrid;
use FKSDB\ORM\Models\ModelContest;
use FKSDB\ORM\Models\ModelEventParticipant;
use FKSDB\ORM\Models\ModelPerson;
use FKSDB\ValidationTest\ValidationLog;
use FKSDB\ValidationTest\ValidationTest;
use Nette\Utils\Html;

/**
 * Class ParticipantsDurationTest
 * @package FKSDB\ValidationTest
 */
class ParticipantsDuration extends ValidationTest {


    /**
     * @param ModelPerson $person
     * @return ValidationLog[]
     */
    public static function run(ModelPerson $person): array {
        // $max = null;
        // $min = null;
        $data = [];
        $log = [];
        /**
         * @var ModelContest[] $contests
         */
        $contests = [];
        foreach ($person->getEventParticipant() as $row) {
            $model = ModelEventParticipant::createFromTableRow($row);
            $event = $model->getEvent();
            $contestId = $event->getEventType()->contest_id;
            $year = $event->year;
            if (!isset($data[$contestId])) {
                $contests[$contestId] = ModelContest::createFromTableRow($event->getEventType()->contest);
                $data[$contestId] = ['max' => null, 'min' => null];
            }

            $data[$contestId]['max'] = (is_null($data[$contestId]['max']) || $data[$contestId]['max'] < $year) ? $year : $data[$contestId]['max'];
            $data[$contestId]['min'] = (is_null($data[$contestId]['min']) || $data[$contestId]['min'] > $year) ? $year : $data[$contestId]['min'];
        };
        foreach ($data as $key => $value) {
            $delta = $value['max'] - $value['min'];
            $log[] = new ValidationLog(\sprintf('Počet rokov zúčastnujucich sa na akciach seminaru %s je %d', $contests[$key]->name, $delta + 1),
                ($delta < 4) ? 'success' : (($delta == 4) ? 'warning' : 'danger'));

        }
        return $log;
    }

    /**
     * @return string
     */
    public function getAction(): string {
        return 'participantsDuration';
    }

    /**
     * @return string
     */
    public function getTitle(): string {
        return _('účasť na akciach');
    }

    /**
     * @param ValidationGrid $grid
     * @throws \NiftyGrid\DuplicateColumnException
     */
    public static function configureGrid(ValidationGrid $grid) {
        $grid->addColumn('participant_duration')->setRenderer(function ($row) {
            $model = ModelPerson::createFromTableRow($row);
            $logs = self::run($model);
            $container = Html::el('span');
            foreach ($logs as $log) {
                $container->add(self::createHtml($log));
            }
            return $container;
        });
    }

}
