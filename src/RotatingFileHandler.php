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
    public const INTERVAL_YEAR     = 'year';
    public const INTERVAL_MONTH    = 'month';
    public const INTERVAL_DAY      = 'day';
    public const INTERVAL_HOUR     = 'hour';
    public const INTERVAL_MINUTE   = 'minute';

    /**
     * 0 => unlimited
     * @var int
     */
    protected $maxFiles = 0;

    /**
     * 检查日志文件数量的时间间隔
     * @var string
     */
    protected $checkLogInterval = self::INTERVAL_DAY;

    /**
     * @var bool|null
     */
    private $willRotate;

    /**
     * @var int
     */
    private $checkTime;

    /**
     * @param string $interval
     * @return int
     */
    protected static function getCheckTime(string $interval)
    {
        switch ($interval) {
            case self::INTERVAL_YEAR:
                return date('Y0101000000');
            case self::INTERVAL_MONTH:
                return date('Ym01000000');
            case self::INTERVAL_DAY:
                return date('Ymd000000');
            case self::INTERVAL_HOUR:
                return date('YmdH0000');
            case self::INTERVAL_MINUTE:
                return date('YmdHi00');
            default:
                throw new \InvalidArgumentException('参数错误');
        }
    }

    /**
     * @inheritdoc
     */
    protected function write(array $records): void
    {
        $checkTime = self::getCheckTime($this->checkLogInterval);
        // on the first record written, if the log is new, we should rotate (once per day)
        if ($this->checkTime != $checkTime) {
            $this->checkTime = $checkTime;
            $this->willRotate = !file_exists($this->formatFile($this->logFile));
        }

        parent::write($records);

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
