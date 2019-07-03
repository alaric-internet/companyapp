<?php
/**
 * Created by PhpStorm.
 * User: fesiong
 * Date: 2019-06-30
 * Time: 19:12
 */

namespace Alaric;

class Logger
{
    public $logFile;
    public $error;

    public function __construct() {
        $this->logFile = storage_path("logs") . '/error.log';
    }

    public function showError($message = null, $code = 0){
        header("HTTP/1.1 404 Not Found");
        header("Status: 404 Not Found");

        if(!$message){
            $message = $this->error['message'];
        }
        if(!$code){
            $code = $this->error['type'];
        }

        output($code, $message);
    }

    public function handleException($exception){
        $this->error = [
            'type'    => $exception->getCode(),
            'message' => $exception->getMessage(),
            'file'    => $exception->getFile(),
            'line'    => $exception->getLine(),
            'trace'   => $exception->getTraceAsString(),
        ];
        $this->log();
    }

    public function handleError($type, $message, $file, $line){
        $this->error = [
            'type'    => $type,
            'message' => $message,
            'file'    => $file,
            'line'    => $line,
        ];

        $this->log();
    }

    public static function handleShutdown(){
        //todo
    }

    public function log(){
        $messages = [
            'time'    => date('Y-m-d H:i:s'),
            'code'    => $this->error['type'],
            'file'    => $this->error['file'],
            'line'    => $this->error['line'],
            'message' => $this->error['message'],
            'trace'   => $this->error['trace'],
            'end'     => "\r\n"
        ];
        //todo 这里还需要上报错误到接口

        file_put_contents($this->logFile, implode("\t", $messages), FILE_APPEND);
        unset($messages);

        $this->showError();
    }
}