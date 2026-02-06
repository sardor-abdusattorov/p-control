<?php

namespace App\Exports;

use App\Models\Project;
use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class ProjectContractsExport implements FromCollection, WithHeadings, WithColumnWidths, WithStyles
{
    protected $project;
    protected $user;

    public function __construct(Project $project, User $user)
    {
        $this->project = $project;
        $this->user = $user;
    }

    public function collection()
    {
        $query = $this->project->contracts()
            ->with(['user', 'currency', 'contact', 'project', 'application']);

        if (!$this->user->can('view all contracts')) {
            $query->where('user_id', $this->user->id);
        }

        return $query->get()
            ->map(function ($contract) {
                return [
                    'Номер контракта' => $contract->contract_number,
                    'Проект'          => $contract->project?->title,
                    'Заявка'          => $contract->application?->title,
                    'Пользователь'    => $contract->user?->name,
                    'Статус'          => $this->getStatusLabel($contract->status),
                    'Валюта'          => $contract->currency?->short_name,
                    'Название'        => $contract->title,
                    'Сумма'           => $contract->budget_sum,
                    'Дедлайн'         => $contract->deadline,
                    'Контакт'         => $contract->contact?->fullname,
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
            'Валюта',
            'Название',
            'Сумма',
            'Дедлайн',
            'Контакт',
            'Создано',
            'Обновлено',
        ];
    }

    // Ширина 30 для всех колонок (A — L)
    public function columnWidths(): array
    {
        return array_combine(range('A', 'L'), array_fill(0, 12, 30));
    }

    public function styles(Worksheet $sheet)
    {
        $columns = 'A1:L' . ($sheet->getHighestRow());

        // Header (как и раньше)
        $sheet->getStyle('A1:L1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '304FFE'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
                'wrapText'   => true, // ВАЖНО: перенос текста для header
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'A0A0A0'],
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
                    'color' => ['rgb' => 'A0A0A0'],
                ],
            ],
        ]);

        return [];
    }


    public function getStatusLabel($status)
    {
        $statuses = [
            1   => 'Новый',
            2   => 'В процессе',
            3   => 'Одобрен',
            -1  => 'Отклонён',
            -2  => 'Аннулирован',
        ];
        return $statuses[$status] ?? $status;
    }
}
