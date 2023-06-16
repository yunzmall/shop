<?php


namespace app\console\Commands;


use app\framework\Cron\Cron;
use app\framework\Log\CronLog;
use Liebig\Cron\RunCommand;

class CronRun extends RunCommand
{
    /**
     * Execute the console command.
     *
     * @return void
     */
    public function fire() {
        // Fire event before the Cron jobs will be executed
        \Event::dispatch('cron.collectJobs');
        $report = Cron::run();

        if($report['inTime'] === -1) {
            $inTime = -1;
        } else if ($report['inTime']) {
            $inTime = 'true';
        } else {
            $inTime = 'false';
        }

        // Get Laravel version
        $laravel = app();
        $version = $laravel::VERSION;

        if ($version < '5.2') {
            // Create table for old Laravel versions.
            $table = $this->getHelperSet()->get('table');
            $table->setHeaders(array('Run date', 'In time', 'Run time', 'Errors', 'Jobs'));
            $table->addRow(array($report['rundate'], $inTime, round($report['runtime'], 4), $report['errors'], count($report['crons'])));
        } else {
            // Create table for new Laravel versions.
            $table = new \Symfony\Component\Console\Helper\Table($this->getOutput());
            $table
                ->setHeaders(array('Run date', 'In time', 'Run time', 'Errors', 'Jobs'))
                ->setRows(array(array($report['rundate'], $inTime, round($report['runtime'], 4), $report['errors'], count($report['crons']))));
        }

        // Output table.
        $table->render($this->getOutput());
    }
}