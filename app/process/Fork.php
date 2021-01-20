<?php

namespace app\process;


use app\framework\Log\SimpleLog;
use Illuminate\Support\Facades\Redis;

/**
 * 只能在cli环境下使用
 * Trait Fork
 * @package app\daemon
 */
trait Fork
{
    /**
     * @var SimpleLog
     */
    private $pidLog;

    private function log()
    {
        if (!$this->pidLog) {
            $this->pidLog = new SimpleLog('pid');
        }
        return $this->pidLog;
    }

    static protected function pidKey($pidKey)
    {
        return $pidKey;
    }

    protected $pidKey;

    protected function cProcess($closure, $pidKey)
    {
        pcntl_signal(SIGCHLD, SIG_IGN);
        $pid = pcntl_fork();
        if ($pid == -1) {
            die('子进程创建失败');
        } else if ($pid) {
            // 父进程执行继续执行
            return $pid;
        } else {
            $this->log()->add($pidKey, [getmypid()]);
            //设置当前进程为会话组长
            if (posix_setsid() < 0) {
                exit('Session leader set failed.' . PHP_EOL);
            }
            \srand();
            \mt_srand();
            //改变当前目录为根目录
            chdir('/');
            //重设文件掩码
            umask(0);
            //关闭打开的文件描述符
            fclose(STDIN);
            fclose(STDOUT);
            fclose(STDERR);
            self::setProcessTitle('php yun_shop fork ' . $pidKey);
            // 生成新的redis和mysql实例,新的进程如果使用相同旧实例,任何一个进程关闭数据库连接后,其他进程将无法连接数据报错
            app('redis')->refresh();
            app('db')->refresh();
            $this->pidKey = $pidKey;
            Redis::hset('forkPids:'.gethostname(),$this->pidKey,getmypid());
            Redis::hset('ProcessData', getmypid(),time());
            // 子进程执行完立刻退出,避免递归执行
            call_user_func($closure);
            Redis::hdel('ProcessData', getmypid(),time());
            Redis::hdel('forkPids:'.gethostname(),$this->pidKey);

            exit($pid);
        }
    }

    /**
     * Set process name.
     *
     * @param string $title
     * @return void
     */
    protected static function setProcessTitle($title)
    {
        \set_error_handler(function () {
        });
        // >=php 5.5
        if (\function_exists('cli_set_process_title')) {
            \cli_set_process_title($title);
        } // Need proctitle when php<=5.5 .
        elseif (\extension_loaded('proctitle') && \function_exists('setproctitle')) {
            \setproctitle($title);
        }
        \restore_error_handler();
    }
}