<?php

namespace Tasks;

use ModelSubmit;
use Nette\Http\FileUpload;
use Nette\InvalidStateException;
use Nette\Utils\Finder;
use Nette\Utils\Strings;
use RuntimeException;
use UnexpectedValueException;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
class SubmitStorage {

    const DELIMITER = '__';

    private $todo = null;

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

    function __construct($root, $directoryMask, $filenameMask, $contestMap) {
        $this->root = $root;
        $this->directoryMask = $directoryMask;
        $this->filenameMask = $filenameMask;
        $this->contestMap = $contestMap;
    }

    public function beginTransaction() {
        $this->todo = array();
    }

    /**
     * @throws SubmitStorageException for unsuccessful commit
     */
    public function commit() {
        if ($this->todo === null) {
            throw new InvalidStateException('Cannot commit out of transaction.');
        }

        try {
            foreach ($this->todo as $todo) {
                $submit = $todo['submit'];

                // remove potential existing instance
                $oldFilename = $this->retrieveFile($submit);
                if (file_exists($oldFilename)) {
                    unlink($oldFilename);
                }

                $file = $todo['file'];
                $name = $file->getName();
                $extension = trim(strrchr($name, '.'), '.');

                $dest = $this->root . DIRECTORY_SEPARATOR . $this->createDirname($submit) . DIRECTORY_SEPARATOR . $this->createFilename($submit) . '.' . $extension;
                $file->move($dest);
            }
        } catch (InvalidStateException $e) {
            throw new SubmitStorageException('Error while storing files.', null, $e);
        }

        $this->todo = null;
    }

    /**
     * 
     * @throws InvalidStateException
     */
    public function rollback() {
        if ($this->todo === null) {
            throw new InvalidStateException('Cannot rollback out of transaction.');
        }

        $this->todo = null;
    }

    /**
     * 
     * @param FileUpload $file
     * @param \Tasks\ModelSubmit $submit Must be a stored submit.
     * @throws InvalidStateException
     */
    public function storeFile(FileUpload $file, ModelSubmit $submit) {
        if ($this->todo === null) {
            throw new InvalidStateException('Cannot store file out of transaction.');
        }

        $this->todo[] = array(
            'file' => $file,
            'submit' => $submit,
        );
    }

    public function retrieveFile(ModelSubmit $submit) {
        $dir = $this->root . DIRECTORY_SEPARATOR . $this->createDirname($submit);

        try {
            $it = Finder::findFiles('*' . self::DELIMITER . $submit . '*')->in($dir);
            $files = iterator_to_array($it, false);
        } catch (UnexpectedValueException $e) {
            return null;
        }

        if (count($files) > 1) {
            throw new InvalidStateException("Ambiguity in file database, submit #{$submit->id}.");
        }
        
        
        return count($files) ? $files[0]->getRealPath() : null;
    }

    /**
     * Checks whether there exists valid file for the submit.
     * 
     * @param ModelSubmit $submit
     * @return bool
     */
    public function existsFile(ModelSubmit $submit) {
        $filename = $this->retrieveFile($submit);

        return (bool) $filename;
    }

    /**
     * 
     * @param ModelSubmit $submit
     * @return string  directory part of the path relative to root, w/out trailing slash
     */
    private function createDirname(ModelSubmit $submit) {
        $task = $submit->getTask();
        $contestName = isset($this->contestMap[$task->contest_id]) ? $this->contestMap[$task->contest_id] : $task->contest_id;
        $year = $task->year;
        $series = $task->series;
        $label = Strings::webalize($task->label, null, false);

        $directory = sprintf($this->directoryMask, $contestName, $year, $series, $label);
        return $directory;
    }

    /**
     * @param ModelSubmit $submit
     * @return string
     */
    private function createFilename(ModelSubmit $submit) {
        $task = $submit->getTask();
        $contestName = isset($this->contestMap[$task->contest_id]) ? $this->contestMap[$task->contest_id] : $task->contest_id;
        $year = $task->year;
        $series = $task->series;
        $label = Strings::webalize($task->label, null, false);

        $contestant = $submit->getContestant();
        $contestantName = $contestant->getPerson()->getFullname();
        $contestantName = preg_replace('/ +/', '_', $contestantName);
        $contestantName = Strings::webalize($contestantName, '_');

        $filename = sprintf($this->filenameMask, $contestantName, $contestName, $year, $series, $label);

        // append metadata
        $filename = $filename . self::DELIMITER . $submit->submit_id;

        return $filename;
    }

}

class SubmitStorageException extends RuntimeException {
    
}