<?php

namespace App\Exports;

use App\Models\Contact;
use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ContactsExport implements FromCollection, WithHeadings, WithColumnWidths, WithStyles
{
    public function __construct(
        protected User $user,
        protected ?string $firstname = null,
        protected ?string $lastname = null,
        protected ?string $company = null,
        protected ?string $email = null,
        protected array $categoryIds = [],
        protected array $countryIds = [],
        protected array $ownerIds = [],
        protected ?int $status = null,
    ) {
    }

    public function collection()
    {
        $query = Contact::query()->with(['category', 'subcategory', 'country', 'owner']);

        if (!$this->user->hasRole('superadmin') && !$this->user->can('view all contacts')) {
            $query->where('owner_id', $this->user->id);
        } elseif (!empty($this->ownerIds)) {
            $query->whereIn('owner_id', $this->ownerIds);
        }

        if ($this->firstname !== null && $this->firstname !== '') {
            $query->where('firstname', 'like', '%' . $this->firstname . '%');
        }
        if ($this->lastname !== null && $this->lastname !== '') {
            $query->where('lastname', 'like', '%' . $this->lastname . '%');
        }
        if ($this->company !== null && $this->company !== '') {
            $query->where('company', 'like', '%' . $this->company . '%');
        }
        if ($this->email !== null && $this->email !== '') {
            $query->where('email', 'like', '%' . $this->email . '%');
        }
        if (!empty($this->categoryIds)) {
            $query->whereIn('category_id', $this->categoryIds);
        }
        if (!empty($this->countryIds)) {
            $query->whereIn('country', $this->countryIds);
        }
        if ($this->status !== null) {
            $query->where('status', $this->status);
        }

        return $query->orderBy('lastname')->orderBy('firstname')->get()->map(function (Contact $c) {
            return [
                'Prefix'        => $c->prefix,
                'Имя'           => $c->firstname,
                'Фамилия'       => $c->lastname,
                'Должность'     => $c->title,
                'Компания'      => $c->company,
                'Email'         => $c->email,
                'Телефон'       => $c->phone,
                'Мобильный'     => $c->cellphone,
                'Адрес'         => $c->address,
                'Адрес 2'       => $c->address2,
                'P.O. Box'      => $c->post_box,
                'Индекс'        => $c->zip_code,
                'Страна'        => $c->country?->name,
                'Город'         => $c->city,
                'Язык'          => $c->language,
                'Категория'     => $c->category?->title,
                'Подкатегория'  => $c->subcategory?->title,
                'Владелец'      => $c->owner?->name,
                'Статус'        => $c->status === Contact::STATUS_ACTIVE ? 'Активный' : 'Неактивный',
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Prefix', 'Имя', 'Фамилия', 'Должность', 'Компания', 'Email',
            'Телефон', 'Мобильный', 'Адрес', 'Адрес 2', 'P.O. Box', 'Индекс',
            'Страна', 'Город', 'Язык', 'Категория', 'Подкатегория', 'Владелец', 'Статус',
        ];
    }

    public function columnWidths(): array
    {
        return array_combine(range('A', 'S'), array_fill(0, 19, 22));
    }

    public function styles(Worksheet $sheet)
    {
        $highestRow = $sheet->getHighestRow();
        $range = 'A1:S' . $highestRow;

        $sheet->getStyle('A1:S1')->applyFromArray([
            'font' => [
                'bold'  => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType'   => Fill::FILL_SOLID,
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

        $sheet->getStyle($range)->applyFromArray([
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
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
}
