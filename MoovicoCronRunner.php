<?php

/**
 * MoovicoCronRunner 
 * 
 * @final
 * @package 
 * @version 
 * @license 
 */
final class MoovicoCronRunner {

    /**
     * debug flag
     */
    protected static $debug = true;

    /**
     * now
     */
    protected static $now;

    /**
     * lock file and path
     */
    protected static $lock_file;

    /**
     * lock handle
     */
    protected static $lock_handle;

    /**
     * Run 
     * 
     * @static
     * @final
     * @access public
     * @return void
     */
    public static final function Run($debug = false) {
        self::$debug = $debug === true;
        self::Debug('MoovicoCronRunner started.');
        self::lock();
        self::$now = getdate();
        self::runQueue();
        self::unlock();
    }

    /**
     * GetTime 
     * 
     * @static
     * @final
     * @access public
     * @return void
     */
    public static function GetTime() {
        return self::$now;
    }

    /**
     * getJobs 
     * 
     * @static
     * @access protected
     * @return void
     */
    protected static function getJobs() {
        $dir = Moovico::GetAppRoot().'/cronjobs';
        $jobs = glob("$dir/*Cronjob.php");

        return $jobs;
    }

    /**
     * runQueue 
     * 
     * @static
     * @access protected
     * @return void
     */
    protected static function runQueue() {
        $jobs = self::getJobs();
        self::Debug(count($jobs).' job(s) to consider.');
        @ob_end_clean();
        foreach ($jobs as $jobfile) {
            include_once($jobfile);
            $job = pathinfo($jobfile, PATHINFO_FILENAME);
            if (class_exists($job)) {
                self::Debug('Running '.$job);

                ob_start();
                $res = $job::Run();
                $out = trim(ob_get_contents());
                ob_end_clean();

                $showOutput = self::$debug === true;
                switch ($res) {
                    case Cron_Base::STATUS_FINISHED:
                        self::Debug('Job finished normally');
                        break;

                    case Cron_Base::STATUS_OUT_OF_SCHEDULE:
                        self::Debug('Job is out of schedule');
                        break;

                    case Cron_Base::STATUS_ERROR:
                        $showOutput = true;
                        self::Output('Job finished with errors');
                        break;
                }

                if (!empty($out) && $showOutput === true) {
                    self::Output($out, $job);
                }
            } else {
                self::Output('Unable to run '.$job);
            }
        }
    }

    /**
     * lock 
     * 
     * @static
     * @access protected
     * @return void
     */
    protected static function lock() {
        self::$lock_file = "/tmp/".__CLASS__."_".md5(Moovico::GetAppRoot()).".lock";
        self::$lock_handle = fopen(self::$lock_file, "c");
        self::Debug('Lockfile created: '.self::$lock_file);
        if (!self::$lock_handle) {
            self::Output("Could not obtain a lock.");
            exit(-1);
        }

        if (!flock(self::$lock_handle, LOCK_EX)) {
            self::Output("Could not lock.");
            exit(1);
        }

        self::Debug("Lock acquired.");
    }

    /**
     * unlock 
     * 
     * @static
     * @access protected
     * @return void
     */
    protected static function unlock() {
        if (!self::$lock_handle) {
            self::Debug("No lock to release.");
            return;
        }

        if (!flock(self::$lock_handle, LOCK_UN)) {
            self::Output("Could not release the lock.");
            exit(1);
        }

        @unlink(self::$lock_file);

        self::Debug("Lock released.");
    }

    /**
     * Debug 
     * 
     * @param mixed $str 
     * @param mixed $class 
     * @static
     * @access public
     * @return void
     */
    public static function Debug($str, $class = __CLASS__) {
        if (self::$debug === true) {
            self::Output($str, $class);
        }
    }

    /**
     * Output 
     * 
     * @param mixed $str 
     * @param mixed $class 
     * @static
     * @access public
     * @return void
     */
    public static function Output($str, $class = __CLASS__) {
        printf("[%s %20s] %s\n", date('Y-m-d H:i:s'), $class, $str);
    }
}

// self invoking if configured as fcgi handler
if (!empty($_SERVER['argc'])) {

    $options = getopt('', array('host:', 'root:', 'debug'));
    if (empty($options['host']) || empty($options['root'])) {
        die("Usage: ".basename($_SERVER['argv'][0])." --host=HTTP_HOST --root=APP_ROOT [--debug]\n");
    }

    $debug = isset($options['debug']);

    require(dirname(__FILE__).'/Moovico.php');

    Moovico::SetEnv(array(
        'HTTP_HOST'         => $options['host'],
        'REQUEST_URI'       => '/',
        'MOOVICO_APP_ROOT'  => realpath($options['root']).'/',
        'HTTPS'             => 'on' // in case it's forced in config
    ));

    Moovico::Setup();
    MoovicoCronRunner::Run($debug);
}
