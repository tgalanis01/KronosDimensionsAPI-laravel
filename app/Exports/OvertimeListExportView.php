<?php

namespace App\Exports;


use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\Exportable;
use App\API\KronosDimensions;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Border;

class OvertimeListExportView implements FromCollection, WithHeadings, WithEvents, WithCustomStartCell, ShouldAutoSize
{


    protected $overtimeList;

    protected $keys;

    public function __construct($overtimeList, $keys)
    {
        $this->overtimeList = $overtimeList;
        $this->keys = $keys;

    }

    public function headings(): array
    {
        return $this->keys;
    }

    public function view(): View
    {
        return view('print', [
            'data' => $this->overtimeList,
            'keys' => $this->keys
        ]);
    }

    /**
     * @inheritDoc
     */
    public function collection()
    {
        // TODO: Implement collection() method.
        return $this->overtimeList;
    }

    public function startCell(): string
    {
        return 'A2';
    }

    /**
     * @return array
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {

                $event->sheet->getDelegate()->getPageMargins()->setTop(.25);
                $event->sheet->getDelegate()->getPageMargins()->setRight(0.1);
                $event->sheet->getDelegate()->getPageMargins()->setLeft(0.1);
                $event->sheet->getDelegate()->getPageMargins()->setBottom(.25);

                $event->sheet->getDelegate()->getPageSetup()->setFitToWidth(1);
                $event->sheet->getDelegate()->getPageSetup()->setFitToHeight(1);
                $event->sheet->getDelegate()->getPageSetup()->setHorizontalCentered(1);

                $event->sheet->getDelegate()->getColumnDimension('A')->setAutoSize(1);
                $event->sheet->getDelegate()->getColumnDimension('B')->setWidth(19);
                $event->sheet->getDelegate()->getColumnDimension('C')->setWidth(17);
                $event->sheet->getDelegate()->getColumnDimension('D')->setWidth(24);
                $event->sheet->getDelegate()->getColumnDimension('E')->setAutoSize(1);
                $event->sheet->getDelegate()->setShowGridlines(0);


                $event->sheet->getDelegate()->getStyle('A1:E100')->getBorders()->getAllBorders()->setBorderStyle('none');

                $event->sheet->getDelegate()->setCellValue('A1', 'Overtime List');
                $event->sheet->getDelegate()->mergeCells('A1:E1')->getStyle('A1:E1')->getAlignment()->setHorizontal('center')->setVertical('top');
                $event->sheet->getDelegate()->getStyle('A1:E1')->getFont()->setSize(18)->setName('Arial');
                $event->sheet->getDelegate()->getRowDimension(1)->setRowHeight(50);
                // All headers - formatting
                $headerStyleArray = [
                    'font' => [
                        'bold' => true,
                        'name' =>  'Arial',
                        'size' => 14,
                        'color' => ['rgb' => 'FFFFFF'],
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    ],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'color' => ['rgb' => '191970'],
                    ],
                ];
                $headerCellRange = 'A2:E2';
                $event->sheet->getDelegate()->getStyle($headerCellRange)->applyFromArray($headerStyleArray);
                $event->sheet->getDelegate()->setAutoFilter($headerCellRange);

                // All Body - formatting
                $bodyStyleArray = [
                    'font' =>  [
                        'name' => 'Arial',
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
                    ],
                    'borders' => [
                        'inside' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        ]
                    ]

                ];
                $bodyCellRange = 'A3:E100';
                $event->sheet->getDelegate()->getStyle($bodyCellRange)->applyFromArray($bodyStyleArray);

            },
        ];
    }


}
