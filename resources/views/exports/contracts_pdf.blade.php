<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>{{ __('app.label.contracts') }}</title>
    <style>
        @page { margin: 14px 12px; }
        * { font-family: DejaVu Sans, sans-serif; }
        body { font-size: 8px; color: #1f2937; }
        h1 { font-size: 14px; margin: 0 0 4px 0; }
        .meta { font-size: 8px; color: #4b5563; margin-bottom: 8px; }
        .meta strong { color: #111827; }
        table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        thead th {
            background: #304FFE;
            color: #ffffff;
            font-weight: bold;
            text-align: center;
            padding: 5px 3px;
            border: 1px solid #a0a0a0;
            font-size: 8px;
        }
        tbody td {
            border: 1px solid #a0a0a0;
            padding: 4px 3px;
            vertical-align: middle;
            text-align: center;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }
        tbody tr:nth-child(even) td { background: #f9fafb; }
        .status {
            display: inline-block;
            padding: 2px 5px;
            border-radius: 8px;
            font-weight: bold;
            white-space: nowrap;
            font-size: 7.5px;
        }
        .s-1  { background: #dbeafe; color: #1e3a8a; }
        .s-2  { background: #fef3c7; color: #92400e; }
        .s-3  { background: #dcfce7; color: #166534; }
        .s--1 { background: #fee2e2; color: #991b1b; }
        .s--2 { background: #e5e7eb; color: #1f2937; }
        .empty { text-align: center; padding: 20px; color: #6b7280; }
        .total { margin-top: 6px; font-size: 9px; color: #374151; }
        .left { text-align: left; }
    </style>
</head>
<body>
    <h1>{{ __('app.label.contracts') }}</h1>
    <div class="meta">
        <strong>{{ __('app.label.year') }}:</strong> {{ $year ?? __('app.label.all') }}
        &nbsp;|&nbsp;
        <strong>{{ __('app.label.status') }}:</strong> {{ $statusLabel ?? __('app.label.all') }}
        &nbsp;|&nbsp;
        <strong>{{ __('app.label.created') }}:</strong> {{ now()->format('d.m.Y H:i') }}
    </div>

    @if($contracts->isEmpty())
        <div class="empty">{{ __('app.label.no_contracts') }}</div>
    @else
        <table>
            <thead>
                <tr>
                    <th style="width: 8%;">{{ __('app.label.contract_number') }}</th>
                    <th style="width: 9%;">{{ __('app.label.project') }}</th>
                    <th style="width: 8%;">{{ __('app.label.application') }}</th>
                    <th style="width: 8%;">{{ __('app.label.user_id') }}</th>
                    <th style="width: 8%;">{{ __('app.label.status') }}</th>
                    <th style="width: 5%;">{{ __('app.label.currency') }}</th>
                    <th style="width: 12%;">{{ __('app.label.title') }}</th>
                    <th style="width: 7%;">{{ __('app.label.contract_sum') }}</th>
                    <th style="width: 7%;">{{ __('app.label.deadline') }}</th>
                    <th style="width: 9%;">{{ __('app.label.contact') }}</th>
                    <th style="width: 5%;">{{ __('app.label.year') }}</th>
                    <th style="width: 7%;">{{ __('app.label.created') }}</th>
                    <th style="width: 7%;">{{ __('app.label.updated') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($contracts as $contract)
                    @php
                        $contact = $contract->contact;
                        $contactName = $contact
                            ? trim(($contact->firstname ?? '') . ' ' . ($contact->lastname ?? ''))
                            : '';
                    @endphp
                    <tr>
                        <td>{{ $contract->contract_number ?: '-' }}</td>
                        <td class="left">{{ $contract->project?->title ?: '-' }}</td>
                        <td class="left">{{ $contract->application?->title ?: '-' }}</td>
                        <td>{{ $contract->user?->name ?: '-' }}</td>
                        <td>
                            <span class="status s-{{ (int) $contract->status }}">
                                {{ $statusLabels[$contract->status] ?? $contract->status }}
                            </span>
                        </td>
                        <td>{{ $contract->currency?->short_name ?: '-' }}</td>
                        <td class="left">{{ $contract->title ?: '-' }}</td>
                        <td>{{ $contract->budget_sum !== null ? number_format($contract->budget_sum, 2, '.', ' ') : '-' }}</td>
                        <td>{{ $contract->deadline ?: '-' }}</td>
                        <td class="left">{{ $contactName !== '' ? $contactName : '-' }}</td>
                        <td>{{ $contract->project?->category?->year ?: '-' }}</td>
                        <td>{{ $contract->created_at ?: '-' }}</td>
                        <td>{{ $contract->updated_at ?: '-' }}</td>
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
