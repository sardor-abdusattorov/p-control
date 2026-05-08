<?php

namespace App\Exports;

use App\Models\Contract;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;

class ContractsPdfExport
{
    public function __construct(
        protected User $user,
        protected ?int $year = null,
        protected ?int $status = null,
    ) {
    }

    public function download(string $filename = 'contracts.pdf')
    {
        $contracts = $this->buildQuery()->get();

        $statusLabels = collect(Contract::getStatuses())
            ->pluck('label', 'id')
            ->toArray();

        $statusLabel = $this->status !== null
            ? ($statusLabels[$this->status] ?? (string) $this->status)
            : null;

        $pdf = Pdf::loadView('exports.contracts_pdf', [
            'contracts'    => $contracts,
            'year'         => $this->year,
            'status'       => $this->status,
            'statusLabel'  => $statusLabel,
            'statusLabels' => $statusLabels,
        ])->setPaper('a4', 'landscape');

        return $pdf->download($filename);
    }

    protected function buildQuery(): Builder
    {
        $query = Contract::query()
            ->with(['user', 'currency', 'project.category']);

        if (!$this->user->can('view all contracts')) {
            $query->where('user_id', $this->user->id);
        }

        if ($this->status !== null) {
            $query->where('status', $this->status);
        }

        if ($this->year !== null) {
            $year = $this->year;
            $query->whereHas('project.category', function ($q) use ($year) {
                $q->where('year', $year);
            });
        }

        return $query->latest('updated_at');
    }
}
