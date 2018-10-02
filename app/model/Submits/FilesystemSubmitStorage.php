<?php

namespace Submits;

use ModelSubmit;
use Nette\Diagnostics\Debugger;

use Nette\InvalidStateException;
use Nette\Utils\Finder;
use Nette\Utils\Strings;
use UnexpectedValueException;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class FilesystemSubmitStorage implements ISubmitStorage {
    /** Characters delimiting name and metadata in filename. */

    const DELIMITER = '__';

    /** @const File extension that marks original untouched file. */
    const ORIGINAL_EXT = '.bak';

    /** @const File extension that marks temporary working file. */
    const TEMPORARY_EXT = '.tmp';

    /** @const File extension that marks final file extension.
     *         It's a bit dangerous that only supported filetype is hard-coded in this class
     */
    const FINAL_EXT = '.pdf';

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

    /**
     * @var array of IStorageProcessing
     */
    private $processings = [];

    function __construct($root, $directoryMask, $filenameMask, $contestMap) {
        $this->root = $root;
        $this->directoryMask = $directoryMask;
        $this->filenameMask = $filenameMask;
        $this->contestMap = $contestMap;
    }

    public function addProcessing(IStorageProcessing $processing) {
        $this->processings[] = $processing;
    }

    public function beginTransaction() {
        $this->todo = [];
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
                if ($this->existsFile($submit)) {
                    $this->deleteFile($submit);
                }

                $filename = $todo['file'];

                $dest = $this->root . DIRECTORY_SEPARATOR . $this->createDirname($submit) . DIRECTORY_SEPARATOR . $this->createFilename($submit);
                @mkdir(dirname($dest), 0777, TRUE); // @ - dir may already exist

                if (count($this->processings) > 0) {
                    $original = $dest . self::ORIGINAL_EXT;
                    $working = $dest . self::TEMPORARY_EXT;

                    rename($filename, $original);
                    chmod($original, 0644);
                    copy($original, $working);
                    foreach ($this->processings as $processing) {
                        $processing->setInputFile($working);
                        $processing->setOutputFile($dest);
                        try {
                            $processing->process($submit);
                            rename($dest, $working);
                        } catch (ProcessingException $e) {
                            Debugger::log($e);
                        }
                    }

                    rename($working, $dest);
                } else {
                    rename($filename, $dest);
                }
            }
        } catch (InvalidStateException $e) {
            throw new StorageException('Error while storing files.', null, $e);
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
     * @param string $filename
     * @param ModelSubmit $submit
     * @return void
     */
    public function storeFile($filename, ModelSubmit $submit) {
        if ($this->todo === null) {
            throw new InvalidStateException('Cannot store file out of transaction.');
        }

        $this->todo[] = [
            'file' => $filename,
            'submit' => $submit,
        ];
    }

    public function retrieveFile(ModelSubmit $submit, $type = self::TYPE_PROCESSED) {
        $files = $this->retrieveFiles($submit);
        if ($type == self::TYPE_ORIGINAL) {
            $files = array_filter($files, function($file) {
                        return Strings::endsWith($file->getRealPath(), self::ORIGINAL_EXT);
                    });
        } else {
            $files = array_filter($files, function($file) {
                        return !Strings::endsWith($file->getRealPath(), self::ORIGINAL_EXT) &&
                                !Strings::endsWith($file->getRealPath(), self::TEMPORARY_EXT);
                    });
        }

        if (count($files) == 0) {
            return null;
        } else if (count($files) > 1) {
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
    public function existsFile(ModelSubmit $submit) {
        $filename = $this->retrieveFile($submit);

        return (bool) $filename;
    }

    public function deleteFile(ModelSubmit $submit) {
        $fails = [];
        $files = $this->retrieveFiles($submit);
        foreach ($files as $file) {
            if (!unlink($file->getRealpath())) {
                $fails[] = $file->getRealpath();
            }
        }

        if (count($fails)) {
            throw new StorageException("Error when deleting '" . implode("', '", $fails) . "'");
        }
    }

    private function retrieveFiles(ModelSubmit $submit) {
        $dir = $this->root . DIRECTORY_SEPARATOR . $this->createDirname($submit);

        try {
            $it = Finder::findFiles('*' . self::DELIMITER . $submit->submit_id . '*')->in($dir);
            $files = iterator_to_array($it, false);
        } catch (UnexpectedValueException $e) {
            return [];
        }

        return $files;
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
        $filename = $filename . self::DELIMITER . $submit->submit_id . self::FINAL_EXT;

        return $filename;
    }

}

