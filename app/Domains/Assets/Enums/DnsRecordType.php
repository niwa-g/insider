<?php

namespace App\Domains\Assets\Enums;

enum DnsRecordType: string
{
    case A = 'A';
    case Aaaa = 'AAAA';
    case Cname = 'CNAME';
    case Mx = 'MX';
    case Txt = 'TXT';
    case Ns = 'NS';
    case Srv = 'SRV';
    case Caa = 'CAA';

    public function label(): string
    {
        return match ($this) {
            self::A => 'A',
            self::Aaaa => 'AAAA',
            self::Cname => 'CNAME',
            self::Mx => 'MX',
            self::Txt => 'TXT',
            self::Ns => 'NS',
            self::Srv => 'SRV',
            self::Caa => 'CAA',
        };
    }

    /** @return array<string, string> */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $case) => [$case->value => $case->label()])
            ->all();
    }
}
