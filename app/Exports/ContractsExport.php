<?php

namespace App\Exports;

use App\Models\Contract;
use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class ContractsExport implements FromCollection, WithHeadings, WithColumnWidths, WithStyles
{
    public function __construct(
        protected User $user,
        protected ?int $year = null,
        protected ?int $status = null,
        protected ?int $transactionType = null,
        protected ?int $userId = null,
    ) {
    }

    public function collection()
    {
        $query = Contract::query()
            ->with(['user', 'currency', 'contact', 'project.category', 'application']);

        if (!$this->user->can('view all contracts')) {
            $query->where('user_id', $this->user->id);
        } elseif ($this->userId !== null) {
            $query->where('user_id', $this->userId);
        }

        if ($this->status !== null) {
            $query->where('status', $this->status);
        }

        if ($this->transactionType !== null) {
            $query->where('transaction_type', $this->transactionType);
        }

        if ($this->year !== null) {
            $year = $this->year;
            $query->whereHas('project.category', function ($q) use ($year) {
                $q->where('year', $year);
            });
        }

        return $query->latest('updated_at')->get()->map(function ($contract) {
            $contact = $contract->contact;
            $contactName = $contact
                ? trim(($contact->firstname ?? '') . ' ' . ($contact->lastname ?? ''))
                : null;

            return [
                'Номер контракта' => $contract->contract_number,
                'Проект'          => $contract->project?->title,
                'Заявка'          => $contract->application?->title,
                'Пользователь'    => $contract->user?->name,
                'Статус'          => $this->getStatusLabel($contract->status),
                'Тип операции'    => $this->getTransactionTypeLabel($contract->transaction_type),
                'Валюта'          => $contract->currency?->short_name,
                'Название'        => $contract->title,
                'Сумма'           => $contract->budget_sum,
                'Дедлайн'         => $contract->deadline,
                'Контакт'         => $contactName ?: null,
                'Год проекта'     => $contract->project?->category?->year,
                'Создано'         => $contract->created_at,
                'Обновлено'       => $contract->updated_at,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Номер контракта',
            'Проект',
            'Заявка',
            'Пользователь',
            'Статус',
            'Тип операции',
            'Валюта',
            'Название',
            'Сумма',
            'Дедлайн',
            'Контакт',
            'Год проекта',
            'Создано',
            'Обновлено',
        ];
    }

    public function columnWidths(): array
    {
        return array_combine(range('A', 'N'), array_fill(0, 14, 30));
    }

    public function styles(Worksheet $sheet)
    {
        $columns = 'A1:N' . $sheet->getHighestRow();

        $sheet->getStyle('A1:N1')->applyFromArray([
            'font' => [
                'bold'  => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '304FFE'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
                'wrapText'   => true,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color'       => ['rgb' => 'A0A0A0'],
                ],
            ],
        ]);

        $sheet->getStyle($columns)->applyFromArray([
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
                'wrapText'   => true,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color'       => ['rgb' => 'A0A0A0'],
                ],
            ],
        ]);

        return [];
    }

    protected function getStatusLabel($status): string
    {
        $statuses = [
            Contract::STATUS_NEW         => 'Новый',
            Contract::STATUS_IN_PROGRESS => 'В процессе',
            Contract::STATUS_APPROVED    => 'Одобрен',
            Contract::STATUS_REJECTED    => 'Отклонён',
            Contract::STATUS_INVALIDATED => 'Аннулирован',
        ];

        return $statuses[$status] ?? (string) $status;
    }

    protected function getTransactionTypeLabel($type): string
    {
        $types = [
            Contract::TYPE_EXPENSE => 'Расход',
            Contract::TYPE_INCOME  => 'Приход',
        ];

        return $types[$type] ?? '';
    }
}
