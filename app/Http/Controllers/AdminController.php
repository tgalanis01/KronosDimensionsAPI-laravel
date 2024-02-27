<?php

namespace App\Http\Controllers;
//ini_set('max_execution_time', 300);

use Adldap\Laravel\Facades\Adldap;
use App\API\KronosDimensions;
use App\DataTables\OvertimeListDataTable;
use App\EmployeeAvailability;
use App\Exports\OvertimeListExport;
use App\Exports\OvertimeListExportView;
use App\OvertimeList;
use App\TrackUnavailable;
use Carbon\Carbon;
use Illuminate\Http\Request;
use DB;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;
use Validator;
use Illuminate\Support\Collection;

use Microsoft\Graph\Exception\GraphException;
use Microsoft\Graph\Graph;
use Microsoft\Graph\Model;
use Yajra\DataTables\Facades\DataTables;


class AdminController extends Controller
{
    public function index()
    {

    }
    public function overtimeList()
    {
        return view('overtimelist');
    }

    public function overtimeListDetails()
    {
        return view('overtimelistdetails');
    }

    public function overtimeListData(Request $request)
    {
        $kronosDimensionsAPI = new KronosDimensions;

        return DataTables::collection(collect($kronosDimensionsAPI->overtimeEqualization($request->startDate, $request->endDate)))
            ->addColumn('action', function($row){
                if(Auth::check()){
                    return '<button class="btn btn-success btn-xs mr-1" onClick="acceptedOvertime('. $row['employee_number'] .');" id="accepted-button-'. $row['employee_number'] .'"><span class="fas fa-calendar-plus" title="Mark Accepted"/></button> <button class="btn btn-warning btn-xs mr-1" onClick="unavailableOvertime('. $row['employee_number'] .');" id="unavailable-button-'.$row['employee_number'].'"><span class="fas fa-calendar-minus" title="Mark Unavailable"/></button> <button class="btn btn-danger btn-xs mr-1" onClick="refusedOvertime('. $row['employee_number'] .');" id="refused-button-'.$row['employee_number'].'"><span class="fas fa-calendar-times" title="Mark Refused"/></button>';
                }
                return '';
                //return '<button class="btn btn-success btn-xs mr-1 accepted-button" data-toggle="tooltip" data-placement="top" title="Mark Accepted" id="accepted-button" data-employee-number='.$row['employee_number'].'><span class="fas fa-calendar-minus"/></button> <button class="btn btn-warning btn-xs mr-1 unavailable-button" data-toggle="tooltip" data-placement="top" title="Mark Unavailable" id="unavailable-button" data-employee-number='.$row['employee_number'].'><span class="fas fa-calendar-minus"/></button> <button class="btn btn-danger btn-xs mr-1 refused-button" data-toggle="tooltip" data-placement="top" id="refused-button" title="Mark Refused" data-employee-number='.$row['employee_number'].'><span class="fas fa-calendar-minus"/></button>';
            })
            ->rawColumns(['action'])
            ->toJson();
    }

    public function overtimeListDataDetails(Request $request)
    {
        $kronosDimensionsAPI = new KronosDimensions;

        return DataTables::collection(collect($kronosDimensionsAPI->overtimeEqualization($request->startDate, $request->endDate)))->toJson();
    }

    public function overtimeListUnavailableStore(Request $request)
    {
        $values = $request->all();
        $values['unavailable_from'] = Carbon::parse($values['unavailable_from']);
        $values['unavailable_to'] = Carbon::parse($values['unavailable_to']);
        TrackUnavailable::create($values);

        return 0;
    }

    public function employeeAvailabilityStore(Request $request)
    {
        $values = $request->all();
        $values['review_status'] = 'PENDING';
        EmployeeAvailability::create($values);

        return 'Successfully tracked: ' . $values['employee_id'];
    }

    public function employeeAvailabilityData()
    {
        return DataTables::collection(EmployeeAvailability::all())
            ->addColumn('action', function($row){
                return '<button class="btn btn-success btn-xs mr-1" onClick="completeReviewStatus('. $row['id'] .');" id="complete-review-status-'. $row['employee_id'] .'"><span class="fas fa-check-circle" title="Mark Reviewed"/></button><button class="btn btn-warning btn-xs mr-1" onClick="pendingReviewStatus('. $row['id'] .');" id="pending-review-status-'. $row['employee_id'] .'"><span class="fas fa-minus-circle" title="Mark Pending"/></button><button class="btn btn-danger btn-xs mr-1" onClick="removeReviewStatus('. $row['id'] .');" id="remove-review-status-'. $row['employee_id'] .'"><span class="fas fa-times-circle" title="Mark Removed"/></button>';
            })
            ->rawColumns(['action'])
            ->toJson();
    }

    public function updateEmployeeAvailabilityReviewStatus(Request $request)
    {
        $values = $request->all();
        $employeeAvailability = EmployeeAvailability::find($values['id']);
        $employeeAvailability->review_status = $values['review_status'];
        $employeeAvailability->save();

        return 'Successfully updated status';

    }
    public function overtimeListUnavailableData()
    {
        return DataTables::collection(TrackUnavailable::all())->toJson();
    }

    public function kronosDimensionsTest()
    {

        $kronosDimensionsAPI = new KronosDimensions;
        //$payCodes = $kronosDimensionsAPI->retrievePayCodes();
        //dd($payCodes);
        //$paycodes = $kronosDimensionsAPI->getPaycodeData();
        //dd($paycodes);
        //dd($kronosDimensionsAPI->Client());
        //dd($kronosDimensionsAPI->getAccessToken());
        $dateTime = new Carbon();
        //$thisWeek = $dateTime->copy()->startOfWeek()->toDateTimeLocalString();
        //$twoWeeks = $dateTime->copy()->startOfWeek()->addWeek()->endOfWeek()->toDateTimeLocalString();
        $startDate = $dateTime->copy()->startOfYear()->toDateString();
        $endDate = $dateTime->copy()->startOfWeek()->toDateString();


        //$overtimeList = collect($kronosDimensionsAPI->overtimeEqualization($startDate,$endDate));
        $overtimeList = collect($kronosDimensionsAPI->overtimeEqualization($startDate,$endDate));
        dd($overtimeList);
        //$test = $kronosDimensionsAPI->getEmployees();
        //$test = $kronosDimensionsAPI->getEmployeeScheduledTimeOff();
        //$test = $kronosDimensionsAPI->getEmployeesTimeOff();
        //$test = $kronosDimensionsAPI->getEmployeeTimeOffs($thisWeek,$twoWeeks);

        //dd($test);

    }

    public function activeDirectoryImport()
    {
        Artisan::queue('adldap:import', [
            '--no-interaction',
            '--filter' => '(cn=Boling, Zachary)'
        ]);
    }

    public static function activeDirectoryTesting()
    {

        $provider = config('ldap_auth.connection');


        //$ad = Adldap::search()->select('User')->get();

        /* organizations
       $ous = Adldap::search()->ous()->get();


       $plucked = $ous->pluck('ou');
       //dd($plucked->all());
       foreach($plucked as $pluck){
           $header = implode(',', $pluck);
           //$headers = '';
           $headers[] = $header;
       }
       //$headers = implode(',', $headers);

       dd($headers);
       foreach ($ous as $ou){

           $ouAttributes[] = [
               'objectclass'           => $ou->objectclass,
               'ou'                    => $ou->ou,
               'distinguishedname'     => $ou->distinguishedname,
               'instancetype'          => $ou->instancetype,
               'whencreated'           => $ou->whencreated,
               'whenchanged'           => $ou->whenchanged,
               'usncreated'            => $ou->usncreated,
               'usnchanged'            => $ou->usnchanged,
               'name'                  => $ou->name,
               'objectguid'            => $ou->objectguid,
               'systemflags'           => $ou->systemflags,
               'objectcategory'        => $ou->objectcategory,
               'dscorepropagationdata' => $ou->dscorepropagationdata,
               'msexchversion'         => $ou->msexchversion,
           ];

       }
       $ouAttributes = array_slice($ouAttributes, 0, 30);
       dd($ouAttributes);
*/


        //$users = Adldap::search()->users()->whereContains('cn',',')->get();
        //$users = Adldap::search()->users()->where('samaccountname','=','zboling')->get();
        //$users = Adldap::search()->users()->whereContains('objectclass','*')->get();

        $search = Adldap::search();
        //$schema = $search->users()->findManyBy('department','It fiber');
        //$user = Adldap::search()->users()->where('userprincipalname','=','zboling@wgeld.org')->first();
        $user = Adldap::search()->users()->where('mailnickname', '=', 'zeb')->first();
        //dd($user->getUserPrincipalName());
        dd($user);
        //dd($schema);
        $records = $search->groups()->find('IT');

        $records = $records->getMembers();
        dd($records);
        $records = array_slice($records, 0, 5);
        dd($records);
        //$record = $search->findByDn('OU=Users,OU=IT,OU=WGELD,DC=wge,DC=org');
        //$record = $search->findByDn('CN=Boling\, Zachary,OU=Users,OU=IT,OU=WGELD,DC=wge,DC=org');

        $record = $search->findBy('department', 'It fiber')->getObjectClass();
        dd($record);

        $users = Adldap::search()->read()->where('objectclass', 'operationalPerson')->get();
        dd($users);
        /*
                //$plucked = $users->pluck('samaccountname');
                //$plucked = $users->pluck('mail');
                //$plucked = $users->pluck('userprincipalname');
                //$plucked = $users->pluck('cn');
                $plucked = $users->pluck('distinguishedname');
                //dd($plucked->all());
                foreach($plucked as $pluck){
                    if(!empty($pluck)) {
                        $header = implode(',', $pluck);
                        //$headers = '';
                        $headers[] = $header;
                    }
                }
                //$headers = implode(',', $headers);

                dd($headers);
        */
        foreach ($users as $user) {

            $userAttributes[] = [
                'objectclass' => $user->objectclass,
                'cn' => $user->cn,
                'sn' => $user->sn,
                'givenname' => $user->givenname,
                'distinguishedname' => $user->distinguishedname,
                'instancetype' => $user->instancetype,
                'whencreated' => $user->whencreated,
                'whenchanged' => $user->whenchanged,
                'displayname' => $user->displayname,
                'usnchanged' => $user->usnchanged,
                'homemta' => $user->homemta,
                'proxyaddresses' => $user->proxyaddresses,
                'homemdb' => $user->homemdb,
                'authorigbl' => $user->authorigbl,
                'mdbusedefaults' => $user->mdbusedefaults,
                'mailnickname' => $user->mailnickname,
                'name' => $user->name,
                'objectguid' => $user->objectguid,
                'useraccountcontrol' => $user->useraccountcontrol,
                'codepage' => $user->codepage,
                'countrycode' => $user->countrycode,
                'lastlogin' => $user->lastlogin,
                'pwdlastset' => $user->pwdlastset,
                'primarygroupid' => $user->primarygroupid,
                'objectsid' => $user->objectsid,
                'accountexpires' => $user->accountexpires,
                'logoncount' => $user->logoncount,
                'samaccountname' => $user->samaccountname,
                'samaccounttype' => $user->samaccounttype,
                'legacyexchangedn' => $user->legacyexchangedn,
                'userprincipalname' => $user->userprincipalname,
                'objectcategory' => $user->objectcategory,
                'dscorepropagationdata' => $user->dscorepropagationdata,
                'ms-ds-consistencyguid' => $user->cn,
                'msds-site-affinity' => $user->cn,
                'lastlogontimestamp' => $user->lastlogontimestamp,
                'msds-revealaddsas' => $user->cn,
                'msds-authenticatedatdc' => $user->cn,
                'textencodedoraddress' => $user->textencodedoraddress,
                'mail' => $user->mail,
                'msexchhomeservername' => $user->msexchhomeservername,
                'msechalobjectversion' => $user->msechalobjectversion,
                'msexchhidefromaddresslists' => $user->msexchhidefromaddresslists,
                'msexchmailboxsecuritydescriptor' => $user->msexchmailboxsecuritydescriptor,
                'msexchuseraccountcontrol' => $user->msexchuseraccountcontrol,
                'msexchmailboxguid' => $user->msexchmailboxguid,
                'msexchpoliciesincluded' => $user->msexchpoliciesincluded,
                'msexchumdtmfmap' => $user->msexchumdtmfmap,
                'msexchwhenmailboxcreated' => $user->msexchwhenmailboxcreated,
                'msexchrbacpolicylink' => $user->msexchrbacpolicylink,
                'msexchrecipientdisplaytype' => $user->msexchrecipientdisplaytype,
                'msexchversion' => $user->msexchversion,
                'msexchtextmessagingstate' => $user->msexchtextmessagingstate,
                'msexchuserbl' => $user->msexchuserbl,
                'msexchrecipienttypedetails' => $user->msexchrecipienttypedetails
            ];
            dd($userAttributes);
        }

        $userAttributes = array_slice($userAttributes, 0, 30);
        dd($userAttributes);


    }

    public function authUserEmployeeNumber()
    {
        dd(Auth::user()->employee_number);
    }

    public function extractValue($array)
    {
        if (is_array($array) && count($array) === 1) {
            foreach ($array as $key => $value) {
                $data = $value;
            }
        }
        return $data;
    }
}
