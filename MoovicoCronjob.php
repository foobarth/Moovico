<?php

/**
 * MoovicoCronjob 
 * 
 * @abstract
 * @package 
 * @version 
 * @license 
 */
abstract class MoovicoCronjob {

    const STATUS_FINISHED = 0;
    const STATUS_OUT_OF_SCHEDULE = 1;
    const STATUS_ERROR = 2;

    /**
     * the schedule (cron format: m h dom mon dow)
     */
    protected static $schedule;

    /**
     * Run 
     * 
     * @static
     * @final
     * @access public
     * @return void
     */
    public static final function Run() {
        if (static::shouldRunNow()) {
            try {
                static::doRun();
                return self::STATUS_FINISHED;
            } catch (MoovicoException $e) {
                echo $e->getMessage();
            }
        } else {
            return self::STATUS_OUT_OF_SCHEDULE;
        }

        return self::STATUS_ERROR;
    }

    /**
     * doRun 
     * 
     * @static
     * @access protected
     * @return void
     */
    static protected function doRun() {
    }

    /**
     * shouldRunNow 
     * 
     * @static
     * @access protected
     * @return void
     */
    protected static function shouldRunNow() {
        if (empty(static::$schedule)) {
            return false;
        }

        $now = Cron_Runner::GetTime();
        list($minutes, $hours, $day, $month, $dayofweek) = explode(' ', static::$schedule);

        if (is_numeric($minutes) && $minutes != $now['minutes']) {
            return false;
        }

        if (is_numeric($hours) && $hours != $now['hours']) {
            return false;
        }

        if (is_numeric($day) && $day != $now['mday']) {
            return false;
        }

        if (is_numeric($month) && $month != $now['mon']) {
            return false;
        }

        if (is_numeric($dayofweek) && $dayofweek != $now['wday']) {
            return false;
        }

        return true;
    }
}
