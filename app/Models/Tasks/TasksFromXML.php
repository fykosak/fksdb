<?php

declare(strict_types=1);

namespace FKSDB\Models\Tasks;

use FKSDB\Models\ORM\Services\TaskService;
use FKSDB\Models\Pipeline\PipelineException;
use FKSDB\Models\Pipeline\Stage;
use Fykosak\Utils\Logging\MemoryLogger;

class TasksFromXML extends Stage
{
    public const XML_NAMESPACE = 'http://www.w3.org/XML/1998/namespace';

    /** @var array   xml element => task column */
    private static array $xmlToColumnMap = [
        'name[@xml:lang="cs"]' => 'name_cs',
        'name[@xml:lang="en"]' => 'name_en',
        'points' => 'points',
        'label' => 'label',
    ];

    private TaskService $taskService;

    public function __construct(TaskService $taskService)
    {
        $this->taskService = $taskService;
    }

    /**
     * @param SeriesData $data
     */
    public function __invoke(MemoryLogger $logger, $data): SeriesData
    {
        $xml = $data->getData();
        $sImported = (string)$xml->number;
        $sSet = $data->getSeries();
        if ($sImported != $sSet) {
            throw new PipelineException(
                sprintf(_('Imported (%s) and set (%s) series does not match.'), $sImported, $sSet)
            );
        }
        $problems = $xml->problems[0]->problem;
        foreach ($problems as $task) {
            $this->processTask($task, $data);
        }
        return $data;
    }

    private function processTask(\SimpleXMLElement $xMLTask, SeriesData $datum): void
    {
        $series = $datum->getSeries();
        $tasknr = (int)(string)$xMLTask->number;

        // update fields
        $data = [];
        foreach (self::$xmlToColumnMap as $xmlElement => $column) {
            $value = null;

            // Argh, I was not able not make ->xpath() working so emulate it.
            $matches = [];
            if (preg_match('/([a-z]*)\[@xml:lang="([a-z]*)"\]/', $xmlElement, $matches)) {
                $name = $matches[1];
                $lang = $matches[2];
                /** @var \SimpleXMLElement[] $elements */
                $elements = $xMLTask->{$name};
                $csvalue = null;

                if (count($elements) == 1) {
                    if (
                        count($elements[0]->attributes(self::XML_NAMESPACE)) == 0
                        || $elements[0]->attributes(self::XML_NAMESPACE)->lang == 'cs'
                    ) {
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
                $value = (string)$xMLTask->{$xmlElement};
            }
            $data[$column] = $value;
        }

        // obtain FKSDB\Models\ORM\Models\ModelTask
        $task = $this->taskService->findBySeries($datum->getContestYear(), $series, $tasknr);

        $task = $this->taskService->storeModel(
            array_merge($data, [
                'contest_id' => $datum->getContestYear()->contest_id,
                'year' => $datum->getContestYear()->year,
                'series' => $series,
                'tasknr' => $tasknr,
            ]),
            $task
        );
        $datum->addTask($tasknr, $task);
    }
}
