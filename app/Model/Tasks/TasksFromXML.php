<?php

namespace FKSDB\Model\Tasks;

use FKSDB\Model\ORM\Services\ServiceTask;
use FKSDB\Model\Pipeline\PipelineException;
use FKSDB\Model\Pipeline\Stage;
use SimpleXMLElement;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class TasksFromXML extends Stage {

    public const XML_NAMESPACE = 'http://www.w3.org/XML/1998/namespace';

    private SeriesData $data;

    /** @var array   xml element => task column */
    private static array $xmlToColumnMap = [
        'name[@xml:lang="cs"]' => 'name_cs',
        'name[@xml:lang="en"]' => 'name_en',
        'points' => 'points',
        'label' => 'label',
    ];

    private ServiceTask $taskService;

    public function __construct(ServiceTask $taskService) {
        $this->taskService = $taskService;
    }

    /**
     * @param SeriesData $data
     */
    public function setInput($data): void {
        $this->data = $data;
    }

    public function process(): void {
        $xml = $this->data->getData();
        $sImported = (string)$xml->number;
        $sSet = $this->data->getSeries();
        if ($sImported != $sSet) {
            throw new PipelineException(sprintf(_('Imported (%s) and set (%s) series does not match.'), $sImported, $sSet));
        }
        $problems = $xml->problems[0]->problem;
        foreach ($problems as $task) {
            $this->processTask($task);
        }
    }

    public function getOutput(): SeriesData {
        return $this->data;
    }

    private function processTask(SimpleXMLElement $XMLTask): void {
        $contest = $this->data->getContest();
        $year = $this->data->getYear();
        $series = $this->data->getSeries();
        $tasknr = (int)(string)$XMLTask->number;

        // update fields
        $data = [];
        foreach (self::$xmlToColumnMap as $xmlElement => $column) {
            $value = null;

            // Argh, I was not able not make ->xpath() working so emulate it.
            $matches = [];
            if (preg_match('/([a-z]*)\[@xml:lang="([a-z]*)"\]/', $xmlElement, $matches)) {
                $name = $matches[1];
                $lang = $matches[2];
                $elements = $XMLTask->{$name};
                $csvalue = null;

                if (count($elements) == 1) {
                    if (count($elements[0]->attributes(self::XML_NAMESPACE)) == 0 || $elements[0]->attributes(self::XML_NAMESPACE)->lang == 'cs') {
                        $csvalue = (string)$elements[0];
                    }
                }
                foreach ($elements as $el) {
                    if (count($el->attributes(self::XML_NAMESPACE)) == 0) {
                        continue;
                    }
                    if ($el->attributes(self::XML_NAMESPACE)->lang == $lang) {
                        $value = (string)$el;
                        break;
                    }
                }
                $value = $value ?: $csvalue;
            } else {
                $value = (string)$XMLTask->{$xmlElement};
            }
            $data[$column] = $value;
        }

        // obtain FKSDB\Model\ORM\Models\ModelTask
        $task = $this->taskService->findBySeries($contest, $year, $series, $tasknr);

        if ($task == null) {
            $task = $this->taskService->createNewModel(array_merge($data, [
                'contest_id' => $contest->contest_id,
                'year' => $year,
                'series' => $series,
                'tasknr' => $tasknr,
            ]));
        } else {
            $this->taskService->updateModel2($task, $data);
        }
        // forward it to pipeline
        $this->data->addTask($tasknr, $task);
    }

}