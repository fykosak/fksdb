<?php

namespace FKSDB\Submits;

use FKSDB\Exceptions\NotImplementedException;
use FKSDB\ORM\Models\ModelSubmit;
use Nette\InvalidStateException;
use Nette\Utils\Finder;
use UnexpectedValueException;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class FilesystemCorrectedSubmitStorage implements ISubmitStorage {
    /** Characters delimiting name and metadata in filename. */
    const DELIMITER = '__';

    /** @const File extension that marks final file extension.
     *         It's a bit dangerous that only supported filetype is hard-coded in this class
     */
    const EXTENSION = '.pdf';

    /**
     * @var string  Absolute path to (existing) directory of the storage.
     */
    private $root;

    /**
     * Sprintf string for arguments (in order): contestName, year, series, label
     * @var string
     */
    private $directoryMask;

    /**
     * Sprintf string for arguments (in order): contestantName, contestName, year, series, label.
     * File extension + metadata will be added to the name.
     *
     * @var string
     */
    private $filenameMask;

    /**
     * @var array   contestId => contest name
     */
    private $contestMap;

    /**
     * @var array of IStorageProcessing
     */
    private $processings = [];

    /**
     * FilesystemSubmitStorage constructor.
     * @param $root
     * @param $directoryMask
     * @param $filenameMask
     * @param $contestMap
     */
    public function __construct($root, $directoryMask, $filenameMask, $contestMap) {
        $this->root = $root;
        $this->directoryMask = $directoryMask;
        $this->filenameMask = $filenameMask;
        $this->contestMap = $contestMap;
    }

    /**
     * @param IStorageProcessing $processing
     */
    public function addProcessing(IStorageProcessing $processing) {
        $this->processings[] = $processing;
    }

    /**
     * @throws NotImplementedException
     */
    public function beginTransaction() {
        throw new NotImplementedException();
    }

    /**
     * @throws NotImplementedException
     */
    public function commit() {
        throw new NotImplementedException();
    }

    /**
     * @throws NotImplementedException
     */
    public function rollback() {
        throw new NotImplementedException();
    }

    /**
     * @param string $filename
     * @param ModelSubmit $submit
     * @throws NotImplementedException
     */
    public function storeFile($filename, ModelSubmit $submit) {
        throw new NotImplementedException();
    }

    /**
     * @param ModelSubmit $submit
     * @param int $type
     * @return null|string
     */
    public function retrieveFile(ModelSubmit $submit, $type = self::TYPE_PROCESSED) {
        $dir = $this->root . DIRECTORY_SEPARATOR . $this->createDirname($submit);

        try {
            $it = Finder::findFiles('*' . self::DELIMITER . $submit->submit_id . '*')->in($dir);
            $files = iterator_to_array($it, false);
        } catch (UnexpectedValueException $exception) {
            return null;
        }

        if (count($files) == 0) {
            return null;
        } elseif (count($files) > 1) {
            throw new InvalidStateException("Ambiguity in file database for submit #{$submit->submit_id}.");
        } else {
            $file = array_pop($files);
            return $file->getRealPath();
        }
    }

    /**
     * Checks whether there exists valid file for the submit.
     *
     * @param ModelSubmit $submit
     * @return bool
     */
    public function fileExists(ModelSubmit $submit) {
        return (bool)$this->retrieveFile($submit);
    }

    /**
     * @param ModelSubmit $submit
     * @throws NotImplementedException
     */
    public function deleteFile(ModelSubmit $submit) {
        throw new NotImplementedException();
    }

    /**
     * @param ModelSubmit $submit
     * @return string  directory part of the path relative to root, w/out trailing slash
     */
    private function createDirname(ModelSubmit $submit) {
        $task = $submit->getTask();
        return sprintf($this->directoryMask, $task->getContest()->getContestSymbol(), $task->year, $task->series, $task->webalizeLabel());
    }

}

