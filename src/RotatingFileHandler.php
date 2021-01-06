<?php
/**
 * The file is part of the swoft_log_handler.
 *
 * (c) alan <alan1766447919@gmail.com>.
 *
 * 2021/1/5 1:11 下午
 */

namespace Anhoder\Swoft\Log;

use Swoft\Log\Handler\FileHandler;

/**
 * Rotating Log Handler
 * @class RotatingFileHandler
 */
class RotatingFileHandler extends FileHandler
{
    /**
     * 0 => unlimited
     * @var int
     */
    protected $maxFiles = 0;

    /**
     * @var bool|null
     */
    protected $willRotate;

    /**
     * @inheritdoc
     */
    protected function write(array $records): void
    {
        // on the first record written, if the log is new, we should rotate (once per day)
        if (null === $this->willRotate) {
            $this->willRotate = !file_exists($this->formatFile($this->logFile));
        }

        parent::write($records);
    }

    /**
     * @inheritdoc
     */
    public function close()
    {
        parent::close();

        if (true === $this->willRotate) {
            $this->rotate();
        }
    }

    /**
     * reset
     */
    public function reset()
    {
        parent::reset();

        if (true === $this->willRotate) {
            $this->rotate();
        }
    }

    /**
     * Rotates the files.
     */
    protected function rotate()
    {
        // skip GC of old logs if files are unlimited
        if (0 === $this->maxFiles) {
            return;
        }

        $logFiles = glob($this->getGlobPattern());
        if ($this->maxFiles >= count($logFiles)) {
            // no files to remove
            return;
        }

        // Sorting the files by name to remove the older ones
        usort($logFiles, function ($a, $b) {
            return strcmp($b, $a);
        });

        foreach (array_slice($logFiles, $this->maxFiles) as $file) {
            if (is_writable($file)) {
                // suppress errors here as unlink() might fail if two processes
                // are cleaning up/rotating at the same time
                set_error_handler(function ($errno, $errstr, $errfile, $errline) {});
                unlink($file);
                restore_error_handler();
            }
        }

        $this->willRotate = false;
    }

    /**
     * @return string|string[]
     */
    protected function getGlobPattern()
    {
        return preg_replace('/%(.*)\{(.*)\}/', '*', $this->logFile);
    }
}
