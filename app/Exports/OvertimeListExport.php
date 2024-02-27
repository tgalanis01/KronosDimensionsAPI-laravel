<?php

namespace App\Exports;


use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Events\BeforeSheet;

class OvertimeListExport implements FromCollection, WithHeadings, WithEvents, WithCustomStartCell
{

    /**
     * The overtime list collection.
     *
     * @var collection
     */
    protected $overtimeList;

    /**
     * The headers.
     *
     * @var array
     */
    protected $headers;

    /**
     * @inheritDoc
     */
    public function __construct($overtimeList, $headers)
    {
        $this->overtimeList = $overtimeList;
        $this->headers = $headers;

    }

    /**
     * The headings.
     *
     * @return array
     */
    public function headings(): array
    {
        return $this->headers;
    }

    /**
     * @inheritDoc
     */
    public function collection()
    {
        return $this->overtimeList;
    }
    /**
     * @inheritDoc
     */
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
            BeforeSheet::class => function (BeforeSheet $event) {

            $test = $event->sheet->getDelegate()->getHighestRow();
            //dd($test);

            },
            AfterSheet::class => function (AfterSheet $event) {

            //number of rows based on array count and adding the report name on first row and the headers on the second row
                $rows = $event->sheet->getDelegate()->getHighestRow();
            //set page margins
                $event->sheet->getDelegate()->getPageMargins()->setTop(.25);
                $event->sheet->getDelegate()->getPageMargins()->setRight(0.1);
                $event->sheet->getDelegate()->getPageMargins()->setLeft(0.1);
                $event->sheet->getDelegate()->getPageMargins()->setBottom(.25);

                //set page setup for printing
                $event->sheet->getDelegate()->getPageSetup()->setFitToWidth(1);
                $event->sheet->getDelegate()->getPageSetup()->setFitToHeight(1);
                $event->sheet->getDelegate()->getPageSetup()->setHorizontalCentered(1);
                $event->sheet->getDelegate()->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);

                //setting column sizes for best printable view
                $event->sheet->getDelegate()->getColumnDimension('A')->setAutoSize(1);
                $event->sheet->getDelegate()->getColumnDimension('B')->setWidth(20);
                $event->sheet->getDelegate()->getColumnDimension('C')->setWidth(14);
                $event->sheet->getDelegate()->getColumnDimension('D')->setWidth(19);
                $event->sheet->getDelegate()->getColumnDimension('E')->setWidth(26);
                $event->sheet->getDelegate()->getColumnDimension('F')->setAutoSize(1);

                //removing gridlines
                $event->sheet->getDelegate()->setShowGridlines(0);


                //removing any borders for printing
                $event->sheet->getDelegate()->getStyle('A1:F' . $rows)->getBorders()->getAllBorders()->setBorderStyle('none');

                //creating an overview header and merging the columns, setting the font, font size, text alignment and row height
                $event->sheet->getDelegate()->setCellValue('A1', 'Overtime List');
                $event->sheet->getDelegate()->mergeCells('A1:F1')->getStyle('A1:F1')->getAlignment()->setHorizontal('center')->setVertical('top');
                $event->sheet->getDelegate()->getStyle('A1:F1')->getFont()->setSize(18)->setName('Arial');
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
                $headerCellRange = 'A2:F2';
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
                $bodyCellRange = 'A3:F' . $rows;
                $event->sheet->getDelegate()->getStyle($bodyCellRange)->applyFromArray($bodyStyleArray);
            },
        ];
    }


}
