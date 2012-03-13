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
     * ignore schedule flag
     */
    protected static $ignore_schedule;

    /**
     * Run 
     * 
     * @static
     * @final
     * @access public
     * @return void
     */
    public static final function Run(Array $opt = array()) {
        self::$debug = !empty($opt['debug']);
        self::$ignore_schedule = !empty($opt['ignore_schedule']);
        self::$now = getdate();

        $jobs = !empty($opt['jobs']) ? $opt['jobs'] : array();
        settype($jobs, 'array');

        self::Debug('MoovicoCronRunner started.');
        self::lock();
        self::runQueue($jobs);
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
     * @param mixed $job 
     * @static
     * @access protected
     * @return void
     */
    protected static function runQueue($jobs = array()) {
        $jobs = !empty($jobs) ? $jobs : self::getJobs();

        self::Debug(count($jobs).' job(s) to consider.');

        @ob_end_clean();
        foreach ($jobs as $jobfile) {
            include_once($jobfile);
            $job = pathinfo($jobfile, PATHINFO_FILENAME);
            if (class_exists($job)) {
                self::Debug('Running '.$job);

                ob_start();
                $res = $job::Run(self::$ignore_schedule === true);
                $out = trim(ob_get_contents());
                ob_end_clean();

                $showOutput = self::$debug === true;
                switch ($res) {
                    case MoovicoCronjob::STATUS_FINISHED:
                        self::Debug('Job finished normally');
                        break;

                    case MoovicoCronjob::STATUS_OUT_OF_SCHEDULE:
                        self::Debug('Job is out of schedule');
                        break;

                    case MoovicoCronjob::STATUS_ERROR:
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
    public static function Debug($str, $class = __CLASS__, $flush = false) {
        if (self::$debug === true) {
            self::Output($str, $class, $flush);
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
    public static function Output($str, $class = __CLASS__, $flush = false) {
        foreach (explode("\n", $str) as $line) {
            printf("[%s %20s] %s\n", date('Y-m-d H:i:s'), $class, $line);
        }

        if ($flush) {
            ob_flush();
        }
    }
}

// self invoking if configured as cli handler
if (!empty($_SERVER['argc'])) {

    $options = getopt('', array('host:', 'root:', 'debug', 'ignore-schedule', 'jobs:'));
    if (empty($options['host']) || empty($options['root'])) {
        die("Usage: ".basename($_SERVER['argv'][0])." --host=HTTP_HOST --root=APP_ROOT [--debug] [--ignore-schedule] [--jobs=JOB_FILENAME_1,JOB_FILENAME_2,...]\n");
    }

    $debug = isset($options['debug']);
    $ignore_schedule = isset($options['ignore-schedule']); // note the dash
    $jobs = !empty($options['jobs']) ? explode(',', $options['jobs']) : array();

    require(dirname(__FILE__).'/Moovico.php');

    Moovico::SetEnv(array(
        'HTTP_HOST'         => $options['host'],
        'REQUEST_URI'       => '/',
        'MOOVICO_APP_ROOT'  => realpath($options['root']).'/',
        'SERVER_PROTOCOL'   => 'http',
        'HTTPS'             => 'on' // in case it's forced in config
    ));

    Moovico::Setup();
    MoovicoCronRunner::Run(array(
        'debug'             => $debug,
        'jobs'              => $jobs,
        'ignore_schedule'   => $ignore_schedule
    ));
}
