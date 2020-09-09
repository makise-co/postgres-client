<?php
/**
 * This file is part of the Makise-Co Postgres Client
 * World line: 0.571024a
 *
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

require \dirname(__DIR__) . '/../vendor/autoload.php';

use MakiseCo\Postgres\ConnectionConfigBuilder;
use MakiseCo\Postgres\PostgresPool;
use Swoole\Timer;

use function Swoole\Coroutine\run;

run(
    static function () {
        $connectionConfig = (new ConnectionConfigBuilder())
            ->withHost('127.0.0.1')
            ->withPort(5432)
            ->withUser('makise')
            ->withPassword('el-psy-congroo')
            ->withDatabase('makise')
            ->build();

        $pool = new PostgresPool($connectionConfig);
        // need to have at least two connections, one connection is reserved to listen
        $pool->setMaxActive(2);
        $pool->setMinActive(0);
        $pool->init();

        $channel = "test";

        $listener = $pool->listen($channel);

        printf("Listening on channel '%s'\n", $channel);

        Timer::after(
            3000,
            function () use ($listener) { // Unlisten in 3 seconds.
                printf("Unlistening from channel '%s'\n", $listener->getChannel());
                $listener->unlisten();
            }
        );

        Timer::after(
            1000,
            function () use ($pool, $channel) {
                $pool->notify($channel, "Data 1"); // Send first notification.
            }
        );

        Timer::after(
            2000,
            function () use ($pool, $channel) {
                $pool->notify($channel, "Data 2"); // Send second notification.
            }
        );

        while ($notification = $listener->getNotification()) {
            printf(
                "Received notification from PID %d on channel '%s' with payload: %s\n",
                $notification->pid,
                $notification->channel,
                $notification->payload
            );
        }

        $pool->close();

        print_r("[+] Done\n");
    }
);
