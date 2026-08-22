<?php

namespace App\Domains\Finance\Actions;

use App\Domains\Finance\Enums\InvoiceStatus;
use App\Domains\Finance\Models\Invoice;
use App\Domains\Platform\Services\AuditLogger;
use App\Support\ActivityHelper;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class UpsertInvoice
{
    public function __construct(
        private AuditLogger $auditLogger,
        private SyncInvoiceTotals $syncInvoiceTotals,
    ) {}

    /** @param  array<string, mixed>  $data  @param  array<int, array<string, mixed>>  $lines */
    public function handle(?Invoice $model, array $data, array $lines = []): Invoice
    {
        if ($model !== null && $model->status !== InvoiceStatus::Draft) {
            throw new InvalidArgumentException('Only draft invoices can be updated.');
        }

        return DB::transaction(function () use ($model, $data, $lines) {
            if ($model === null) {
                $data['number'] = $data['number'] ?? $this->nextNumber($data['organization_id']);
                $data['status'] = InvoiceStatus::Draft;
                $model = Invoice::query()->create($data);
                $this->auditLogger->log('invoice.created', $model, null, $model->toArray());
                ActivityHelper::log('Invoice created', $model);
            } else {
                $old = $model->toArray();
                $model->update($data);
                $this->auditLogger->log('invoice.updated', $model, $old, $model->fresh()->toArray());
                ActivityHelper::log('Invoice updated', $model);
            }

            if ($lines !== []) {
                $this->syncInvoiceTotals->syncLines($model, $lines);
            }

            return $model->fresh(['lines', 'client:id,company_name']);
        });
    }

    private function nextNumber(string $organizationId): string
    {
        $last = Invoice::withoutGlobalScopes()
            ->where('organization_id', $organizationId)
            ->orderByDesc('number')
            ->value('number');

        $seq = 1;
        if ($last && preg_match('/INV-(\d+)/', $last, $m)) {
            $seq = (int) $m[1] + 1;
        }

        return sprintf('INV-%04d', $seq);
    }
}
