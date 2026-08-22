<?php

namespace App\Domains\Finance\Services;

use App\Domains\Finance\Enums\FinancialEntryStatus;
use App\Domains\Finance\Enums\FinancialEntryType;
use App\Domains\Finance\Models\FinancialEntry;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class ProfitabilityCalculator
{
    /** @return array{revenue: float, cost: float, profit: float} */
    public function forOrganization(
        string $organizationId,
        ?string $clientId = null,
        ?string $from = null,
        ?string $to = null,
    ): array {
        $revenue = $this->sumPosted($organizationId, FinancialEntryType::Revenue, $clientId, $from, $to);
        $cost = $this->sumPosted($organizationId, FinancialEntryType::Cost, $clientId, $from, $to);

        return [
            'revenue' => $revenue,
            'cost' => $cost,
            'profit' => $revenue - $cost,
        ];
    }

    public function monthlyBreakdown(
        string $organizationId,
        ?string $clientId = null,
        ?string $from = null,
        ?string $to = null,
    ): Collection {
        $fromDate = $from ? Carbon::parse($from)->startOfMonth() : now()->startOfYear();
        $toDate = $to ? Carbon::parse($to)->endOfMonth() : now()->endOfMonth();

        $entries = FinancialEntry::query()
            ->where('organization_id', $organizationId)
            ->where('status', FinancialEntryStatus::Posted)
            ->whereNull('void_of_id')
            ->when($clientId, fn ($q) => $q->where('client_id', $clientId))
            ->whereBetween('occurred_on', [$fromDate->toDateString(), $toDate->toDateString()])
            ->get(['entry_type', 'amount', 'occurred_on']);

        $months = [];
        $cursor = $fromDate->copy();

        while ($cursor <= $toDate) {
            $key = $cursor->format('Y-m');
            $months[$key] = ['month' => $key, 'revenue' => 0.0, 'cost' => 0.0, 'profit' => 0.0];
            $cursor->addMonth();
        }

        foreach ($entries as $entry) {
            $key = $entry->occurred_on->format('Y-m');
            if (! isset($months[$key])) {
                continue;
            }
            $amount = (float) $entry->amount;
            if ($entry->entry_type === FinancialEntryType::Revenue) {
                $months[$key]['revenue'] += $amount;
            } else {
                $months[$key]['cost'] += $amount;
            }
            $months[$key]['profit'] = $months[$key]['revenue'] - $months[$key]['cost'];
        }

        return collect($months)->values();
    }

    /** @return array<int, array{client_id: string, company_name: string, revenue: float, cost: float, profit: float}> */
    public function byClient(string $organizationId, ?string $from = null, ?string $to = null): array
    {
        $query = FinancialEntry::query()
            ->where('financial_entries.organization_id', $organizationId)
            ->where('financial_entries.status', FinancialEntryStatus::Posted)
            ->whereNull('financial_entries.void_of_id')
            ->when($from, fn ($q) => $q->whereDate('occurred_on', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('occurred_on', '<=', $to))
            ->join('clients', 'clients.id', '=', 'financial_entries.client_id')
            ->selectRaw('financial_entries.client_id, clients.company_name, financial_entries.entry_type, SUM(financial_entries.amount) as total')
            ->groupBy('financial_entries.client_id', 'clients.company_name', 'financial_entries.entry_type');

        $grouped = [];
        foreach ($query->get() as $row) {
            $id = $row->client_id;
            if (! isset($grouped[$id])) {
                $grouped[$id] = [
                    'client_id' => $id,
                    'company_name' => $row->company_name,
                    'revenue' => 0.0,
                    'cost' => 0.0,
                    'profit' => 0.0,
                ];
            }
            if ($row->entry_type === FinancialEntryType::Revenue->value) {
                $grouped[$id]['revenue'] = (float) $row->total;
            } else {
                $grouped[$id]['cost'] = (float) $row->total;
            }
            $grouped[$id]['profit'] = $grouped[$id]['revenue'] - $grouped[$id]['cost'];
        }

        return array_values($grouped);
    }

    public function mrr(string $organizationId, ?string $clientId = null): float
    {
        return (new MrrCalculator)->forOrganization($organizationId, $clientId);
    }

    private function sumPosted(
        string $organizationId,
        FinancialEntryType $type,
        ?string $clientId,
        ?string $from,
        ?string $to,
    ): float {
        return (float) FinancialEntry::query()
            ->where('organization_id', $organizationId)
            ->where('entry_type', $type)
            ->where('status', FinancialEntryStatus::Posted)
            ->whereNull('void_of_id')
            ->when($clientId, fn ($q) => $q->where('client_id', $clientId))
            ->when($from, fn ($q) => $q->whereDate('occurred_on', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('occurred_on', '<=', $to))
            ->sum('amount');
    }
}
