<?php

namespace App\Jobs;

use App\API\KronosDimensions;
use App\Exports\OvertimeListExport;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;

class OvertimeList_YearToStartOfWeek implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //create the default dates
        $dateTime  = Carbon::now();
        $startDate = $dateTime->copy()->startOfYear()->toDateString();
        $endDate = $dateTime->copy()->startOfWeek()->toDateString();

        //create file storage string that can be passed to the mailable to attach proper filename.
        $excelFilePath = 'OvertimeList_' . $startDate .'_to_' . $endDate .'.xlsx';
        $pdfFilePath = 'OvertimeList_' . $startDate .'_to_' . $endDate .'.pdf';

        //create an api class
        $kronosDimensionsAPI = new KronosDimensions;

        //get the overtimelist data and put it into a collection
        $overtimeList = collect($kronosDimensionsAPI->overtimeEqualization($startDate,$endDate));

        //get the keys for column headers
        foreach($overtimeList as $item){
            $keys = collect($item)->keys()->toArray();
        }

        //sort and group the data
        $overtimeList = $overtimeList->sortBy('Hours');
        $overtimeList = $overtimeList->sortBy('Job Category');
        $overtimeList = $overtimeList->groupBy('Job Category');

        //create the excel/pdf formatting and styling
        $data = new OvertimeListExport($overtimeList, $keys);

        //create and store the excel and pdf files
        Excel::store($data,$excelFilePath);
        Excel::store($data,$pdfFilePath);

        //mail the files
        $subject = 'OvertimeList_YearToStartOfWeek Weekly Report';
        $bodyMessage = 'Overtime List Year to Start of Week; Weekly Report';
        Mail::send(new \App\Mail\OvertimeList($excelFilePath,$pdfFilePath,$subject,$bodyMessage));
    }
}
