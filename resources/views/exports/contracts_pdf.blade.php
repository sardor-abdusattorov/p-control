<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>{{ __('app.label.contracts') }}</title>
    <style>
        @page { margin: 20px 18px; }
        * { font-family: DejaVu Sans, sans-serif; }
        body { font-size: 10px; color: #1f2937; }
        h1 { font-size: 16px; margin: 0 0 4px 0; }
        .meta { font-size: 9px; color: #4b5563; margin-bottom: 10px; }
        .meta strong { color: #111827; }
        table { width: 100%; border-collapse: collapse; }
        thead th {
            background: #304FFE;
            color: #ffffff;
            font-weight: bold;
            text-align: center;
            padding: 6px 4px;
            border: 1px solid #a0a0a0;
        }
        tbody td {
            border: 1px solid #a0a0a0;
            padding: 5px 4px;
            vertical-align: middle;
            text-align: center;
            word-wrap: break-word;
        }
        tbody tr:nth-child(even) td { background: #f9fafb; }
        .status {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 8px;
            font-weight: bold;
            white-space: nowrap;
        }
        .s-1  { background: #dbeafe; color: #1e3a8a; }
        .s-2  { background: #fef3c7; color: #92400e; }
        .s-3  { background: #dcfce7; color: #166534; }
        .s--1 { background: #fee2e2; color: #991b1b; }
        .s--2 { background: #e5e7eb; color: #1f2937; }
        .empty { text-align: center; padding: 20px; color: #6b7280; }
        .total { margin-top: 8px; font-size: 10px; color: #374151; }
    </style>
</head>
<body>
    <h1>{{ __('app.label.contracts') }}</h1>
    <div class="meta">
        <strong>{{ __('app.label.year') }}:</strong> {{ $year ?? __('app.label.all') }}
        &nbsp;|&nbsp;
        <strong>{{ __('app.label.status') }}:</strong> {{ $statusLabel ?? __('app.label.all') }}
        &nbsp;|&nbsp;
        <strong>{{ __('app.label.created') ?? 'Создано' }}:</strong> {{ now()->format('d.m.Y H:i') }}
    </div>

    @if($contracts->isEmpty())
        <div class="empty">{{ __('app.label.no_contracts') }}</div>
    @else
        <table>
            <thead>
                <tr>
                    <th style="width: 4%;">#</th>
                    <th style="width: 12%;">{{ __('app.label.contract_number') }}</th>
                    <th style="width: 18%;">{{ __('app.label.title') }}</th>
                    <th style="width: 12%;">{{ __('app.label.project') }}</th>
                    <th style="width: 7%;">{{ __('app.label.year') }}</th>
                    <th style="width: 12%;">{{ __('app.label.user_id') }}</th>
                    <th style="width: 10%;">{{ __('app.label.contract_sum') }}</th>
                    <th style="width: 10%;">{{ __('app.label.deadline') }}</th>
                    <th style="width: 15%;">{{ __('app.label.status') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($contracts as $i => $contract)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $contract->contract_number ?: '-' }}</td>
                        <td style="text-align: left;">{{ $contract->title ?: '-' }}</td>
                        <td style="text-align: left;">{{ $contract->project?->title ?: '-' }}</td>
                        <td>{{ $contract->project?->category?->year ?: '-' }}</td>
                        <td>{{ $contract->user?->name ?: '-' }}</td>
                        <td>
                            {{ $contract->budget_sum !== null ? number_format($contract->budget_sum, 2, '.', ' ') : '-' }}
                            {{ $contract->currency?->short_name ?: '' }}
                        </td>
                        <td>{{ $contract->deadline ?: '-' }}</td>
                        <td>
                            <span class="status s-{{ (int) $contract->status }}">
                                {{ $statusLabels[$contract->status] ?? $contract->status }}
                            </span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="total">
            <strong>{{ __('app.label.contracts_length') }}:</strong> {{ $contracts->count() }}
        </div>
    @endif
</body>
</html>
