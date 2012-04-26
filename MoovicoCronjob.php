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
    public static final function Run($ignore_schedule = false) {
        if ($ignore_schedule === true || static::shouldRunNow()) {
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

        $now = MoovicoCronRunner::GetTime();
        list($minutes, $hours, $mday, $mon, $wday) = explode(' ', static::$schedule);

        if (!self::partIsMatching($minutes, $now['minutes'])) {
            return false;
        }

        if (!self::partIsMatching($hours, $now['hours'])) {
            return false;
        }

        if (!self::partIsMatching($mday, $now['mday'])) {
            return false;
        }

        if (!self::partIsMatching($mon, $now['mon'])) {
            return false;
        }

        if (!self::partIsMatching($wday, $now['wday'])) {
            return false;
        }

        return true;
    }

    /**
     * partIsMatching 
     * 
     * @param mixed $schedulePartValue 
     * @param mixed $nowPartValue 
     * @access protected
     * @return void
     */
    protected static function partIsMatching($schedulePartValue, $nowPartValue) {
        if ($schedulePartValue === '*') {
            return true;
        }

        $testValues = array();
        if (is_numeric($schedulePartValue)) {
            $testValues[] = $schedulePartValue;
        } else if (strpos($schedulePartValue, ',') !== false) {
            $tmp = explode(',', $schedulePartValue);
            foreach ($tmp as $val) {
                if (is_numeric($val)) {
                    $testValues[] = $val;
                }
            }
        } else if (strpos($schedulePartValue, '-') !== false) {
            list($from, $to) = explode('-', $schedulePartValue);
            for ($val = $from; $val <= $to; $val++) {
                if (is_numeric($val)) {
                    $testValues[] = $val;
                }
            }
        } else {
            throw new MoovicoException('Unparseable schedule part found');
        }

        foreach ($testValues as $val) {
            if ($val == $nowPartValue) {
                return true;
            }
        }

        return false;
    }
}
