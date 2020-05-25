<?php

namespace FKSDB\Submits\FileSystemStorage;

use FKSDB\ORM\Models\ModelSubmit;
use FKSDB\Submits\IStorageProcessing;
use FKSDB\Submits\ISubmitStorage;
use FKSDB\Submits\ProcessingException;
use FKSDB\Submits\StorageException;
use Tracy\Debugger;
use Nette\InvalidStateException;
use Nette\Utils\Finder;
use Nette\Utils\Strings;
use UnexpectedValueException;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class UploadedStorage implements ISubmitStorage {
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

    /**
     * @var null
     */
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
     * @var IStorageProcessing[]
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
     * @return void
     */
    public function addProcessing(IStorageProcessing $processing) {
        $this->processings[] = $processing;
    }

    public function beginTransaction() {
        $this->todo = [];
    }

    /**
     * @throws StorageException for unsuccessful commit
     */
    public function commit() {
        if ($this->todo === null) {
            throw new InvalidStateException('Cannot commit out of transaction.');
        }

        try {
            foreach ($this->todo as $todo) {
                $submit = $todo['submit'];

                // remove potential existing instance
                if ($this->fileExists($submit)) {
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
                        } catch (ProcessingException $exception) {
                            Debugger::log($exception);
                        }
                    }

                    rename($working, $dest);
                } else {
                    rename($filename, $dest);
                }
            }
        } catch (InvalidStateException $exception) {
            throw new StorageException('Error while storing files.', null, $exception);
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

    /**
     * @param ModelSubmit $submit
     * @param int $type
     * @return null|string
     */
    public function retrieveFile(ModelSubmit $submit, $type = self::TYPE_PROCESSED) {
        $files = $this->retrieveFiles($submit);
        if ($type == self::TYPE_ORIGINAL) {
            $files = array_filter($files, function (\SplFileInfo $file) {
                return Strings::endsWith($file->getRealPath(), self::ORIGINAL_EXT);
            });
        } else {
            $files = array_filter($files, function (\SplFileInfo $file) {
                return !Strings::endsWith($file->getRealPath(), self::ORIGINAL_EXT) &&
                    !Strings::endsWith($file->getRealPath(), self::TEMPORARY_EXT);
            });
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
     * @return void
     */
    public function deleteFile(ModelSubmit $submit) {
        $fails = [];
        $files = $this->retrieveFiles($submit);
        foreach ($files as $file) {
            if (!unlink($file->getRealPath())) {
                $fails[] = $file->getRealPath();
            }
        }

        if (count($fails)) {
            throw new StorageException("Error when deleting '" . implode("', '", $fails) . "'");
        }
    }

    /**
     * @param ModelSubmit $submit
     * @return \SplFileInfo[]
     */
    private function retrieveFiles(ModelSubmit $submit): array {
        $dir = $this->root . DIRECTORY_SEPARATOR . $this->createDirname($submit);

        try {
            $it = Finder::findFiles('*' . self::DELIMITER . $submit->submit_id . '*')->in($dir);
            return iterator_to_array($it, false);
        } catch (UnexpectedValueException $exception) {
            return [];
        }
    }

    /**
     * @param ModelSubmit $submit
     * @return string  directory part of the path relative to root, w/out trailing slash
     */
    private function createDirname(ModelSubmit $submit) {
        $task = $submit->getTask();
        return sprintf($this->directoryMask, $task->getContest()->getContestSymbol(), $task->year, $task->series, $task->webalizeLabel());
    }

    /**
     * @param ModelSubmit $submit
     * @return string
     */
    private function createFilename(ModelSubmit $submit) {
        $task = $submit->getTask();

        $contestantName = $submit->getContestant()->getPerson()->getFullName();
        $contestantName = preg_replace('/ +/', '_', $contestantName);
        $contestantName = Strings::webalize($contestantName, '_');

        $filename = sprintf($this->filenameMask, $contestantName, $task->getContest()->getContestSymbol(), $task->year, $task->series, $task->webalizeLabel());

        // append metadata
        return $filename . self::DELIMITER . $submit->submit_id . self::FINAL_EXT;
    }

}
