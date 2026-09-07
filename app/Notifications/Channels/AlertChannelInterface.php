<?php

namespace App\Notifications\Channels;

interface AlertChannelInterface
{
    public function name(): string;

    public function send(string $message): bool;
}
