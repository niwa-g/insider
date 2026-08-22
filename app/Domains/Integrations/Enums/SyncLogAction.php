<?php

namespace App\Domains\Integrations\Enums;

enum SyncLogAction: string
{
    case Connect = 'connect';
    case Test = 'test';
    case Sync = 'sync';
    case Disconnect = 'disconnect';

    public function label(): string
    {
        return match ($this) {
            self::Connect => 'Connect',
            self::Test => 'Test',
            self::Sync => 'Sync',
            self::Disconnect => 'Disconnect',
        };
    }
}
