<?php

namespace Tasks;

use FKSDB\ORM\Services\ServiceTask;
use Pipeline\PipelineException;
use Pipeline\Stage;
use SimpleXMLElement;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class TasksFromXML2 extends Stage {

    const XML_NAMESPACE = 'http://www.w3.org/XML/1998/namespace';

    /**
     * @var SeriesData
     */
    private $data;

    /**
     * @var array   xml element => task column
     */
    private static $xmlToColumnMap = [
        'name[@xml:lang="cs"]' => 'name_cs',
        'name[@xml:lang="en"]' => 'name_en',
        'points' => 'points',
        'label' => 'label',
    ];

    /**
     * @var \FKSDB\ORM\Services\ServiceTask
     */
    private $taskService;

    /**
     * TasksFromXML2 constructor.
     * @param \FKSDB\ORM\Services\ServiceTask $taskService
     */
    public function __construct(ServiceTask $taskService) {
        $this->taskService = $taskService;
    }

    /**
     * @param mixed $data
     */
    public function setInput($data) {
        $this->data = $data;
    }

    public function process() {
        $xml = $this->data->getData();
        $sImported = (string) $xml->number;
        $sSet = $this->data->getSeries();
        if ($sImported != $sSet) {
            throw new PipelineException(sprintf(_('Nesouhlasí importovaná (%s) a nastavená (%s) série.'), $sImported, $sSet));
        }
        $problems = $xml->problems[0]->problem;
        foreach ($problems as $task) {
            $this->processTask($task);
        }
    }

    /**
     * @return mixed|SeriesData
     */
    public function getOutput() {
        return $this->data;
    }

    /**
     * @param SimpleXMLElement $XMLTask
     */
    private function processTask(SimpleXMLElement $XMLTask) {
        $contest = $this->data->getContest();
        $year = $this->data->getYear();
        $series = $this->data->getSeries();
        $tasknr = (int) (string) $XMLTask->number;

        // obtain FKSDB\ORM\Models\ModelTask
        $task = $this->taskService->findBySeries($contest, $year, $series, $tasknr);
        if ($task == null) {
            $task = $this->taskService->createNew(array(
                'contest_id' => $contest->contest_id,
                'year' => $year,
                'series' => $series,
                'tasknr' => $tasknr,
            ));
        }

        // update fields
        $data = [];
        foreach (self::$xmlToColumnMap as $xmlElement => $column) {
            $value = NULL;

            // Argh, I was not able not make ->xpath() working so emulate it.
            $matches = [];
            if (preg_match('/([a-z]*)\[@xml:lang="([a-z]*)"\]/', $xmlElement, $matches)) {
                $name = $matches[1];
                $lang = $matches[2];
                $elements = $XMLTask->{$name};
                $csvalue = null;

                if (count($elements) == 1) {
                    if (count($elements[0]->attributes(self::XML_NAMESPACE)) == 0 || $elements[0]->attribute(self::XML_NAMESPACE)->lang == 'cs') {
                        $csvalue = (string) $elements[0];
                    }
                }
                foreach ($elements as $el) {
                    if (count($el->attributes(self::XML_NAMESPACE)) == 0) {
                        continue;
                    }
                    if ($el->attributes(self::XML_NAMESPACE)->lang == $lang) {
                        $value = (string) $el;
                        break;
                    }
                }
                $value = $value ?: $csvalue;
            } else {
                $value = (string) $XMLTask->{$xmlElement};
            }
            $data[$column] = $value;
        }
        $this->taskService->updateModel2($task,$data);

        // forward it to pipeline
        $this->data->addTask($tasknr, $task);
    }

}
