<?php

declare(strict_types=1);

use App\Domains\Platform\Services\AuditLogger;

it('resolves the audit logger from the container', function () {
    expect(app(AuditLogger::class))->toBeInstanceOf(AuditLogger::class);
});
