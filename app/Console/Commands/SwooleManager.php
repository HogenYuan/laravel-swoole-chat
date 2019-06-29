<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Handlers\SwooleHandler;

class SwooleManager extends Command
{
    private $server;

    private $pid_file;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'swoole {action}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'start or stop the swoole process';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->pid_file =  __DIR__ . '/../../../storage/swoole_websocket.pid';
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $arg = $this->argument('action');
        switch ($arg) {
            case 'start':
                $pid = $this->getPid();
                if ($pid && \Swoole\Process::kill($pid, 0)) {
                    $this->error("\r\nprocess already exist!\r\n");
                    exit;
                }
                $this->server = new \swoole_websocket_server("0.0.0.0", 9501);
                $this->server->set([
                    'worker_num' => 8,
                    'daemonize' => 1,
                    'max_request' => 1000,
                    'dispatch_mode' => 5,
                    'debug_mode' => 1,
                    'pid_file' => $this->pid_file
                ]);

                $app = SwooleHandler::getInstance();
                $this->server->on('open', array($app, 'onOpen'));
                // $this->server->on('handshake', array($app, 'onHandshake'));
                $this->server->on('message', array($app, 'onMessage'));
                $this->server->on('close', array($app, 'onClose'));
                $this->server->on('request', array($app, 'onRequest'));

                $this->info("\r\nprocess created successful!\r\n");
                $this->server->start();
                break;
            case 'stop':
                if (!$pid = $this->getPid()) {
                    $this->error("\r\nprocess not started!\r\n");
                    exit;
                }
                if (\Swoole\Process::kill((int) $pid)) {
                    $this->info("\r\nprocess close successful!\r\n");
                    exit;
                }
                $this->info("\r\nprocess close failed!\r\n");
                break;
            case 'info':
                if ($this->getPid()) {
                    $this->info("\r\npid:" . file_exists($this->pid_file) ? file_get_contents($this->pid_file) : false . "\r\n");
                    exit;
                }
            default:
                $this->error("\r\noperation method does not exist!\r\n");
        }
    }

    //获取pid
    public function getPid()
    {
        return file_exists($this->pid_file) ? file_get_contents($this->pid_file) : false;
    }
}
