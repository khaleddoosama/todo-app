<?php

namespace Monolog\Handler;

abstract class Handler implements HandlerInterface
{

    public function __construct()
    {

    }

    // Implement methods from HandlerInterface
    public function handle(\Monolog\LogRecord $record): bool
    {
        // Handle the log record
        // This is just a placeholder implementation
        echo $record->message;
        return true;
    }


}
