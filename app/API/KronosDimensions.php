<?php

namespace App\API;

ini_set('max_execution_time', 300);

//ini_set('memory_limit','1G');

use App\EmployeeTimeOff;
use App\TimeOffSchedule;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;
use GuzzleHttp\Middleware;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7;
use App\DeviceManagerAPI;
use App\Equipment;
use App\Location;
use DB;
use Carbon\Carbon;
use App\LocationIndex;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Artisan;
use App\Employee;

/**
 * Class KronosDimensions
 * @package App\API
 */
class KronosDimensions
{
    /*** API Connector methods ***/

    /**
     * Gather information on the request
     *
     * @param $client
     *
     * @return mixed
     */
    public function tapMiddleware($client)
    {
        // Grab the client's handler instance.
        $clientHandler = $client->getConfig('handler');
        // Create a middleware that echoes parts of the request.
        $tapMiddleware = Middleware::tap(function ($request) {
            echo $request->getHeaderLine('Content-Type');
            // application/json
            echo $request->getBody();
            // {"foo":"bar"}
        });
        return $tapMiddleware($clientHandler);
    }

    /**
     * @param $startDate
     * @param $endDate
     * @return array
     */
    public function overtimeEqualizationOLD($startDate, $endDate)
    {
        //$dateTime  = Carbon::now();   //testing
        //$startDate = $dateTime->copy()->startOfYear()->toDateString(); //testing
        //$endDate = $dateTime->copy()->startOfWeek()->toDateString(); //testing


        $bodyRequest = '{"select":[{"key":"PEOPLE_PERSON_ID","alias":"Employee ID"},{"key":"PEOPLE_PERSON_NUMBER","alias":"Employee Number"},{"key":"PEOPLE_FIRST_NAME","alias":"First Name"},{"key":"PEOPLE_LAST_NAME","alias":"Last Name"},{"key":"PEOPLE_EMAIL","alias":"Email"},{"key":"PEOPLE_USER_ACCOUNT_NAME","alias":"Username"},{"key":"EMP_COMMON_FULL_NAME","alias":"Full Name"},{"key":"PEOPLE_HOME_COST_CENTER","alias":"Cost Center"},{"key":"PEOPLE_PAYRULE","alias":"Payrule"},{"key":"PEOPLE_WORKER_TYPE","alias":"Worker Type"},{"key":"PEOPLE_HOME_LABOR_CATEGORY","alias":"Labor Category"},{"key":"PEOPLE_PHONE_NUMBER","alias":"Phone","properties":[{"key":"1","value":"Phone 1"}]},{"key":"EMP_COMMON_PRIMARY_JOB_CODE","alias":"Job Code"},{"key":"EMP_COMMON_PRIMARY_JOB","alias":"Primary Job"},{"key":"EMP_COMMON_PRIMARY_ORG","alias":"Primary Org"},{"key":"TK_GENIE_ACTUAL_TOTAL_HOURS_EXCLUDE_CORRECTIONS","alias":"Regular Hours","properties":[{"key":"114","value":"1 Regular"}]},{"key":"TK_GENIE_ACTUAL_TOTAL_HOURS_EXCLUDE_CORRECTIONS","alias":"Standby","properties":[{"key":"260","value":"Standby"}]},{"key":"TK_GENIE_ACTUAL_TOTAL_HOURS_EXCLUDE_CORRECTIONS","alias":"Overtime Hours","properties":[{"key":"155","value":"1 OT"}]},{"key":"TK_GENIE_ACTUAL_TOTAL_HOURS_EXCLUDE_CORRECTIONS","alias":"Overtime Red Hours","properties":[{"key":"111","value":"1 OT Red"}]},{"key":"TK_GENIE_ACTUAL_TOTAL_HOURS_EXCLUDE_CORRECTIONS","alias":"Overtime Premium Hours","properties":[{"key":"254","value":"1 OTP"}]},{"key":"TK_GENIE_ACTUAL_TOTAL_HOURS_EXCLUDE_CORRECTIONS","alias":"Overtime Premium Red Hours","properties":[{"key":"112","value":"1 OTP Red"}]},{"key":"TK_GENIE_ACTUAL_TOTAL_HOURS_EXCLUDE_CORRECTIONS","alias":"Doubletime Hours","properties":[{"key":"152","value":"1 DB"}]},{"key":"TK_GENIE_ACTUAL_TOTAL_HOURS_EXCLUDE_CORRECTIONS","alias":"Doubletime Red Hours","properties":[{"key":"153","value":"1 DB Red"}]},{"key":"TK_GENIE_ACTUAL_TOTAL_HOURS_EXCLUDE_CORRECTIONS","alias":"Doubletime Premium Hours","properties":[{"key":"105","value":"1 DBP"}]},{"key":"TK_GENIE_ACTUAL_TOTAL_HOURS_EXCLUDE_CORRECTIONS","alias":"Doubletime Premium Red Hours","properties":[{"key":"153","value":"1 DBP Red"}]}],"from":{"view":"EMP","employeeSet":{"hyperfind":{"qualifier":"IBEW Accruals"},"dateRange":{"startDate":"' . $startDate . 'T00:00","EndDate":"' . $endDate . 'T00:00"}}}}';
        //$bodyRequest = '{"select":[{"key":"PEOPLE_PERSON_ID","alias":"Employee ID"},{"key":"PEOPLE_PERSON_NUMBER","alias":"Employee Number"},{"key":"PEOPLE_FIRST_NAME","alias":"First Name"},{"key":"PEOPLE_LAST_NAME","alias":"Last Name"},{"key":"PEOPLE_EMAIL","alias":"Email"},{"key":"PEOPLE_USER_ACCOUNT_NAME","alias":"Username"},{"key":"EMP_COMMON_FULL_NAME","alias":"Full Name"},{"key":"PEOPLE_HOME_COST_CENTER","alias":"Cost Center"},{"key":"PEOPLE_PAYRULE","alias":"Payrule"},{"key":"PEOPLE_WORKER_TYPE","alias":"Worker Type"},{"key":"PEOPLE_HOME_LABOR_CATEGORY","alias":"Labor Category"},{"key":"PEOPLE_PHONE_NUMBER","alias":"Phone","properties":[{"key":"1","value":"Phone 1"}]},{"key":"EMP_COMMON_PRIMARY_JOB_CODE","alias":"Job Code"},{"key":"EMP_COMMON_PRIMARY_JOB","alias":"Primary Job"},{"key":"EMP_COMMON_PRIMARY_ORG","alias":"Primary Org"},{"key":"TK_GENIE_ACTUAL_TOTAL_HOURS_EXCLUDE_CORRECTIONS","alias":"Regular Hours","properties":[{"key":"114","value":"1 Regular"}]},{"key":"TK_GENIE_ACTUAL_TOTAL_HOURS_EXCLUDE_CORRECTIONS","alias":"Standby","properties":[{"key":"260","value":"Standby"}]},{"key":"TK_GENIE_ACTUAL_TOTAL_HOURS_EXCLUDE_CORRECTIONS","alias":"Overtime Hours","properties":[{"key":"155","value":"1 OT"}]},{"key":"TK_GENIE_ACTUAL_TOTAL_HOURS_EXCLUDE_CORRECTIONS","alias":"Overtime Red Hours","properties":[{"key":"111","value":"1 OT Red"}]},{"key":"TK_GENIE_ACTUAL_TOTAL_HOURS_EXCLUDE_CORRECTIONS","alias":"Overtime Premium Hours","properties":[{"key":"254","value":"1 OTP"}]},{"key":"TK_GENIE_ACTUAL_TOTAL_HOURS_EXCLUDE_CORRECTIONS","alias":"Overtime Premium Red Hours","properties":[{"key":"112","value":"1 OTP Red"}]},{"key":"TK_GENIE_ACTUAL_TOTAL_HOURS_EXCLUDE_CORRECTIONS","alias":"Doubletime Hours","properties":[{"key":"152","value":"1 DB"}]},{"key":"TK_GENIE_ACTUAL_TOTAL_HOURS_EXCLUDE_CORRECTIONS","alias":"Doubletime Red Hours","properties":[{"key":"153","value":"1 DB Red"}]},{"key":"TK_GENIE_ACTUAL_TOTAL_HOURS_EXCLUDE_CORRECTIONS","alias":"Doubletime Premium Hours","properties":[{"key":"105","value":"1 DBP"}]},{"key":"TK_GENIE_ACTUAL_TOTAL_HOURS_EXCLUDE_CORRECTIONS","alias":"Doubletime Premium Red Hours","properties":[{"key":"153","value":"1 DBP Red"}]}],"from":{"view":"EMP","employeeSet":{"hyperfind":{"qualifier":"All Employees Active (No Test Employees)"},"dateRange":{"startDate":"' . $startDate . 'T00:00","EndDate":"' . $endDate . 'T00:00"}}}}';

        $client = $this->Client();
        $url = 'commons/data/multi_read';

        $response = $client->post($url, [
            'body' => $bodyRequest
        ]);

        $bodyResponse = json_decode($response->getBody(), true);

        $childrens = data_get($bodyResponse, 'data.children');
        //$childrens = array_slice($childrens, 0, 30);
        //dd($childrens);

        foreach ($childrens as $children) {
            $attributes = data_get($children, 'attributes');
            $employeeNumber = data_get($children, 'coreEntityKey.EMP.qualifier');
            (float)$newRegularHoursTotal = 0;

            foreach ($attributes as $attribute) {

                $alias = data_get($attribute, 'alias');

                switch ($alias) {
                    case "Employee Number":
                        $employeeNumber = data_get($attribute, 'rawValue', '');
                        break;
                    case "Employee ID":
                        $employeeID = data_get($attribute, 'rawValue', '');
                        break;
                    case "Username":
                        $username = data_get($attribute, 'rawValue', '');
                        break;
                    case "First Name":
                        $firstName = data_get($attribute, 'rawValue', '');
                        break;
                    case "Last Name":
                        $lastName = data_get($attribute, 'rawValue', '');
                        break;
                    case "Full Name":
                        $employeeName = data_get($attribute, 'rawValue', '');
                        break;
                    case "Cost Center":
                        $costCenter = data_get($attribute, 'rawValue', '');
                        break;
                    case "Payrule":
                        $payrule = data_get($attribute, 'rawValue', '');
                        break;
                    case "Worker Type":
                        $workerType = data_get($attribute, 'rawValue', '');
                        break;
                    case "Labor Category":
                        $laborCategory = data_get($attribute, 'rawValue', '');
                        break;
                    case "Phone":
                        $phone = data_get($attribute, 'rawValue', '');
                        break;
                    case "Email":
                        $email = data_get($attribute, 'rawValue', '');
                        break;
                    case "Job Code":
                        $jobCode = data_get($attribute, 'rawValue', '');
                        break;
                    case "Primary Job":
                        $primaryJob = data_get($attribute, 'rawValue', '');
                        break;
                    case "Primary Org":
                        $primaryOrg = data_get($attribute, 'rawValue', '');
                        break;
                    case "Regular Hours":
                        $regularHours = data_get($attribute, 'rawValue', '');
                        break;
                    case "Overtime Adjustment":
                        $overtimeAdjustmentHours = data_get($attribute, 'rawValue', '');
                        $newRegularHoursTotal += (float)$overtimeAdjustmentHours;
                        break;
                    case "Standby":
                        $standbyHours = data_get($attribute, 'rawValue', '');
                        $newRegularHoursTotal += (float)$standbyHours;
                        break;
                    case "Overtime Hours":
                        $overtimeHours = data_get($attribute, 'rawValue', '');
                        $overtimeHoursToRegularHours = (float)$overtimeHours * 1.5;
                        $newRegularHoursTotal += $overtimeHoursToRegularHours;
                        break;
                    case "Overtime Red Hours":
                        $overtimeRedHours = data_get($attribute, 'rawValue', '');
                        $overtimeRedHoursToRegularHours = (float)$overtimeRedHours * 1.5;
                        $newRegularHoursTotal += $overtimeRedHoursToRegularHours;
                        break;
                    case "Overtime Premium Hours":
                        $overtimePremiumHours = data_get($attribute, 'rawValue', '');
                        $overtimePremiumHoursToRegularHours = (float)$overtimePremiumHours * 1.75;
                        $newRegularHoursTotal += $overtimePremiumHoursToRegularHours;
                        break;
                    case "Overtime Premium Red Hours":
                        $overtimePremiumRedHours = data_get($attribute, 'rawValue', '');
                        $overtimePremiumRedHoursToRegularHours = (float)$overtimePremiumRedHours * 1.75;
                        $newRegularHoursTotal += $overtimePremiumRedHoursToRegularHours;
                        break;
                    case "Doubletime Hours":
                        $doubletimeHours = data_get($attribute, 'rawValue', '');
                        $doubletimeHoursToRegularHours = (float)$doubletimeHours * 2;
                        $newRegularHoursTotal += $doubletimeHoursToRegularHours;
                        break;
                    case "Doubletime Red Hours":
                        $doubletimeRedHours = data_get($attribute, 'rawValue', '');
                        $doubletimeRedHoursToRegularHours = (float)$doubletimeRedHours * 2;
                        $newRegularHoursTotal += $doubletimeRedHoursToRegularHours;
                        break;
                    case "Doubletime Premium Hours":
                        $doubletimePremiumHours = data_get($attribute, 'rawValue', '');
                        $doubletimePremiumHoursToRegularHours = (float)$doubletimePremiumHours * 2.25;
                        $newRegularHoursTotal += $doubletimePremiumHoursToRegularHours;
                        break;
                    case "Doubletime Premium Red Hours":
                        $doubletimePremiumRedHours = data_get($attribute, 'rawValue', '');
                        $doubletimePremiumRedHoursToRegularHours = (float)$doubletimePremiumRedHours * 2.25;
                        //$newRegularHoursTotal += $doubletimePremiumRedHoursToRegularHours;
                        break;
                }
            }
            $employees[] = [
                'employee_id' => $employeeID,
                'employee_number' => $employeeNumber,
                'employee_name' => $employeeName,
                'username' => $username,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $email
            ];

            if (!empty($primaryOrg)) {
                $primaryOrgSplit = explode('/', $primaryOrg);

                $company = $primaryOrgSplit[0];
                $division = $primaryOrgSplit[1];
                $department = $primaryOrgSplit[2];
                $jobCategory = $primaryOrgSplit[3];
            }
            //used for datatable
            $newAttributes[] = [
                'Number' => $employeeNumber,
                'Name' => $employeeName,
                //'labor_category'            => $laborCategory,
                'Phone' => $phone,
                //'primary_job'               => $primaryJob,
                //'company'                   => $company,
                //'division'                  => $division,
                'Department' => $department,
                'Job Category' => $jobCategory,
                'Hours' => $this->floorToFraction((float)$newRegularHoursTotal),
                'regular_hours'                                 => $this->floorToFraction((float)$regularHours),
                'standby_hours'                                 => $this->floorToFraction((float)$standbyHours),
                'overtime_hours'                                => $this->floorToFraction((float)$overtimeHours),
                'overtime_red_hours'                            => $this->floorToFraction((float)$overtimeRedHours),
                'overtime_premium_hours'                        => $this->floorToFraction((float)$overtimePremiumHours),
                'overtime_premium_red_hours'                    => $this->floorToFraction((float)$overtimePremiumRedHours),
                'doubletime_hours'                              => $this->floorToFraction((float)$doubletimeHours),
                'doubletime_red_hours'                          => $this->floorToFraction((float)$doubletimeRedHours),
                'doubletime_premium_hours'                      => $this->floorToFraction((float)$doubletimePremiumHours),
                'doubletime_premium_red_hours'                  => $this->floorToFraction((float)$doubletimePremiumRedHours),
                'overtime_hours_to_regular_hours'               => $this->floorToFraction((float)$overtimeHoursToRegularHours),
                'overtime_red_hours_to_regular_hours'           => $this->floorToFraction((float)$overtimeRedHoursToRegularHours),
                'overtime_premium_hours_to_regular_hours'       => $this->floorToFraction((float)$overtimePremiumHoursToRegularHours),
                'overtime_premium_red_hours_to_regular_hours'   => $this->floorToFraction((float)$overtimePremiumRedHoursToRegularHours),
                'doubletime_hours_to_regular_hours'             => $this->floorToFraction((float)$doubletimeHoursToRegularHours),
                'doubletime_red_hours_To_regular_hours'         => $this->floorToFraction((float)$doubletimeRedHoursToRegularHours),
                'doubletime_premium_hours_to_regular_hours'     => $this->floorToFraction((float)$doubletimePremiumHoursToRegularHours),
                'doubletime_premium_red_hours_to_regular_hours' => $this->floorToFraction((float)$doubletimePremiumRedHoursToRegularHours)
            ];


            /* used for putting data to csv
            $newAttributes[] = [
                'employee_number'                               => $employeeNumber,
                'employee_name'                                 => $employeeName,
                'pay_rule'                                      => $payrule,
                'worker_type'                                   => $workerType,
                'labor_category'                                => $laborCategory,
                'phone'                                         => $phone,
                'job_code'                                      => $jobCode,
                'primary_job'                                   => $primaryJob,
                'primary_org'                                   => $primaryOrg,
                'regular_hours'                                 => $this->floorToFraction((float)$regularHours),
                'overtime_hours'                                => $this->floorToFraction((float)$overtimeHours),
                'overtime_red_hours'                            => $this->floorToFraction((float)$overtimeRedHours),
                'overtime_premium_hours'                        => $this->floorToFraction((float)$overtimePremiumHours),
                'overtime_premium_red_hours'                    => $this->floorToFraction((float)$overtimePremiumRedHours),
                'doubletime_hours'                              => $this->floorToFraction((float)$doubletimeHours),
                'doubletime_red_hours'                          => $this->floorToFraction((float)$doubletimeRedHours),
                'doubletime_premium_hours'                      => $this->floorToFraction((float)$doubletimePremiumHours),
                'doubletime_premium_red_hours'                  => $this->floorToFraction((float)$doubletimePremiumRedHours),
                'overtime_hours_to_regular_hours'               => $this->floorToFraction((float)$overtimeHoursToRegularHours),
                'overtime_red_hours_to_regular_hours'           => $this->floorToFraction((float)$overtimeRedHoursToRegularHours),
                'overtime_premium_hours_to_regular_hours'       => $this->floorToFraction((float)$overtimePremiumHoursToRegularHours),
                'overtime_premium_red_hours_to_regular_Hours'   => $this->floorToFraction((float)$overtimePremiumRedHoursToRegularHours),
                'doubletime_hours_to_regular_hours'             => $this->floorToFraction((float)$doubletimeHoursToRegularHours),
                'doubletime_red_hours_To_regular_hours'         => $this->floorToFraction((float)$doubletimeRedHoursToRegularHours),
                'doubletime_premium_hours_to_regular_hours'     => $this->floorToFraction((float)$doubletimePremiumHoursToRegularHours),
                'doubletime_premium_red_hours_to_regular_hours' => $this->floorToFraction((float)$doubletimePremiumRedHoursToRegularHours),
                'new_regular_total_hours'                       => $this->floorToFraction((float)$newRegularHoursTotal),
            ];
            */
        }

        /*
        // creates csv and insert data
        $filename = 'Z:\IT_Development\Processes\KronosDimensions-API Integrations\OvertimeListConverted.csv';
        $header = ['employeeNumber', 'employeeName', 'costCenter', 'payrule', 'workerType', 'laborCategory', 'phone', 'jobCode', 'primaryJob', 'primaryOrg', 'regularHours', 'overtimeHours', 'overtimeRedHours', 'overtimePremiumHours', 'overtimePremiumRedHours', 'doubletimeHours', 'doubletimeRedHours', 'doubletimePremiumHours', 'doubletimePremiumRedHours', 'overtimeHoursToRegularHours', 'overtimeRedHoursToRegularHours', 'overtimePremiumHoursToRegularHours', 'overtimePremiumRedHoursToRegularHours', 'doubletimeHoursToRegularHours', 'doubletimeRedHoursToRegularHours', 'doubletimePremiumHoursToRegularHours', 'doubletimePremiumRedHoursToRegularHours', 'newRegularTotalHours'];

        $fp = fopen($filename, 'w');

        fputcsv($fp, $header);
        foreach ($newAttributes as $newAttribute) {
            fputcsv($fp, $newAttribute);
        }
        fclose($fp);

        */
        //dd($newAttributes); //testing
        return $newAttributes;
    }

    /**
     * @param $startDate
     * @param $endDate
     * @return array
     */
    public function overtimeEqualization($startDate, $endDate)
    {
        //$dateTime  = Carbon::now();   //testing
        //$startDate = $dateTime->copy()->startOfYear()->toDateString(); //testing
        //$endDate = $dateTime->copy()->startOfWeek()->toDateString(); //testing

        $bodyRequest = '{"select":[{"key":"PEOPLE_PERSON_ID","alias":"employee_id"},{"key":"PEOPLE_PERSON_NUMBER","alias":"employee_number"},{"key":"PEOPLE_FIRST_NAME","alias":"first_name"},{"key":"PEOPLE_LAST_NAME","alias":"last_name"},{"key":"PEOPLE_EMAIL","alias":"email"},{"key":"PEOPLE_USER_ACCOUNT_NAME","alias":"username"},{"key":"EMP_COMMON_FULL_NAME","alias":"full_name"},{"key":"PEOPLE_PAYRULE","alias":"payrule"},{"key":"PEOPLE_HOME_LABOR_CATEGORY","alias":"labor_category"},{"key":"PEOPLE_PHONE_NUMBER","alias":"phone","properties":[{"key":"1","value":"Phone 1"}]},{"key":"EMP_COMMON_PRIMARY_JOB","alias":"primary_job"},{"key":"EMP_COMMON_PRIMARY_ORG","alias":"primary_organization"},{"key":"TK_GENIE_ACTUAL_TOTAL_HOURS_EXCLUDE_CORRECTIONS","alias":"regular","properties":[{"key":"114","value":"1 Regular"}]},{"key":"TK_GENIE_ACTUAL_TOTAL_HOURS_EXCLUDE_CORRECTIONS","alias":"standby","properties":[{"key":"260","value":"Standby"}]},{"key":"TK_GENIE_ACTUAL_TOTAL_HOURS_EXCLUDE_CORRECTIONS","alias":"standby_additional_covid","properties":[{"key":"851","value":"Standby Add\'l Covid"}]},{"key":"TK_GENIE_ACTUAL_TOTAL_HOURS_EXCLUDE_CORRECTIONS","alias":"overtime","properties":[{"key":"155","value":"1 OT"}]},{"key":"TK_GENIE_ACTUAL_TOTAL_HOURS_EXCLUDE_CORRECTIONS","alias":"overtime_red","properties":[{"key":"111","value":"1 OT Red"}]},{"key":"TK_GENIE_ACTUAL_TOTAL_HOURS_EXCLUDE_CORRECTIONS","alias":"overtime_premium","properties":[{"key":"254","value":"1 OTP"}]},{"key":"TK_GENIE_ACTUAL_TOTAL_HOURS_EXCLUDE_CORRECTIONS","alias":"overtime_premium_red","properties":[{"key":"112","value":"1 OTP Red"}]},{"key":"TK_GENIE_ACTUAL_TOTAL_HOURS_EXCLUDE_CORRECTIONS","alias":"doubletime","properties":[{"key":"152","value":"1 DB"}]},{"key":"TK_GENIE_ACTUAL_TOTAL_HOURS_EXCLUDE_CORRECTIONS","alias":"doubletime_red","properties":[{"key":"153","value":"1 DB Red"}]},{"key":"TK_GENIE_ACTUAL_TOTAL_HOURS_EXCLUDE_CORRECTIONS","alias":"doubletime_premium","properties":[{"key":"105","value":"1 DBP"}]},{"key":"TK_GENIE_ACTUAL_TOTAL_HOURS_EXCLUDE_CORRECTIONS","alias":"doubletime_premium_red","properties":[{"key":"153","value":"1 DBP Red"}]}],"from":{"view":"EMP","employeeSet":{"hyperfind":{"qualifier":"IBEW Accruals"},"dateRange":{"startDate":"' . $startDate . 'T00:00","EndDate":"' . $endDate . 'T00:00"}}}}';

        //working on the new unavailable time
        //$bodyRequest = '{"select":[{"key":"PEOPLE_PERSON_ID","alias":"employee_id"},{"key":"PEOPLE_PERSON_NUMBER","alias":"employee_number"},{"key":"PEOPLE_FIRST_NAME","alias":"first_name"},{"key":"PEOPLE_LAST_NAME","alias":"last_name"},{"key":"PEOPLE_EMAIL","alias":"email"},{"key":"PEOPLE_USER_ACCOUNT_NAME","alias":"username"},{"key":"EMP_COMMON_FULL_NAME","alias":"full_name"},{"key":"PEOPLE_PAYRULE","alias":"payrule"},{"key":"PEOPLE_HOME_LABOR_CATEGORY","alias":"labor_category"},{"key":"PEOPLE_PHONE_NUMBER","alias":"phone","properties":[{"key":"1","value":"Phone 1"}]},{"key":"EMP_COMMON_PRIMARY_JOB","alias":"primary_job"},{"key":"EMP_COMMON_PRIMARY_ORG","alias":"primary_organization"},{"key":"TK_GENIE_ACTUAL_TOTAL_HOURS_EXCLUDE_CORRECTIONS","alias":"regular","properties":[{"key":"114","value":"1 Regular"}]},{"key":"TK_GENIE_ACTUAL_TOTAL_HOURS_EXCLUDE_CORRECTIONS","alias":"standby","properties":[{"key":"260","value":"Standby"}]},{"key":"TK_GENIE_ACTUAL_TOTAL_HOURS_EXCLUDE_CORRECTIONS","alias":"standby_additional_covid","properties":[{"key":"851","value":"Standby Add\'l Covid"}]},{"key":"TK_GENIE_ACTUAL_TOTAL_HOURS_EXCLUDE_CORRECTIONS","alias":"unavailable","properties":[{"key":"851","value":"UNAV"}]},{"key":"TK_GENIE_ACTUAL_TOTAL_HOURS_EXCLUDE_CORRECTIONS","alias":"overtime","properties":[{"key":"155","value":"1 OT"}]},{"key":"TK_GENIE_ACTUAL_TOTAL_HOURS_EXCLUDE_CORRECTIONS","alias":"overtime_red","properties":[{"key":"111","value":"1 OT Red"}]},{"key":"TK_GENIE_ACTUAL_TOTAL_HOURS_EXCLUDE_CORRECTIONS","alias":"overtime_premium","properties":[{"key":"254","value":"1 OTP"}]},{"key":"TK_GENIE_ACTUAL_TOTAL_HOURS_EXCLUDE_CORRECTIONS","alias":"overtime_premium_red","properties":[{"key":"112","value":"1 OTP Red"}]},{"key":"TK_GENIE_ACTUAL_TOTAL_HOURS_EXCLUDE_CORRECTIONS","alias":"doubletime","properties":[{"key":"152","value":"1 DB"}]},{"key":"TK_GENIE_ACTUAL_TOTAL_HOURS_EXCLUDE_CORRECTIONS","alias":"doubletime_red","properties":[{"key":"153","value":"1 DB Red"}]},{"key":"TK_GENIE_ACTUAL_TOTAL_HOURS_EXCLUDE_CORRECTIONS","alias":"doubletime_premium","properties":[{"key":"105","value":"1 DBP"}]},{"key":"TK_GENIE_ACTUAL_TOTAL_HOURS_EXCLUDE_CORRECTIONS","alias":"doubletime_premium_red","properties":[{"key":"153","value":"1 DBP Red"}]}],"from":{"view":"EMP","employeeSet":{"hyperfind":{"qualifier":"IBEW Accruals"},"dateRange":{"startDate":"' . $startDate . 'T00:00","EndDate":"' . $endDate . 'T00:00"}}}}';


        //$bodyRequest = '{"select":[{"key":"PEOPLE_PERSON_ID","alias":"employee_id"},{"key":"PEOPLE_PERSON_NUMBER","alias":"employee_number"},{"key":"PEOPLE_FIRST_NAME","alias":"first_name"},{"key":"PEOPLE_LAST_NAME","alias":"last_name"},{"key":"PEOPLE_EMAIL","alias":"email"},{"key":"PEOPLE_USER_ACCOUNT_NAME","alias":"username"},{"key":"EMP_COMMON_FULL_NAME","alias":"full_name"},{"key":"PEOPLE_PAYRULE","alias":"payrule"},{"key":"PEOPLE_HOME_LABOR_CATEGORY","alias":"labor_category"},{"key":"PEOPLE_PHONE_NUMBER","alias":"phone","properties":[{"key":"1","value":"Phone 1"}]},{"key":"EMP_COMMON_PRIMARY_JOB","alias":"primary_job"},{"key":"EMP_COMMON_PRIMARY_ORG","alias":"primary_organization"},{"key":"TK_GENIE_ACTUAL_TOTAL_HOURS_EXCLUDE_CORRECTIONS","alias":"regular","properties":[{"key":"114","value":"1 Regular"}]},{"key":"TK_GENIE_ACTUAL_TOTAL_HOURS_EXCLUDE_CORRECTIONS","alias":"standby","properties":[{"key":"260","value":"Standby"}]},{"key":"TK_GENIE_ACTUAL_TOTAL_HOURS_EXCLUDE_CORRECTIONS","alias":"standby_additional_covid","properties":[{"value":"Standby Add\'l Covid"}]},{"key":"TK_GENIE_ACTUAL_TOTAL_HOURS_EXCLUDE_CORRECTIONS","alias":"overtime","properties":[{"key":"155","value":"1 OT"}]},{"key":"TK_GENIE_ACTUAL_TOTAL_HOURS_EXCLUDE_CORRECTIONS","alias":"overtime_red","properties":[{"key":"111","value":"1 OT Red"}]},{"key":"TK_GENIE_ACTUAL_TOTAL_HOURS_EXCLUDE_CORRECTIONS","alias":"overtime_premium","properties":[{"key":"254","value":"1 OTP"}]},{"key":"TK_GENIE_ACTUAL_TOTAL_HOURS_EXCLUDE_CORRECTIONS","alias":"overtime_premium_red","properties":[{"key":"112","value":"1 OTP Red"}]},{"key":"TK_GENIE_ACTUAL_TOTAL_HOURS_EXCLUDE_CORRECTIONS","alias":"doubletime","properties":[{"key":"152","value":"1 DB"}]},{"key":"TK_GENIE_ACTUAL_TOTAL_HOURS_EXCLUDE_CORRECTIONS","alias":"doubletime_red","properties":[{"key":"153","value":"1 DB Red"}]},{"key":"TK_GENIE_ACTUAL_TOTAL_HOURS_EXCLUDE_CORRECTIONS","alias":"doubletime_premium","properties":[{"key":"105","value":"1 DBP"}]},{"key":"TK_GENIE_ACTUAL_TOTAL_HOURS_EXCLUDE_CORRECTIONS","alias":"doubletime_premium_red","properties":[{"key":"153","value":"1 DBP Red"}]}],"from":{"view":"EMP","employeeSet":{"hyperfind":{"qualifier":"IBEW Accruals"},"dateRange":{"startDate":"' . $startDate . 'T00:00","EndDate":"' . $endDate . 'T00:00"}}}}';
        //$bodyRequest = '{"select":[{"key":"PEOPLE_PERSON_ID","alias":"employee_id"},{"key":"PEOPLE_PERSON_NUMBER","alias":"employee_number"},{"key":"PEOPLE_FIRST_NAME","alias":"first_name"},{"key":"PEOPLE_LAST_NAME","alias":"last_name"},{"key":"PEOPLE_EMAIL","alias":"email"},{"key":"PEOPLE_USER_ACCOUNT_NAME","alias":"username"},{"key":"EMP_COMMON_FULL_NAME","alias":"full_name"},{"key":"PEOPLE_PAYRULE","alias":"payrule"},{"key":"PEOPLE_HOME_LABOR_CATEGORY","alias":"labor_category"},{"key":"PEOPLE_PHONE_NUMBER","alias":"phone","properties":[{"key":"1","value":"Phone 1"}]},{"key":"EMP_COMMON_PRIMARY_JOB","alias":"primary_job"},{"key":"EMP_COMMON_PRIMARY_ORG","alias":"primary_organization"},{"key":"TK_GENIE_ACTUAL_TOTAL_HOURS_EXCLUDE_CORRECTIONS","alias":"regular","properties":[{"key":"114","value":"1 Regular"}]},{"key":"TK_GENIE_ACTUAL_TOTAL_HOURS_EXCLUDE_CORRECTIONS","alias":"standby","properties":[{"key":"260","value":"Standby"}]},{"key":"TK_GENIE_ACTUAL_TOTAL_HOURS_EXCLUDE_CORRECTIONS","alias":"overtime","properties":[{"key":"155","value":"1 OT"}]},{"key":"TK_GENIE_ACTUAL_TOTAL_HOURS_EXCLUDE_CORRECTIONS","alias":"overtime_red","properties":[{"key":"111","value":"1 OT Red"}]},{"key":"TK_GENIE_ACTUAL_TOTAL_HOURS_EXCLUDE_CORRECTIONS","alias":"overtime_premium","properties":[{"key":"254","value":"1 OTP"}]},{"key":"TK_GENIE_ACTUAL_TOTAL_HOURS_EXCLUDE_CORRECTIONS","alias":"overtime_premium_red","properties":[{"key":"112","value":"1 OTP Red"}]},{"key":"TK_GENIE_ACTUAL_TOTAL_HOURS_EXCLUDE_CORRECTIONS","alias":"doubletime","properties":[{"key":"152","value":"1 DB"}]},{"key":"TK_GENIE_ACTUAL_TOTAL_HOURS_EXCLUDE_CORRECTIONS","alias":"doubletime_red","properties":[{"key":"153","value":"1 DB Red"}]},{"key":"TK_GENIE_ACTUAL_TOTAL_HOURS_EXCLUDE_CORRECTIONS","alias":"doubletime_premium","properties":[{"key":"105","value":"1 DBP"}]},{"key":"TK_GENIE_ACTUAL_TOTAL_HOURS_EXCLUDE_CORRECTIONS","alias":"doubletime_premium_red","properties":[{"key":"153","value":"1 DBP Red"}]}],"from":{"view":"EMP","employeeSet":{"hyperfind":{"qualifier":"IBEW Accruals"},"dateRange":{"startDate":"' . $startDate . 'T00:00","EndDate":"' . $endDate . 'T00:00"}}}}';
        //$bodyRequest = '{"select":[{"key":"PEOPLE_PERSON_ID","alias":"Employee ID"},{"key":"PEOPLE_PERSON_NUMBER","alias":"Employee Number"},{"key":"PEOPLE_FIRST_NAME","alias":"First Name"},{"key":"PEOPLE_LAST_NAME","alias":"Last Name"},{"key":"PEOPLE_EMAIL","alias":"Email"},{"key":"PEOPLE_USER_ACCOUNT_NAME","alias":"Username"},{"key":"EMP_COMMON_FULL_NAME","alias":"Full Name"},{"key":"PEOPLE_HOME_COST_CENTER","alias":"Cost Center"},{"key":"PEOPLE_PAYRULE","alias":"Payrule"},{"key":"PEOPLE_WORKER_TYPE","alias":"Worker Type"},{"key":"PEOPLE_HOME_LABOR_CATEGORY","alias":"Labor Category"},{"key":"PEOPLE_PHONE_NUMBER","alias":"Phone","properties":[{"key":"1","value":"Phone 1"}]},{"key":"EMP_COMMON_PRIMARY_JOB_CODE","alias":"Job Code"},{"key":"EMP_COMMON_PRIMARY_JOB","alias":"Primary Job"},{"key":"EMP_COMMON_PRIMARY_ORG","alias":"Primary Org"},{"key":"TK_GENIE_ACTUAL_TOTAL_HOURS_EXCLUDE_CORRECTIONS","alias":"Regular Hours","properties":[{"key":"114","value":"1 Regular"}]},{"key":"TK_GENIE_ACTUAL_TOTAL_HOURS_EXCLUDE_CORRECTIONS","alias":"Standby","properties":[{"key":"260","value":"Standby"}]},{"key":"TK_GENIE_ACTUAL_TOTAL_HOURS_EXCLUDE_CORRECTIONS","alias":"Overtime Hours","properties":[{"key":"155","value":"1 OT"}]},{"key":"TK_GENIE_ACTUAL_TOTAL_HOURS_EXCLUDE_CORRECTIONS","alias":"Overtime Red Hours","properties":[{"key":"111","value":"1 OT Red"}]},{"key":"TK_GENIE_ACTUAL_TOTAL_HOURS_EXCLUDE_CORRECTIONS","alias":"Overtime Premium Hours","properties":[{"key":"254","value":"1 OTP"}]},{"key":"TK_GENIE_ACTUAL_TOTAL_HOURS_EXCLUDE_CORRECTIONS","alias":"Overtime Premium Red Hours","properties":[{"key":"112","value":"1 OTP Red"}]},{"key":"TK_GENIE_ACTUAL_TOTAL_HOURS_EXCLUDE_CORRECTIONS","alias":"Doubletime Hours","properties":[{"key":"152","value":"1 DB"}]},{"key":"TK_GENIE_ACTUAL_TOTAL_HOURS_EXCLUDE_CORRECTIONS","alias":"Doubletime Red Hours","properties":[{"key":"153","value":"1 DB Red"}]},{"key":"TK_GENIE_ACTUAL_TOTAL_HOURS_EXCLUDE_CORRECTIONS","alias":"Doubletime Premium Hours","properties":[{"key":"105","value":"1 DBP"}]},{"key":"TK_GENIE_ACTUAL_TOTAL_HOURS_EXCLUDE_CORRECTIONS","alias":"Doubletime Premium Red Hours","properties":[{"key":"153","value":"1 DBP Red"}]}],"from":{"view":"EMP","employeeSet":{"hyperfind":{"qualifier":"IBEW Accruals"},"dateRange":{"startDate":"' . $startDate . 'T00:00","EndDate":"' . $endDate . 'T00:00"}}}}';
        //$bodyRequest = '{"select":[{"key":"PEOPLE_PERSON_ID","alias":"Employee ID"},{"key":"PEOPLE_PERSON_NUMBER","alias":"Employee Number"},{"key":"PEOPLE_FIRST_NAME","alias":"First Name"},{"key":"PEOPLE_LAST_NAME","alias":"Last Name"},{"key":"PEOPLE_EMAIL","alias":"Email"},{"key":"PEOPLE_USER_ACCOUNT_NAME","alias":"Username"},{"key":"EMP_COMMON_FULL_NAME","alias":"Full Name"},{"key":"PEOPLE_HOME_COST_CENTER","alias":"Cost Center"},{"key":"PEOPLE_PAYRULE","alias":"Payrule"},{"key":"PEOPLE_WORKER_TYPE","alias":"Worker Type"},{"key":"PEOPLE_HOME_LABOR_CATEGORY","alias":"Labor Category"},{"key":"PEOPLE_PHONE_NUMBER","alias":"Phone","properties":[{"key":"1","value":"Phone 1"}]},{"key":"EMP_COMMON_PRIMARY_JOB_CODE","alias":"Job Code"},{"key":"EMP_COMMON_PRIMARY_JOB","alias":"Primary Job"},{"key":"EMP_COMMON_PRIMARY_ORG","alias":"Primary Org"},{"key":"TK_GENIE_ACTUAL_TOTAL_HOURS_EXCLUDE_CORRECTIONS","alias":"Regular Hours","properties":[{"key":"114","value":"1 Regular"}]},{"key":"TK_GENIE_ACTUAL_TOTAL_HOURS_EXCLUDE_CORRECTIONS","alias":"Standby","properties":[{"key":"260","value":"Standby"}]},{"key":"TK_GENIE_ACTUAL_TOTAL_HOURS_EXCLUDE_CORRECTIONS","alias":"Overtime Hours","properties":[{"key":"155","value":"1 OT"}]},{"key":"TK_GENIE_ACTUAL_TOTAL_HOURS_EXCLUDE_CORRECTIONS","alias":"Overtime Red Hours","properties":[{"key":"111","value":"1 OT Red"}]},{"key":"TK_GENIE_ACTUAL_TOTAL_HOURS_EXCLUDE_CORRECTIONS","alias":"Overtime Premium Hours","properties":[{"key":"254","value":"1 OTP"}]},{"key":"TK_GENIE_ACTUAL_TOTAL_HOURS_EXCLUDE_CORRECTIONS","alias":"Overtime Premium Red Hours","properties":[{"key":"112","value":"1 OTP Red"}]},{"key":"TK_GENIE_ACTUAL_TOTAL_HOURS_EXCLUDE_CORRECTIONS","alias":"Doubletime Hours","properties":[{"key":"152","value":"1 DB"}]},{"key":"TK_GENIE_ACTUAL_TOTAL_HOURS_EXCLUDE_CORRECTIONS","alias":"Doubletime Red Hours","properties":[{"key":"153","value":"1 DB Red"}]},{"key":"TK_GENIE_ACTUAL_TOTAL_HOURS_EXCLUDE_CORRECTIONS","alias":"Doubletime Premium Hours","properties":[{"key":"105","value":"1 DBP"}]},{"key":"TK_GENIE_ACTUAL_TOTAL_HOURS_EXCLUDE_CORRECTIONS","alias":"Doubletime Premium Red Hours","properties":[{"key":"153","value":"1 DBP Red"}]}],"from":{"view":"EMP","employeeSet":{"hyperfind":{"qualifier":"All Employees Active (No Test Employees)"},"dateRange":{"startDate":"' . $startDate . 'T00:00","EndDate":"' . $endDate . 'T00:00"}}}}';

        $client = $this->Client();
        $url = 'commons/data/multi_read';

        $response = $client->post($url, [
            'body' => $bodyRequest
        ]);

        $bodyResponse = json_decode($response->getBody(), true);

        $childrens = data_get($bodyResponse, 'data.children');
        //$childrens = array_slice($childrens, 0, 30);
        //dd($childrens);

        $employees = $this->cleanData($childrens);
        //dd($employees);

        return $employees;
    }

    public function cleanData(array $childrens)
    {

        foreach ($childrens as $children) {
            $attributeTemplateArray = [
                'primary_organization' => '',
                'company' => '',
                'division' => '',
                'department' => '',
                'job_category' => '',
                'labor_category' => '',
                'payrule' => '',
                'primary_job' => '',
                'job_code' => '',
                'employee_id' => '',
                'employee_number' => '',
                'full_name' => '',
                'first_name' => '',
                'last_name' => '',
                'username' => '',
                'phone' => '',
                'email' => '',
                'regular' => '',
                'standby' => '',
                'overtime' => '',
                'overtime_red' => '',
                'overtime_premium' => '',
                'overtime_premium_red' => '',
                'doubletime' => '',
                'doubletime_red' => '',
                'doubletime_premium' => '',
                'doubletime_premium_red' => '',
                'overtime_to_regular' => '',
                'overtime_red_to_regular' => '',
                'overtime_premium_to_regular' => '',
                'overtime_premium_red_to_regular' => '',
                'doubletime_to_regular' => '',
                'doubletime_red_To_regular' => '',
                'doubletime_premium_to_regular' => '',
                'doubletime_premium_red_to_regular' => '',
                'overtime_equalized' => '',
                'red_time_hours' => '',
                'unavailable_hours' => ''
                //'cost_center' => '',
                //'worker_type' => '',
            ];
            $attributes = collect(data_get($children, 'attributes'));

            $mapped = $attributes->mapWithKeys(function ($item, $key) {
                return [$item['alias'] => $item['rawValue'] ?? ''];
            });
            //dd($mapped->all());
            $mapped = array_merge($attributeTemplateArray,$mapped->all());

            $primaryOrganizationSplit = explode('/', $mapped['primary_organization']);

            $primaryOrganizations = [
                'company'       => $primaryOrganizationSplit[0],
                'division'      => $primaryOrganizationSplit[1],
                'department'    => $primaryOrganizationSplit[2],
                'job_category'  => $primaryOrganizationSplit[3]
            ];
            $mapped = array_merge($mapped,$primaryOrganizations);

            if(!isset($mapped['standby_additional_covid'])){
                $mapped['standby_additional_covid'] = '';
            }
            if(!isset($mapped['unavailable'])){
                $mapped['unavailable'] = '';
            }

            $standbyHours                               = (float)$mapped['standby'] * 1;
            $standbyCovidHours                          = (float)$mapped['standby_additional_covid'] * 1;
            $overtimeHoursToRegularHours                = (float)$mapped['overtime'] * 1.5;
            $overtimeRedHoursToRegularHours             = (float)$mapped['overtime_red'] * 1.5;
            $overtimePremiumHoursToRegularHours         = (float)$mapped['overtime_premium'] * 1.75;
            $overtimePremiumRedHoursToRegularHours      = (float)$mapped['overtime_premium_red'] * 1.75;
            $doubletimeHoursToRegularHours              = (float)$mapped['doubletime'] * 2;
            $doubletimeRedHoursToRegularHours           = (float)$mapped['doubletime_red'] * 2;
            $doubletimePremiumHoursToRegularHours       = (float)$mapped['doubletime_premium'] * 2.25;
            $doubletimePremiumRedHoursToRegularHours    = (float)$mapped['doubletime_premium_red'] * 2.25;
            $unavailableHoursToRegularHours             = (float)$mapped['unavailable'] * 1;

            $redTimeHours = collect([
                $overtimeRedHoursToRegularHours,
                $overtimePremiumRedHoursToRegularHours,
                $doubletimePremiumRedHoursToRegularHours
            ])->sum();

            $unavailableHours = collect([
                $unavailableHoursToRegularHours
            ])->sum();

            $overtimeEqualized = collect([
                $standbyHours,
                $standbyCovidHours,
                $overtimeHoursToRegularHours,
                $overtimeRedHoursToRegularHours,
                $overtimePremiumHoursToRegularHours,
                $overtimePremiumRedHoursToRegularHours,
                $doubletimeHoursToRegularHours,
                $doubletimeRedHoursToRegularHours,
                $doubletimePremiumHoursToRegularHours,
                $doubletimePremiumRedHoursToRegularHours
            ])->sum();

            $calculations = [
                'overtime_to_regular'               => $this->floorToFraction((float)$overtimeHoursToRegularHours),
                'overtime_red_to_regular'           => $this->floorToFraction((float)$overtimeRedHoursToRegularHours),
                'overtime_premium_to_regular'       => $this->floorToFraction((float)$overtimePremiumHoursToRegularHours),
                'overtime_premium_red_to_regular'   => $this->floorToFraction((float)$overtimePremiumRedHoursToRegularHours),
                'doubletime_to_regular'             => $this->floorToFraction((float)$doubletimeHoursToRegularHours),
                'doubletime_red_To_regular'         => $this->floorToFraction((float)$doubletimeRedHoursToRegularHours),
                'doubletime_premium_to_regular'     => $this->floorToFraction((float)$doubletimePremiumHoursToRegularHours),
                'doubletime_premium_red_to_regular' => $this->floorToFraction((float)$doubletimePremiumRedHoursToRegularHours),
                'overtime_equalized'                => $this->floorToFraction((float)$overtimeEqualized),
                'red_time_hours'                    => $this->floorToFraction((float)$redTimeHours),
                'unavailable_hours'                 => $this->floorToFraction((float)$unavailableHours)
            ];

            $cleanedData[] = array_merge($mapped,$calculations);


        }
        return $cleanedData;
    }
    /**
     * @return Client
     */
    public function Client()
    {

        $accessToken = $this->getAccessToken();

        $client = new Client([
            'base_uri' => 'https://westfieldgaselect-nosso.prd.mykronos.com/api/v1/',
            'headers' => [
                'Content-Type' => 'application/json',
                'appkey' => 'fy8Ifpyr3fASgARUb9jeHAozJ6C9p61K',
                'Authorization' => $accessToken
            ],
            'verify' => false
        ]);
        return $client;
    }

    /**
     * @return mixed|string
     */
    public function getAccessToken()
    {
        $client = new Client([
            'base_uri' => 'https://westfieldgaselect-nosso.prd.mykronos.com/api/',
            'headers' => [
                'Content-Type' => 'application/x-www-form-urlencoded',
                'appkey' => 'fy8Ifpyr3fASgARUb9jeHAozJ6C9p61K',
            ],
            'verify' => false
        ]);

        $url = 'authentication/access_token';

        try {
            $response = $client->post($url, [
                'form_params' => [
                    'username' => 'appdev',
                    'password' => 'AppDev@2023',
                    //'username' => 'infotech', //sprymobile's account
                    //'password' => 'Kronos@13', //sprymobile's account password
                    'client_id' => 'lyfIMokDWq6rHCaEUIRF4hK3M54OVYPV',
                    'client_secret' => 'X1orGr3HN9Jgohlg',
                    'grant_type' => 'password',
                    'auth_chain' => 'OAuthLdapService'
                ]
            ]);
        } catch (RequestException $e) {
            if ($e->hasResponse()) {
                $exception = $e->getResponse()->getBody();
                $exception = json_decode($exception, true);
                $error = $exception['error'];
                $message = $exception['error_description'];

                return 'Something went wrong! ' . $error . ': ' . $message;
            }
        }

        $body = json_decode($response->getBody(), true);
        $access_token = data_get($body, 'access_token');

        return $access_token;
    }

    /*** Employees ***/

    /**
     * @return array
     */
    public function getEmployees()
    {
        $dateTime = Carbon::now();
        $startOfYear = $dateTime->startOfYear()->toDateString();
        $endOfYear = $dateTime->endOfYear()->toDateString();
        //$employees = array();
        //$bodyRequest = '{"select":[{"key":"PEOPLE_PERSON_ID","alias":"PEOPLE_PERSON_ID"},{"key":"PEOPLE_PERSON_NUMBER","alias":"PEOPLE_PERSON_NUMBER"},{"key":"PEOPLE_FIRST_NAME","alias":"PEOPLE_FIRST_NAME"},{"key":"PEOPLE_LAST_NAME","alias":"PEOPLE_LAST_NAME"},{"key":"PEOPLE_EMAIL","alias":"PEOPLE_EMAIL"},{"key":"EMP_COMMON_FULL_NAME","alias":"EMP_COMMON_FULL_NAME"},{"key":"PEOPLE_PHONE_NUMBER","alias":"PEOPLE_PHONE_NUMBER","properties":[{"key":"1","value":"Phone 1"}]},{"key":"PEOPLE_ACCRUAL_PROFILE_NAME","alias":"PEOPLE_ACCRUAL_PROFILE_NAME"}],"from":{"view":"EMP","employeeSet":{"hyperfind":{"qualifier":"All Home"},"dateRange":{"symbolicPeriod":{"id":1}}}},"where":[{"key":"PEOPLE_ACCRUAL_PROFILE_NAME","alias":"PEOPLE_ACCRUAL_PROFILE_NAME","operator":"STARTS_WITH","values":["IBEW"]}]}';

        //all employee data dictionaries
        //$bodyRequest = '{"select":[{"key":"CORE_EMP","alias":"CORE_EMP"},{"key":"CORE_GENERICJOB_CODE","alias":"CORE_GENERICJOB_CODE"},{"key":"CORE_GENERICJOB_DESCRIPTION","alias":"CORE_GENERICJOB_DESCRIPTION"},{"key":"CORE_GENERICJOB_SORT_ORDER","alias":"CORE_GENERICJOB_SORT_ORDER"},{"key":"CORE_GENERICJOB_TITLE","alias":"CORE_GENERICJOB_TITLE"},{"key":"CORE_JOB_DESCRIPTION","alias":"CORE_JOB_DESCRIPTION"},{"key":"CORE_ORGJOB","alias":"CORE_ORGJOB"},{"key":"CORE_ORGJOB_PATH","alias":"CORE_ORGJOB_PATH"},{"key":"EMP_COMMON_DISPLAY_PROFILE","alias":"EMP_COMMON_DISPLAY_PROFILE"},{"key":"EMP_COMMON_EMPLOYEE_GROUP","alias":"EMP_COMMON_EMPLOYEE_GROUP"},{"key":"EMP_COMMON_FULL_NAME","alias":"EMP_COMMON_FULL_NAME"},{"key":"EMP_COMMON_FUNCTION_ACCESS_PROFILE","alias":"EMP_COMMON_FUNCTION_ACCESS_PROFILE"},{"key":"EMP_COMMON_ID","alias":"EMP_COMMON_ID"},{"key":"EMP_COMMON_PHOTO_ID","alias":"EMP_COMMON_PHOTO_ID"},{"key":"EMP_COMMON_PRIMARY_JOB","alias":"EMP_COMMON_PRIMARY_JOB"},{"key":"EMP_COMMON_PRIMARY_JOB_CODE","alias":"EMP_COMMON_PRIMARY_JOB_CODE"},{"key":"EMP_COMMON_PRIMARY_JOB_DESCRIPTION","alias":"EMP_COMMON_PRIMARY_JOB_DESCRIPTION"},{"key":"EMP_COMMON_PRIMARY_JOB_EFFECTIVE_DATE","alias":"EMP_COMMON_PRIMARY_JOB_EFFECTIVE_DATE"},{"key":"EMP_COMMON_PRIMARY_JOB_END_DATE","alias":"EMP_COMMON_PRIMARY_JOB_END_DATE"},{"key":"EMP_COMMON_PRIMARY_JOB_SORT_ORDER","alias":"EMP_COMMON_PRIMARY_JOB_SORT_ORDER"},{"key":"EMP_COMMON_PRIMARY_JOB_TITLE","alias":"EMP_COMMON_PRIMARY_JOB_TITLE"},{"key":"EMP_COMMON_PRIMARY_ORG","alias":"EMP_COMMON_PRIMARY_ORG"},{"key":"EMP_COMMON_PRIMARY_ORG_DESCRIPTION","alias":"EMP_COMMON_PRIMARY_ORG_DESCRIPTION"},{"key":"PEOPLE_ACCRUAL_EFFECTIVE_DATE","alias":"PEOPLE_ACCRUAL_EFFECTIVE_DATE"},{"key":"PEOPLE_ACCRUAL_EXPIRATION_DATE","alias":"PEOPLE_ACCRUAL_EXPIRATION_DATE"},{"key":"PEOPLE_ACCRUAL_PROFILE_NAME","alias":"PEOPLE_ACCRUAL_PROFILE_NAME"},{"key":"PEOPLE_ADDRESS_CITY_HOME","alias":"PEOPLE_ADDRESS_CITY_HOME"},{"key":"PEOPLE_ADDRESS_CITY_WORK","alias":"PEOPLE_ADDRESS_CITY_WORK"},{"key":"PEOPLE_ADDRESS_COUNTRY_HOME","alias":"PEOPLE_ADDRESS_COUNTRY_HOME"},{"key":"PEOPLE_ADDRESS_COUNTRY_WORK","alias":"PEOPLE_ADDRESS_COUNTRY_WORK"},{"key":"PEOPLE_ADDRESS_STATE_HOME","alias":"PEOPLE_ADDRESS_STATE_HOME"},{"key":"PEOPLE_ADDRESS_STATE_WORK","alias":"PEOPLE_ADDRESS_STATE_WORK"},{"key":"PEOPLE_ADDRESS_STREET_HOME","alias":"PEOPLE_ADDRESS_STREET_HOME"},{"key":"PEOPLE_ADDRESS_STREET_WORK","alias":"PEOPLE_ADDRESS_STREET_WORK"},{"key":"PEOPLE_ADDRESS_ZIPCODE_HOME","alias":"PEOPLE_ADDRESS_ZIPCODE_HOME"},{"key":"PEOPLE_ADDRESS_ZIPCODE_WORK","alias":"PEOPLE_ADDRESS_ZIPCODE_WORK"},{"key":"PEOPLE_ASSIGNED_CURRENCY","alias":"PEOPLE_ASSIGNED_CURRENCY"},{"key":"PEOPLE_ATTENDANCE_PROFILE_NAME","alias":"PEOPLE_ATTENDANCE_PROFILE_NAME"},{"key":"PEOPLE_AUTH_TYPE","alias":"PEOPLE_AUTH_TYPE"},{"key":"PEOPLE_BADGE_EFFECTIVE_DATE","alias":"PEOPLE_BADGE_EFFECTIVE_DATE"},{"key":"PEOPLE_BADGE_EXPIRATION_DATE","alias":"PEOPLE_BADGE_EXPIRATION_DATE"},{"key":"PEOPLE_BADGE_NUMBER","alias":"PEOPLE_BADGE_NUMBER"},{"key":"PEOPLE_BIRTH_DATE","alias":"PEOPLE_BIRTH_DATE"},{"key":"PEOPLE_CAN_APPROVE_OVERTIME_REQUESTS","alias":"PEOPLE_CAN_APPROVE_OVERTIME_REQUESTS"},{"key":"PEOPLE_CAN_SEE_TRANSFERRED_EMPLOYEES","alias":"PEOPLE_CAN_SEE_TRANSFERRED_EMPLOYEES"},{"key":"PEOPLE_EMAIL","alias":"PEOPLE_EMAIL"},{"key":"PEOPLE_EMP_STATUS","alias":"PEOPLE_EMP_STATUS"},{"key":"PEOPLE_EMP_TERM","alias":"PEOPLE_EMP_TERM"},{"key":"PEOPLE_EMP_TERM_END_DATE","alias":"PEOPLE_EMP_TERM_END_DATE"},{"key":"PEOPLE_EMPLOYEE_JOB_TRANSFER_SET","alias":"PEOPLE_EMPLOYEE_JOB_TRANSFER_SET"},{"key":"PEOPLE_EMPLOYEE_LABOR_CATEGORY","alias":"PEOPLE_EMPLOYEE_LABOR_CATEGORY"},{"key":"PEOPLE_EMPLOYMENT_STATUS_DATE","alias":"PEOPLE_EMPLOYMENT_STATUS_DATE"},{"key":"PEOPLE_EXPECTED_DAILY_HOURS","alias":"PEOPLE_EXPECTED_DAILY_HOURS"},{"key":"PEOPLE_EXPECTED_PAYPERIOD_HOURS","alias":"PEOPLE_EXPECTED_PAYPERIOD_HOURS"},{"key":"PEOPLE_EXPECTED_WEEKLY_HOURS","alias":"PEOPLE_EXPECTED_WEEKLY_HOURS"},{"key":"PEOPLE_FINGERSCAN_ENROLLED","alias":"PEOPLE_FINGERSCAN_ENROLLED"},{"key":"PEOPLE_FINGERSCAN_IDENTIFICATION_ENROLLED","alias":"PEOPLE_FINGERSCAN_IDENTIFICATION_ENROLLED"},{"key":"PEOPLE_FINGERSCAN_REQUIRED","alias":"PEOPLE_FINGERSCAN_REQUIRED"},{"key":"PEOPLE_FIRST_NAME","alias":"PEOPLE_FIRST_NAME"},{"key":"PEOPLE_HIRE_DATE","alias":"PEOPLE_HIRE_DATE"},{"key":"PEOPLE_HOME_COST_CENTER","alias":"PEOPLE_HOME_COST_CENTER"},{"key":"PEOPLE_HOME_LABOR_CATEGORY","alias":"PEOPLE_HOME_LABOR_CATEGORY"},{"key":"PEOPLE_HOURLY_WAGE_RATE","alias":"PEOPLE_HOURLY_WAGE_RATE"},{"key":"PEOPLE_LAST_NAME","alias":"PEOPLE_LAST_NAME"},{"key":"PEOPLE_MANAGER_ACCESS_ORG_GROUP","alias":"PEOPLE_MANAGER_ACCESS_ORG_GROUP"},{"key":"PEOPLE_MANAGER_ACCESS_ORG_GROUP_EFFECTIVE_DATE","alias":"PEOPLE_MANAGER_ACCESS_ORG_GROUP_EFFECTIVE_DATE"},{"key":"PEOPLE_MANAGER_JOB_TRANSFER_SET","alias":"PEOPLE_MANAGER_JOB_TRANSFER_SET"},{"key":"PEOPLE_MANAGER_LABOR_CATEGORY","alias":"PEOPLE_MANAGER_LABOR_CATEGORY"},{"key":"PEOPLE_MANAGER_NAME","alias":"PEOPLE_MANAGER_NAME"},{"key":"PEOPLE_MIDDLE_NAME","alias":"PEOPLE_MIDDLE_NAME"},{"key":"PEOPLE_PAYRULE","alias":"PEOPLE_PAYRULE"},{"key":"PEOPLE_PAYRULE_EFFECTIVE_DATE","alias":"PEOPLE_PAYRULE_EFFECTIVE_DATE"},{"key":"PEOPLE_PERSON_ID","alias":"PEOPLE_PERSON_ID"},{"key":"PEOPLE_PERSON_NUMBER","alias":"PEOPLE_PERSON_NUMBER"},{"key":"PEOPLE_PHONE_NUMBER","alias":"PEOPLE_PHONE_NUMBER","properties":[{"key":"1","value":"Phone 1"}]},{"key":"PEOPLE_PRIMARY_FINGERSCAN_ENROLL_LOCATION","alias":"PEOPLE_PRIMARY_FINGERSCAN_ENROLL_LOCATION"},{"key":"PEOPLE_PRIMARY_FINGERSCAN_THRESHOLD","alias":"PEOPLE_PRIMARY_FINGERSCAN_THRESHOLD"},{"key":"PEOPLE_SCHEDULE_GROUP_ASSIGNMENT_EFFECTIVE_DATE","alias":"PEOPLE_SCHEDULE_GROUP_ASSIGNMENT_EFFECTIVE_DATE"},{"key":"PEOPLE_SCHEDULE_GROUP_ASSIGNMENT_EXPIRATION_DATE","alias":"PEOPLE_SCHEDULE_GROUP_ASSIGNMENT_EXPIRATION_DATE"},{"key":"PEOPLE_SCHEDULE_GROUP_ASSIGNMENT_NAME","alias":"PEOPLE_SCHEDULE_GROUP_ASSIGNMENT_NAME"},{"key":"PEOPLE_SENIORITY_DATE","alias":"PEOPLE_SENIORITY_DATE"},{"key":"PEOPLE_SHORT_NAME","alias":"PEOPLE_SHORT_NAME"},{"key":"PEOPLE_TIMEZONE","alias":"PEOPLE_TIMEZONE"},{"key":"PEOPLE_UDM_DEVICE_GROUP","alias":"PEOPLE_UDM_DEVICE_GROUP"},{"key":"PEOPLE_USER_ACCOUNT_NAME","alias":"PEOPLE_USER_ACCOUNT_NAME"},{"key":"PEOPLE_USER_PREFERRED_CURRENCY","alias":"PEOPLE_USER_PREFERRED_CURRENCY"},{"key":"PEOPLE_USER_STATUS","alias":"PEOPLE_USER_STATUS"},{"key":"PEOPLE_WORKER_TYPE","alias":"PEOPLE_WORKER_TYPE"},{"key":"PEOPLE_WORKFLOW_EMPLOYEE_PROFILE","alias":"PEOPLE_WORKFLOW_EMPLOYEE_PROFILE"},{"key":"PEOPLE_WORKFLOW_MANAGER_PROFILE","alias":"PEOPLE_WORKFLOW_MANAGER_PROFILE"},{"key":"SCH_PEOPLE_CERTIFICATION_EXPIRATION_DATE","alias":"SCH_PEOPLE_CERTIFICATION_EXPIRATION_DATE"},{"key":"SCH_PEOPLE_CERTIFICATION_GRANT_DATE","alias":"SCH_PEOPLE_CERTIFICATION_GRANT_DATE"},{"key":"SCH_PEOPLE_CERTIFICATION_NAME","alias":"SCH_PEOPLE_CERTIFICATION_NAME"},{"key":"SCH_PEOPLE_CERTIFICATION_NAMES_COMMA_SEPARATED","alias":"SCH_PEOPLE_CERTIFICATION_NAMES_COMMA_SEPARATED"},{"key":"SCH_PEOPLE_CERTIFICATION_PROFICIENCY_LEVEL_NAME","alias":"SCH_PEOPLE_CERTIFICATION_PROFICIENCY_LEVEL_NAME"},{"key":"SCH_PEOPLE_SKILL_NAME","alias":"SCH_PEOPLE_SKILL_NAME"},{"key":"SCH_PEOPLE_SKILL_NAMES_COMMA_SEPARATED","alias":"SCH_PEOPLE_SKILL_NAMES_COMMA_SEPARATED"},{"key":"SCH_PEOPLE_SKILL_PROFICIENCY_LEVEL_NAME","alias":"SCH_PEOPLE_SKILL_PROFICIENCY_LEVEL_NAME"}],"from":{"view":"EMP","employeeSet":{"hyperfind":{"qualifier":"All Home"},"dateRange":{"symbolicPeriod":{"id":1}}}}}';

        //shortened list
        $bodyRequest = '{"select":[{"key":"PEOPLE_PERSON_NUMBER","alias":"PEOPLE_PERSON_NUMBER"},{"key":"PEOPLE_PHONE_NUMBER","alias":"PEOPLE_PHONE_NUMBER","properties":[{"key":"1","value":"Phone 1"}]},{"key":"PEOPLE_EMAIL","alias":"PEOPLE_EMAIL"},{"key":"EMP_COMMON_PRIMARY_JOB_DESCRIPTION","alias":"EMP_COMMON_PRIMARY_JOB_DESCRIPTION"},{"key":"PEOPLE_PERSON_ID","alias":"PEOPLE_PERSON_ID"},{"key":"PEOPLE_HIRE_DATE","alias":"PEOPLE_HIRE_DATE"},{"key":"PEOPLE_SENIORITY_DATE","alias":"PEOPLE_SENIORITY_DATE"},{"key":"EMP_COMMON_PRIMARY_JOB_TITLE","alias":"EMP_COMMON_PRIMARY_JOB_TITLE"},{"key":"PEOPLE_BADGE_NUMBER","alias":"PEOPLE_BADGE_NUMBER"},{"key":"EMP_COMMON_FULL_NAME","alias":"EMP_COMMON_FULL_NAME"},{"key":"PEOPLE_FIRST_NAME","alias":"PEOPLE_FIRST_NAME"},{"key":"PEOPLE_LAST_NAME","alias":"PEOPLE_LAST_NAME"},{"key":"PEOPLE_MANAGER_NAME","alias":"PEOPLE_MANAGER_NAME"},{"key":"EMP_COMMON_PRIMARY_ORG","alias":"EMP_COMMON_PRIMARY_ORG"}],"from":{"view":"EMP","employeeSet":{"hyperfind":{"qualifier":"All Employees Active (No Test Employees)"},"dateRange":{"symbolicPeriod":{"id":1}}}}}';

        $client = $this->Client();
        $url = 'commons/data/multi_read';
        //dd($client);

        $response = $client->post($url, [
            'body' => $bodyRequest
        ]);
        //dd($response);
        $bodyResponse = json_decode($response->getBody(), true);
        //dd($bodyResponse);
        //$bodyResponse = array_slice($bodyResponse, 0, 30);
        //dd($bodyResponse);

        //$metadata = data_get($bodyResponse, 'metadata');
        //dd($metadata);

        $childrens = data_get($bodyResponse, 'data.children');

        //$childrens = array_slice($childrens, 0, 30);
        //dd($childrens);
        foreach ($childrens as $children) {

            $attributes = data_get($children, 'attributes');

            //$attributes = array_slice($attributes, 0, 30);
            //dd($attributes);

            $employee = array();

            foreach ($attributes as $attribute) {

                $key = data_get($attribute, 'alias', '');
                $value = data_get($attribute, 'value', '');
                $fancyArray = [
                    $key => $value
                ];

                $employee = Arr::add($employee, $key, $value);

            }
            $employees[] = $employee;
            //dd($employees);
        }
        //dd($employees);
        /*
        foreach($employees as $employee){
            $keys = collect($employee)->keys()->toArray();
        }
        $filename = 'Z:\IT_Development\Processes\KronosDimensions-API Integrations\Employees_Short_'. $startOfYear .'-'. $endOfYear .'.csv';
        //$header = ['CORE_EMP','CORE_GENERICJOB_CODE','CORE_GENERICJOB_DESCRIPTION','CORE_GENERICJOB_SORT_ORDER','CORE_GENERICJOB_TITLE','CORE_JOB_DESCRIPTION','CORE_ORGJOB','CORE_ORGJOB_PATH','EMP_COMMON_DISPLAY_PROFILE','EMP_COMMON_EMPLOYEE_GROUP','EMP_COMMON_FULL_NAME','EMP_COMMON_FUNCTION_ACCESS_PROFILE','EMP_COMMON_ID','EMP_COMMON_PHOTO_ID','EMP_COMMON_PRIMARY_JOB','EMP_COMMON_PRIMARY_JOB_CODE','EMP_COMMON_PRIMARY_JOB_DESCRIPTION','EMP_COMMON_PRIMARY_JOB_EFFECTIVE_DATE','EMP_COMMON_PRIMARY_JOB_END_DATE','EMP_COMMON_PRIMARY_JOB_SORT_ORDER','EMP_COMMON_PRIMARY_JOB_TITLE','EMP_COMMON_PRIMARY_ORG','EMP_COMMON_PRIMARY_ORG_DESCRIPTION','PEOPLE_ACCRUAL_EFFECTIVE_DATE','PEOPLE_ACCRUAL_EXPIRATION_DATE','PEOPLE_ACCRUAL_PROFILE_NAME','PEOPLE_ADDRESS_CITY_HOME','PEOPLE_ADDRESS_CITY_WORK','PEOPLE_ADDRESS_COUNTRY_HOME','PEOPLE_ADDRESS_COUNTRY_WORK','PEOPLE_ADDRESS_STATE_HOME','PEOPLE_ADDRESS_STATE_WORK','PEOPLE_ADDRESS_STREET_HOME','PEOPLE_ADDRESS_STREET_WORK','PEOPLE_ADDRESS_ZIPCODE_HOME','PEOPLE_ADDRESS_ZIPCODE_WORK','PEOPLE_ASSIGNED_CURRENCY','PEOPLE_ATTENDANCE_PROFILE_NAME','PEOPLE_AUTH_TYPE','PEOPLE_BADGE_EFFECTIVE_DATE','PEOPLE_BADGE_EXPIRATION_DATE','PEOPLE_BADGE_NUMBER','PEOPLE_BIRTH_DATE','PEOPLE_CAN_APPROVE_OVERTIME_REQUESTS','PEOPLE_CAN_SEE_TRANSFERRED_EMPLOYEES','PEOPLE_CUSTOM','PEOPLE_EMAIL','PEOPLE_EMP_STATUS','PEOPLE_EMP_TERM','PEOPLE_EMP_TERM_END_DATE','PEOPLE_EMPLOYEE_JOB_TRANSFER_SET','PEOPLE_EMPLOYEE_LABOR_CATEGORY','PEOPLE_EMPLOYMENT_STATUS_DATE','PEOPLE_EXPECTED_DAILY_HOURS','PEOPLE_EXPECTED_PAYPERIOD_HOURS','PEOPLE_EXPECTED_WEEKLY_HOURS','PEOPLE_FINGERSCAN_ENROLLED','PEOPLE_FINGERSCAN_IDENTIFICATION_ENROLLED','PEOPLE_FINGERSCAN_REQUIRED','PEOPLE_FIRST_NAME','PEOPLE_HIRE_DATE','PEOPLE_HOME_COST_CENTER','PEOPLE_HOME_LABOR_CATEGORY','PEOPLE_HOURLY_WAGE_RATE','PEOPLE_LAST_NAME','PEOPLE_MANAGER_ACCESS_ORG_GROUP','PEOPLE_MANAGER_ACCESS_ORG_GROUP_EFFECTIVE_DATE','PEOPLE_MANAGER_JOB_TRANSFER_SET','PEOPLE_MANAGER_LABOR_CATEGORY','PEOPLE_MANAGER_NAME','PEOPLE_MIDDLE_NAME','PEOPLE_PAYRULE','PEOPLE_PAYRULE_EFFECTIVE_DATE','PEOPLE_PERSON_ID','PEOPLE_PERSON_NUMBER','PEOPLE_PHONE_NUMBER','PEOPLE_PRIMARY_FINGERSCAN_ENROLL_LOCATION','PEOPLE_PRIMARY_FINGERSCAN_THRESHOLD','PEOPLE_SCHEDULE_GROUP_ASSIGNMENT_EFFECTIVE_DATE','PEOPLE_SCHEDULE_GROUP_ASSIGNMENT_EXPIRATION_DATE','PEOPLE_SCHEDULE_GROUP_ASSIGNMENT_NAME','PEOPLE_SENIORITY_DATE','PEOPLE_SHORT_NAME','PEOPLE_TIMEZONE','PEOPLE_UDM_DEVICE_GROUP','PEOPLE_USER_ACCOUNT_NAME','PEOPLE_USER_PREFERRED_CURRENCY','PEOPLE_USER_STATUS','PEOPLE_WORKER_TYPE','PEOPLE_WORKFLOW_EMPLOYEE_PROFILE','PEOPLE_WORKFLOW_MANAGER_PROFILE','SCH_PEOPLE_CERTIFICATION_EXPIRATION_DATE','SCH_PEOPLE_CERTIFICATION_GRANT_DATE','SCH_PEOPLE_CERTIFICATION_NAME','SCH_PEOPLE_CERTIFICATION_NAMES_COMMA_SEPARATED','SCH_PEOPLE_CERTIFICATION_PROFICIENCY_LEVEL_NAME','SCH_PEOPLE_SKILL_NAME','SCH_PEOPLE_SKILL_NAMES_COMMA_SEPARATED','SCH_PEOPLE_SKILL_PROFICIENCY_LEVEL_NAME'];

        $header = $keys;

        $fp = fopen($filename, 'w');
        fputcsv($fp, $header);
        foreach ($employees as $employee) {
            fputcsv($fp, $employee);
        }

        fclose($fp);

        //dd($employees);
        */
        return $employees;
    }

    /**
     * @return mixed
     */
    public function updateEmployees()
    {
        $kronosEmployees = $this->getEmployees();


        foreach ($kronosEmployees as $kronosEmployee) {
            $employee = Employee::updateOrCreate($kronosEmployee);
        }
        return $employee;
    }

    /**
     * @return mixed
     */
    public function updateEmployeesTimeOff()
    {

        $kronosEmployeeTimeOffs = $this->getEmployeesTimeOff();


        foreach ($kronosEmployeeTimeOffs as $kronosEmployeeTimeOff) {
            $employeesTimeOffInserted = EmployeeTimeOff::updateOrCreate($kronosEmployeeTimeOff);
        }
        return $employeesTimeOffInserted;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getEmployeesTimeOff()
    {
        //Creating a carbon instance
        $dateTime = new Carbon();
        $startOfYear = $dateTime->copy()->startOfYear()->toDateString();
        $endOfYear = $dateTime->copy()->endOfYear()->toDateString();
        $employeesTimeOff = array();


        $startDate = '2020-03-30';
        $endDate = '2020-12-31';
        //looping through each quarter subtracting by 1 because the index is starting at 0. gathering employee paycode data.

        $bodyRequest = '{"select":[{"key":"EMP_COMMON_FULL_NAME","alias":"EMP_COMMON_FULL_NAME"},{"key":"SCH_PCE_START_DATE","alias":"SCH_PCE_START_DATE"},{"key":"SCH_PCE_START_TIME","alias":"SCH_PCE_START_TIME"},{"key":"SCH_PCE_END_DATE","alias":"SCH_PCE_END_DATE"},{"key":"SCH_PCE_IS_POSTED","alias":"SCH_PCE_IS_POSTED"},{"key":"SCH_PCE_IS_GENERATED","alias":"SCH_PCE_IS_GENERATED"},{"key":"SCH_PCE_TRANSFER_STRING","alias":"SCH_PCE_TRANSFER_STRING"}],"from":{"view":"EMP","employeeSet":{"hyperfind":{"qualifier":"All Employees Active (No Test Employees)"},"dateRange":{"startDate":"' . $startDate . 'T00:00","EndDate":"' . $endDate . 'T00:00"}}}}';

        //$bodyRequest = '{"select":[{"key":"EMP_COMMON_FULL_NAME","alias":"EMP_COMMON_FULL_NAME"},{"key":"SCH_PCE_START_DATE","alias":"SCH_PCE_START_DATE"},{"key":"SCH_PCE_START_TIME","alias":"SCH_PCE_START_TIME"},{"key":"SCH_PCE_END_DATE","alias":"SCH_PCE_END_DATE"},{"key":"SCH_PCE_END_TIME","alias":"SCH_PCE_END_TIME"},{"key":"PEOPLE_ACCRUAL_PROFILE_NAME","alias":"PEOPLE_ACCRUAL_PROFILE_NAME"}],"from":{"view":"EMP","employeeSet":{"hyperfind":{"qualifier":"All Home"},"dateRange":{"startDate":"' . $startDate . 'T00:00","EndDate":"' . $endDate . 'T00:00"}}},"where":[{"key":"PEOPLE_ACCRUAL_PROFILE_NAME","alias":"PEOPLE_ACCRUAL_PROFILE_NAME","operator":"STARTS_WITH","values":["IBEW"]}]}';
        //setting kronos query string in json for the body request(specific SCH_PCE columns)
        //$bodyRequest = '{"select":[{"key":"PEOPLE_PERSON_ID","alias":"PEOPLE_PERSON_ID"},{"key":"PEOPLE_PERSON_NUMBER","alias":"PEOPLE_PERSON_NUMBER"},{"key":"PEOPLE_FIRST_NAME","alias":"PEOPLE_FIRST_NAME"},{"key":"PEOPLE_LAST_NAME","alias":"PEOPLE_LAST_NAME"},{"key":"EMP_COMMON_FULL_NAME","alias":"EMP_COMMON_FULL_NAME"},{"key":"PEOPLE_PHONE_NUMBER","alias":"PEOPLE_PHONE_NUMBER","properties":[{"key":"1","value":"Phone 1"}]},{"key":"PEOPLE_EMAIL","alias":"PEOPLE_EMAIL"},{"key":"SCH_PCE_PRIMARY_ORG_JOB","alias":"SCH_PCE_PRIMARY_ORG_JOB"},{"key":"SCH_PCE_PAYCODE_REF","alias":"SCH_PCE_PAYCODE_REF"},{"key":"SCH_PCE_START_DATE","alias":"SCH_PCE_START_DATE"},{"key":"SCH_PCE_START_TIME","alias":"SCH_PCE_START_TIME"},{"key":"SCH_PCE_END_DATE","alias":"SCH_PCE_END_DATE"},{"key":"SCH_PCE_END_TIME","alias":"SCH_PCE_END_TIME"},{"key":"SCH_PCE_DURATION_IN_TIME","alias":"SCH_PCE_DURATION_IN_TIME"},{"key":"SCH_PCE_SYMBOLIC_VALUE_REF","alias":"SCH_PCE_SYMBOLIC_VALUE_REF"},{"key":"PEOPLE_ACCRUAL_PROFILE_NAME","alias":"PEOPLE_ACCRUAL_PROFILE_NAME"}],"from":{"view":"EMP","employeeSet":{"hyperfind":{"qualifier":"All Home"},"dateRange":{"startDate":"' . $startDate . 'T00:00","EndDate":"' . $endDate . 'T00:00"}}},"where":[{"key":"PEOPLE_ACCRUAL_PROFILE_NAME","alias":"PEOPLE_ACCRUAL_PROFILE_NAME","operator":"STARTS_WITH","values":["IBEW"]}]}';

        //initiating client
        $client = $this->Client();

        //setting the resource url
        $url = 'commons/data/multi_read';

        //sending the request and receiving the response data
        $response = $client->post($url, [
            'body' => $bodyRequest
        ]);
        //decoding the json response body to an array
        $bodyResponse = json_decode($response->getBody(), true);

        //getting the array of arrays for the data to be extracted
        $childrens = data_get($bodyResponse, 'data.children');
        dd($childrens);
        //going through each children data array to get their attributes
        foreach ($childrens as $children) {
            //getting each childrens data attribute array
            $attributes = data_get($children, 'attributes');
            //dd($children);
            $employeeTimeOff = array();
            $timeOffStartDate = '';
            $timeOffStartTime = '';
            $timeOffEndDate = '';
            $timeOffEndTime = '';
            //if the array count is greater than 10 then it has paycode edit values that we need from the kronos query
            if (count($attributes) > 4) {
                //going through each array in attributes to get the value of each attribute
                foreach ($attributes as $attribute) {
//            dd($attribute);
                    $key = data_get($attribute, 'alias', '');
                    $value = data_get($attribute, 'value', '');
                    $fancyArray = [
                        $key => $value
                    ];
                    switch ($key) {
                        case "SCH_PCE_START_DATE":
                            $timeOffStartDate = data_get($attribute, 'rawValue', '');
                            break;
                        case "SCH_PCE_START_TIME":
                            $timeOffStartTime = data_get($attribute, 'rawValue', '');;
                            break;
                        case "SCH_PCE_END_DATE":
                            $timeOffEndDate = data_get($attribute, 'rawValue', '');;
                            break;
                        case "SCH_PCE_END_TIME":
                            $timeOffEndTime = data_get($attribute, 'rawValue', '');;
                            break;
                    }
                    $employeeTimeOff = Arr::add($employeeTimeOff, $key, $value);
                }
                $timeOffStartDatetime = Carbon::createFromFormat('Y-m-d H:i:s', $timeOffStartDate . ' ' . $timeOffStartTime)->toDateTimeString();
                $timeOffEndDatetime = Carbon::createFromFormat('Y-m-d H:i:s', $timeOffEndDate . ' ' . $timeOffEndTime)->toDateTimeString();
                $employeeTimeOff = Arr::add($employeeTimeOff, 'SCH_PCE_START_DATETIME', $timeOffStartDatetime);
                $employeeTimeOff = Arr::add($employeeTimeOff, 'SCH_PCE_END_DATETIME', $timeOffEndDatetime);
                //var_dump($employeeTimeOff);
                $employeesTimeOff[] = $employeeTimeOff;
            }
        }


        //dd($employeesTimeOff);

        return $employeesTimeOff;
    }

    public function getEmployeeTimeOffs($startDate, $endDate)
    {
        //Creating a carbon instance
        $employeesTimeOff = array();

        $startDate = Carbon::parse($startDate)->toDateString();
        $endDate = Carbon::parse($endDate)->toDateString();

        $bodyRequest = '{"select":[{"key":"EMP_COMMON_FULL_NAME","alias":"EMP_COMMON_FULL_NAME"},{"key":"SCH_PCE_START_DATE","alias":"SCH_PCE_START_DATE"},{"key":"SCH_PCE_START_TIME","alias":"SCH_PCE_START_TIME"},{"key":"SCH_PCE_END_DATE","alias":"SCH_PCE_END_DATE"},{"key":"SCH_PCE_END_TIME","alias":"SCH_PCE_END_TIME"}],"from":{"view":"EMP","employeeSet":{"hyperfind":{"qualifier":"All Employees Active (No Test Employees)"},"dateRange":{"startDate":"' . $startDate . 'T00:00","EndDate":"' . $endDate . 'T00:00"}}}}';
        //setting kronos query string in json for the body request(specific SCH_PCE columns)
        //$bodyRequest = '{"select":[{"key":"PEOPLE_PERSON_ID","alias":"PEOPLE_PERSON_ID"},{"key":"PEOPLE_PERSON_NUMBER","alias":"PEOPLE_PERSON_NUMBER"},{"key":"PEOPLE_FIRST_NAME","alias":"PEOPLE_FIRST_NAME"},{"key":"PEOPLE_LAST_NAME","alias":"PEOPLE_LAST_NAME"},{"key":"EMP_COMMON_FULL_NAME","alias":"EMP_COMMON_FULL_NAME"},{"key":"PEOPLE_PHONE_NUMBER","alias":"PEOPLE_PHONE_NUMBER","properties":[{"key":"1","value":"Phone 1"}]},{"key":"PEOPLE_EMAIL","alias":"PEOPLE_EMAIL"},{"key":"SCH_PCE_PRIMARY_ORG_JOB","alias":"SCH_PCE_PRIMARY_ORG_JOB"},{"key":"SCH_PCE_PAYCODE_REF","alias":"SCH_PCE_PAYCODE_REF"},{"key":"SCH_PCE_START_DATE","alias":"SCH_PCE_START_DATE"},{"key":"SCH_PCE_START_TIME","alias":"SCH_PCE_START_TIME"},{"key":"SCH_PCE_END_DATE","alias":"SCH_PCE_END_DATE"},{"key":"SCH_PCE_END_TIME","alias":"SCH_PCE_END_TIME"},{"key":"SCH_PCE_DURATION_IN_TIME","alias":"SCH_PCE_DURATION_IN_TIME"},{"key":"SCH_PCE_SYMBOLIC_VALUE_REF","alias":"SCH_PCE_SYMBOLIC_VALUE_REF"},{"key":"PEOPLE_ACCRUAL_PROFILE_NAME","alias":"PEOPLE_ACCRUAL_PROFILE_NAME"}],"from":{"view":"EMP","employeeSet":{"hyperfind":{"qualifier":"All Home"},"dateRange":{"startDate":"' . $startDate . 'T00:00","EndDate":"' . $endDate . 'T00:00"}}},"where":[{"key":"PEOPLE_ACCRUAL_PROFILE_NAME","alias":"PEOPLE_ACCRUAL_PROFILE_NAME","operator":"STARTS_WITH","values":["IBEW"]}]}';

        //initiating client
        $client = $this->Client();

        //setting the resource url
        $url = 'commons/data/multi_read';

        //sending the request and receiving the response data
        $response = $client->post($url, [
            'body' => $bodyRequest
        ]);
        //decoding the json response body to an array
        $bodyResponse = json_decode($response->getBody(), true);

        //getting the array of arrays for the data to be extracted
        $childrens = data_get($bodyResponse, 'data.children');

        //going through each children data array to get their attributes
        foreach ($childrens as $children) {
            //getting each childrens data attribute array
            $attributes = data_get($children, 'attributes');

            $employeeTimeOff = array();
            $timeOffStartDate = '';
            $timeOffStartTime = '';
            $timeOffEndDate = '';
            $timeOffEndTime = '';
            //if the array count is greater than 10 then it has paycode edit values that we need from the kronos query
            if (count($attributes) > 4) {
                //going through each array in attributes to get the value of each attribute
                foreach ($attributes as $attribute) {

                    $key = data_get($attribute, 'alias', '');
                    $value = data_get($attribute, 'rawValue', '');

                    switch ($key) {
                        case "SCH_PCE_START_DATE":
                            $timeOffStartDate = data_get($attribute, 'rawValue', '');
                            break;
                        case "SCH_PCE_START_TIME":
                            $timeOffStartTime = data_get($attribute, 'rawValue', '');
                            //dd($attribute);
                            break;
                        case "SCH_PCE_END_DATE":
                            $timeOffEndDate = data_get($attribute, 'rawValue', '');
                            break;
                        case "SCH_PCE_END_TIME":
                            $timeOffEndTime = data_get($attribute, 'rawValue', '');
                            break;
                    }
                    $employeeTimeOff = Arr::add($employeeTimeOff, $key, $value);

                }
                //dd($employeeTimeOff);
                $timeOffStartDatetime = Carbon::createFromFormat('Y-m-d H:i:s', $timeOffStartDate . ' ' . $timeOffStartTime)->toDateTimeString();
                $timeOffEndDatetime = Carbon::createFromFormat('Y-m-d H:i:s', $timeOffEndDate . ' ' . $timeOffEndTime)->toDateTimeString();
                $employeeTimeOff = Arr::add($employeeTimeOff, 'SCH_PCE_START_DATETIME', $timeOffStartDatetime);
                $employeeTimeOff = Arr::add($employeeTimeOff, 'SCH_PCE_END_DATETIME', $timeOffEndDatetime);
                //var_dump($employeeTimeOff);
                $employeesTimeOff[] = $employeeTimeOff;
            }
        }


        //dd($employeesTimeOff);

        return $employeesTimeOff;
    }



    /**
     * @return array
     * @throws \Exception
     */
    public function getScheduledTimeOff()
    {
        //Creating a carbon instance
        $dateTime = new Carbon();

        $timeOffs = array(); //initializing the timeoffs array
        $quartersInYear = 4; //Setting the number of quarters to loop
        $indexOffset = 1; //Setting the offset to be subtracted from quartersInYear within the for loop

        $monthsInYear = 12; //setting the number of months in a year to loop
        //looping through each quarter subtracting by 1 because the index is starting at 0. gathering employee paycode data.
        for ($index = 0; $index <= $quartersInYear - $indexOffset; $index++) {
            //setting the startdate by copying the carbon instance and adding the number of quarters based on the loop index
            $startDate = $dateTime->copy()->startOfYear()->addQuarters($index)->toDateString(); //2020-01-01 2020-04-01 2020-07-01 2020-10-01 2021-01-01
            //setting end date by copying the carbon instance and adding a quarter to be a quarter ahead of start date and then continueing to add quarters based on the loop index and subtracting 1 day to be a day behind the next start date so no duplication
            $endDate = $dateTime->copy()->startOfYear()->addQuarter()->addQuarters($index)->addDays(-1)->toDateString(); //2020-03-31 2020-06-30 2020-09-30 2020-12-31

            $client = $this->Client();

            $url = 'scheduling/schedule';

            $response = $client->get($url, [
                'query' => [
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    //'employee_id' => 1038,
                    //'person_number' => 85580,
                    'hyperfind_id' => 1,
                    'hyperfind_name' => 'All Home',
                    //'location_id' => '',
                    //'location_path' => '',
                    'exclude_breaks' => false,
                    //'order_by' => ''
                ]
            ]);

            $body = $response->getBody();

            $body = \GuzzleHttp\json_decode($body, true);

            $scheduledTimeOffs = data_get($body, 'payCodeEdits');

            //dd($payCodeEdits);

            foreach ($scheduledTimeOffs as $scheduledTimeOff) {

                $scheduleStartDate = data_get($scheduledTimeOff, 'startDate', '');
                $scheduleStartTime = data_get($scheduledTimeOff, 'startTime', '');
                $scheduleEndDate = data_get($scheduledTimeOff, 'endDate', '');
                $scheduleEndTime = data_get($scheduledTimeOff, 'endTime', '');

                $timeOffs[] = [
                    'schedule_id' => data_get($scheduledTimeOff, 'id', ''),
                    'kronos_id' => data_get($scheduledTimeOff, 'employee.id', ''),
                    'employee_number' => data_get($scheduledTimeOff, 'employee.qualifier', ''),
                    'start_date' => $scheduleStartDate,
                    'start_time' => $scheduleStartTime,
                    'end_date' => $scheduleEndDate,
                    'end_time' => $scheduleEndTime,
                    'start_datetime' => Carbon::createFromFormat('Y-m-d H:i:s', $scheduleStartDate . ' ' . $scheduleStartTime)->toDateTimeString(),
                    'end_datetime' => Carbon::createFromFormat('Y-m-d H:i:s', $scheduleEndDate . ' ' . $scheduleEndTime)->toDateTimeString(),
                    'locked' => data_get($scheduledTimeOff, 'locked', ''),
                    'posted' => data_get($scheduledTimeOff, 'posted', ''),
                    'generated' => data_get($scheduledTimeOff, 'generated', ''),
                    'deleted' => data_get($scheduledTimeOff, 'deleted', false),
                    'pay_code_id' => data_get($scheduledTimeOff, 'payCodeRef.id', ''),
                    'pay_code_name' => data_get($scheduledTimeOff, 'payCodeRef.qualifier', ''),
                ];
            }
        }


        return $timeOffs;

    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getEmployeeScheduledTimeOff()
    {

        $client = $this->Client();

        $url = 'scheduling/schedule';

        $response = $client->get($url, [
            'query' => [
                'start_date' => '2020-03-31',
                'end_date' => '2020-03-31',
                //'employee_id' => 972, // lori b
                //'employee_id' => 1038, // zach boling
                //'employee_id' => 1033, // john leary
                //'employee_id' => 1018, // ryan callan
                'person_number' => 84034,
                //'hyperfind_id' => 1,
                //'hyperfind_name' => 'All Home',
                //'location_id' => '',
                //'location_path' => '',
                'exclude_breaks' => false,
                //'order_by' => ''
            ]
        ]);

        $body = $response->getBody();

        $body = \GuzzleHttp\json_decode($body, true);

        $scheduledTimeOffs = data_get($body, 'payCodeEdits');

        dd($body);

        foreach ($scheduledTimeOffs as $scheduledTimeOff) {

            $scheduleStartDate = data_get($scheduledTimeOff, 'startDate', '');
            $scheduleStartTime = data_get($scheduledTimeOff, 'startTime', '');
            $scheduleEndDate = data_get($scheduledTimeOff, 'endDate', '');
            $scheduleEndTime = data_get($scheduledTimeOff, 'endTime', '');

            $timeOffs[] = [
                'schedule_id' => data_get($scheduledTimeOff, 'id', ''),
                'kronos_id' => data_get($scheduledTimeOff, 'employee.id', ''),
                'employee_number' => data_get($scheduledTimeOff, 'employee.qualifier', ''),
                'start_date' => $scheduleStartDate,
                'start_time' => $scheduleStartTime,
                'end_date' => $scheduleEndDate,
                'end_time' => $scheduleEndTime,
                'start_datetime' => Carbon::createFromFormat('Y-m-d H:i:s', $scheduleStartDate . ' ' . $scheduleStartTime)->toDateTimeString(),
                'end_datetime' => Carbon::createFromFormat('Y-m-d H:i:s', $scheduleEndDate . ' ' . $scheduleEndTime)->toDateTimeString(),
                'locked' => data_get($scheduledTimeOff, 'locked', ''),
                'posted' => data_get($scheduledTimeOff, 'posted', ''),
                'generated' => data_get($scheduledTimeOff, 'generated', ''),
                'deleted' => data_get($scheduledTimeOff, 'deleted', false),
                'pay_code_id' => data_get($scheduledTimeOff, 'payCodeRef.id', ''),
                'pay_code_name' => data_get($scheduledTimeOff, 'payCodeRef.qualifier', ''),
            ];
        }

        //Creating a carbon instance
        $dateTime = new Carbon();

        $timeOffs = array(); //initializing the timeoffs array
        $quartersInYear = 4; //Setting the number of quarters to loop
        $indexOffset = 1; //Setting the offset to be subtracted from quartersInYear within the for loop

        $monthsInYear = 12; //setting the number of months in a year to loop
        //looping through each quarter subtracting by 1 because the index is starting at 0. gathering employee paycode data.
        for ($index = 0; $index <= $quartersInYear - $indexOffset; $index++) {
            //setting the startdate by copying the carbon instance and adding the number of quarters based on the loop index
            $startDate = $dateTime->copy()->startOfYear()->addQuarters($index)->toDateString(); //2020-01-01 2020-04-01 2020-07-01 2020-10-01 2021-01-01
            //setting end date by copying the carbon instance and adding a quarter to be a quarter ahead of start date and then continueing to add quarters based on the loop index and subtracting 1 day to be a day behind the next start date so no duplication
            $endDate = $dateTime->copy()->startOfYear()->addQuarter()->addQuarters($index)->addDays(-1)->toDateString(); //2020-03-31 2020-06-30 2020-09-30 2020-12-31

        }


        return $timeOffs;

    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function updateScheduledTimeOff()
    {
        $scheduledTimeOffs = $this->getScheduledTimeOff();

        foreach ($scheduledTimeOffs as $scheduledTimeOff) {
            $timeOff = TimeOffSchedule::updateOrCreate(['schedule_id' => $scheduledTimeOff['schedule_id']], $scheduledTimeOff);
        }

        return $timeOff;
    }

    /*** Hyperfinds ***/

    public function getPublicHyperfindQueries()
    {

        $client = $this->Client();

        $url = 'commons/hyperfind/public';

        $response = $client->get($url, [
            'query' => [
                'all_details' => true
            ]
        ]);

        $body = json_decode($response->getBody(), true);

        dd($body);
    }

    /**
     *
     */
    public function getHyperfindProfiles()
    {

        $client = $this->Client();

        $url = 'commons/hyperfind_profiles';

        $response = $client->get($url);

        $body = json_decode($response->getBody(), true);

        dd($body);
    }

    public function executeHyperfindQuery()
    {
        $bodyRequest = '{"dateRange":{"symbolicPeriod":{"id":1}},"hyperfind":{"qualifier":"All Employees Active (No Test Employees)"}}';

        $client = $this->Client();
        $url = 'commons/hyperfind/execute';
        //dd($client);

        $response = $client->post($url, [
            'body' => $bodyRequest
        ]);

        $body = json_decode($response->getBody(), true);

        //dd($body);

        return $body;
    }

    public function getSymbolicPeriods()
    {

        $client = $this->Client();

        $url = 'commons/symbolicperiod';

        $response = $client->get($url);

        $body = json_decode($response->getBody(), true);

        $filename = 'Z:\IT_Development\Processes\KronosDimensions-API Integrations\SymbolicPeriods.csv';
        $header = ['id', 'symbolicId', 'name', 'periodTypeId', 'sortOrder'];

        $fp = fopen($filename, 'w');
        fputcsv($fp, $header);

        foreach ($body as $bod) {
            fputcsv($fp, $bod);
        }

        fclose($fp);

        dd($body);
    }

    /*** Data Dictionary ***/

    public function getDataDictionary()
    {
        $client = $this->Client();

        $url = 'commons/data_dictionary/data_elements';

        $response = $client->get($url);

        $body = json_decode($response->getBody(), true);

        //$body = array_slice($body, 0, 30);
        //$body = array_search('where',array_column($body, 'key'));
        //dd($body);
        //$filename = 'C:\Users\ZBoling\Documents\testfile.csv';
        //$filename = 'C:\Users\ZBoling\Documents\property.csv';
        $filename = 'Z:\IT_Development\Processes\KronosDimensions-API Integrations\metadata.csv';
        $header = ['category', 'key', 'entity', 'label', 'description', 'dataType', 'parameterized', 'views'];
        //$header = ['key', 'propertyKey', 'propertyValue'];
        $fp = fopen($filename, 'w');

        fputcsv($fp, $header);
        foreach ($body as $bod) {
            //dd($bod);
            $dictionary = [
                data_get($bod, 'categories.0', ''),
                data_get($bod, 'key'),
                data_get($bod, 'metadata.entity'),
                data_get($bod, 'label'),
                data_get($bod, 'metadata.description'),
                data_get($bod, 'metadata.dataType', ''),
                data_get($bod, 'metadata.parameterized', ''),
                implode('; ', data_get($bod, 'metadata.views', ''))
            ];
            $key = data_get($bod, 'key');


            /*
                        //get properties
                        if(count($bod) > 4) {
                            //dd($bod);
                            $properties = array_get($bod,'properties');
                            $metadata = array_get($bod,'metadata');
                            dd($metadata);
                            foreach ($properties as $property){
                                $propertyCSV = [$key, data_get($property,'key'), data_get($property,'value')];
                                fputcsv($fp,$propertyCSV);
                            }

                            //dd($properties);
                        }
            */

            fputcsv($fp, $dictionary);
            //$data = implode(',',$dictionary);
            //$data = $data . "\n";
            //dd($data);
        }
        fclose($fp);
        //$data
        //dd($data);

        //file_put_contents($filename,$data);
        //dd($data);

        dd($body);

        return $body;
    }

    public function getDataDictionaryMetadata()
    {

        $client = $this->Client();

        $url = 'commons/data_dictionary/metadata';

        $response = $client->get($url);

        $body = json_decode($response->getBody(), true);

        dd($body);
    }

    public function getDataDictionaryDataElements()
    {

        $client = $this->Client();

        $url = 'commons/data_dictionary/data_elements';

        $response = $client->get($url);

        $body = json_decode($response->getBody(), true);

        dd($body);
    }

    /*** Schedule ***/
    public function getSchedule()
    {
        $jsonString = '';

        $client = $this->Client();

        $url = 'scheduling/schedule';

        $response = $client->get($url, [
            'query' => [
                'start_date' => '2020-01-01',
                'end_date' => '2020-04-30',
                //'employee_id' => 1038,
                //'person_number' => 85580,
                'hyperfind_id' => 1,
                'hyperfind_name' => 'All Home',
                //'location_id' => '',
                //'location_path' => '',
                'exclude_breaks' => false,
                //'order_by' => ''
            ]
        ]);

        $body = $response->getBody();

        $body = \GuzzleHttp\json_decode($body, true);
        //dd($bodies);

        //$shifts = data_get($body,'shifts');
        $payCodeEdits = data_get($body, 'payCodeEdits');
        //$employees = data_get($body,'employees');
        //$scheduleDayList = data_get($body,'scheduleDayList');

        //$shifts = array_slice($shifts, 0, 30);
        //$payCodeEdits = array_slice($payCodeEdits, 0, 30);
        //$employees = array_slice($employees, 0, 30);
        //$scheduleDayList = array_slice($scheduleDayList, 0, 30);

        //dd($shifts);
        dd($payCodeEdits);
        //dd($employees);
        $payCodeEditsDot = array();
        $offDays = array();

        foreach ($payCodeEdits as $payCodeEdit) {
            $employeeID = data_get($payCodeEdit, 'employee.id');
            $employeeNumber = data_get($payCodeEdit, 'employee.qualifier');
            $startDate = data_get($payCodeEdit, 'startDate');
            $endDate = data_get($payCodeEdit, 'endDate');
            $startTime = data_get($payCodeEdit, 'startTime');
            $endTime = data_get($payCodeEdit, 'endTime');
            $payCodeID = data_get($payCodeEdit, 'payCodeRef.id');
            $payCodeName = data_get($payCodeEdit, 'payCodeRef.qualifier');

            $startDateTime = Carbon::createFromFormat('Y-m-d H:i:s', $startDate . ' ' . $startTime)->toDateTimeString();
            $endDateTime = Carbon::createFromFormat('Y-m-d H:i:s', $endDate . ' ' . $endTime)->toDateTimeString();

            $offDays[] = [
                'employeeID' => $employeeID,
                'employeeNumber' => $employeeNumber,
                'startDate' => $startDate,
                'endDate' => $endDate,
                'startTime' => $startTime,
                'endTime' => $endTime,
                'startDateTime' => $startDateTime,
                'endDateTime' => $endDateTime,
                'payCodeID' => $payCodeID,
                'payCodeName' => $payCodeName
            ];

            // $payCodeEditsDot[] = ARR::dot($payCodeEdit);
        }
        //dd($calendarData);

        //$payCodeEditIDs = array_column($payCodeEdits,'id');
        //dd($payCodeEditsDot);

        return $offDays;

    }

    public function getPaycodeData()
    {
        $dateTime = Carbon::now();
        $startDate = $dateTime->startOfYear()->toDateString();
        $endDate = $dateTime->endOfYear()->toDateString();

        //$bodyRequest = '{"select":[{"key":"CORE_PAYCODE_IS_COMBINED","alias":"CORE_PAYCODE_IS_COMBINED"},{"key":"CORE_PAYCODE_IS_EXCUSED_ABSENCE","alias":"CORE_PAYCODE_IS_EXCUSED_ABSENCE"},{"key":"CORE_PAYCODE_IS_MONEY","alias":"CORE_PAYCODE_IS_MONEY"},{"key":"CORE_PAYCODE_IS_TOTALS","alias":"CORE_PAYCODE_IS_TOTALS"},{"key":"CORE_PAYCODE_SCHEDULED_HOURS_TYPE","alias":"CORE_PAYCODE_SCHEDULED_HOURS_TYPE"},{"key":"CORE_PAYCODE_SHORT_NAME","alias":"CORE_PAYCODE_SHORT_NAME"},{"key":"CORE_PAYCODE_UNIT","alias":"CORE_PAYCODE_UNIT"},{"key":"CORE_PAYCODE_WAGE_ADDITION","alias":"CORE_PAYCODE_WAGE_ADDITION"},{"key":"CORE_PAYCODE_WAGE_MULTIPLIER","alias":"CORE_PAYCODE_WAGE_MULTIPLIER"},{"key":"CORE_PAYCODE","alias":"CORE_PAYCODE"},{"key":"CORE_PAYCODE_TYPE","alias":"CORE_PAYCODE_TYPE"},{"key":"TK_ACTUAL_APPLY_DATE","alias":"TK_ACTUAL_APPLY_DATE"},{"key":"TK_ACTUAL_APPLY_DATE_EXCLUDE_CORRECTIONS","alias":"TK_ACTUAL_APPLY_DATE_EXCLUDE_CORRECTIONS"},{"key":"TK_ACTUAL_JOB_TRANSFER_EXCLUDE_CORRECTIONS","alias":"TK_ACTUAL_JOB_TRANSFER_EXCLUDE_CORRECTIONS"},{"key":"TK_ACTUAL_JOB_TRANSFER_ONLY_CORRECTIONS","alias":"TK_ACTUAL_JOB_TRANSFER_ONLY_CORRECTIONS"},{"key":"TK_ACTUAL_LABOR_TRANSFER","alias":"TK_ACTUAL_LABOR_TRANSFER"},{"key":"TK_ACTUAL_LABOR_TRANSFER_EXCLUDE_CORRECTIONS","alias":"TK_ACTUAL_LABOR_TRANSFER_EXCLUDE_CORRECTIONS"},{"key":"TK_ACTUAL_LABOR_TRANSFER_ONLY_CORRECTIONS","alias":"TK_ACTUAL_LABOR_TRANSFER_ONLY_CORRECTIONS"},{"key":"TK_ACTUAL_PAYPERIOD_NUMBER","alias":"TK_ACTUAL_PAYPERIOD_NUMBER"},{"key":"TK_ACTUAL_PAYPERIOD_NUMBER_EXCLUDE_CORRECTIONS","alias":"TK_ACTUAL_PAYPERIOD_NUMBER_EXCLUDE_CORRECTIONS"},{"key":"TK_ACTUAL_PAYPERIOD_NUMBER_ONLY_CORRECTIONS","alias":"TK_ACTUAL_PAYPERIOD_NUMBER_ONLY_CORRECTIONS"},{"key":"TK_ACTUAL_PAYPERIOD_WEEK","alias":"TK_ACTUAL_PAYPERIOD_WEEK"},{"key":"TK_ACTUAL_PAYPERIOD_WEEK_EXCLUDE_CORRECTIONS","alias":"TK_ACTUAL_PAYPERIOD_WEEK_EXCLUDE_CORRECTIONS"},{"key":"TK_ACTUAL_PAYPERIOD_WEEK_ONLY_CORRECTIONS","alias":"TK_ACTUAL_PAYPERIOD_WEEK_ONLY_CORRECTIONS"},{"key":"TK_ACTUAL_SIGNED_OFF","alias":"TK_ACTUAL_SIGNED_OFF"},{"key":"TK_ACTUAL_SIGNED_OFF_EXCLUDE_CORRECTIONS","alias":"TK_ACTUAL_SIGNED_OFF_EXCLUDE_CORRECTIONS"},{"key":"TK_ACTUAL_SIGNED_OFF_ONLY_CORRECTIONS","alias":"TK_ACTUAL_SIGNED_OFF_ONLY_CORRECTIONS"},{"key":"TK_ACTUAL_TOTAL_DAYS_EXCLUDE_CORRECTIONS","alias":"TK_ACTUAL_TOTAL_DAYS_EXCLUDE_CORRECTIONS"},{"key":"TK_ACTUAL_TOTAL_DAYS_ONLY_CORRECTIONS","alias":"TK_ACTUAL_TOTAL_DAYS_ONLY_CORRECTIONS"},{"key":"TK_ACTUAL_TOTAL_HOURS","alias":"TK_ACTUAL_TOTAL_HOURS"},{"key":"TK_ACTUAL_TOTAL_HOURS_EXCLUDE_CORRECTIONS","alias":"TK_ACTUAL_TOTAL_HOURS_EXCLUDE_CORRECTIONS"},{"key":"TK_ACTUAL_TOTAL_HOURS_ONLY_CORRECTIONS","alias":"TK_ACTUAL_TOTAL_HOURS_ONLY_CORRECTIONS"},{"key":"TK_ACTUAL_TOTAL_WAGES","alias":"TK_ACTUAL_TOTAL_WAGES"},{"key":"TK_ACTUAL_TOTAL_WAGES_EXCLUDE_CORRECTIONS","alias":"TK_ACTUAL_TOTAL_WAGES_EXCLUDE_CORRECTIONS"},{"key":"TK_ACTUAL_TOTAL_WAGES_ONLY_CORRECTIONS","alias":"TK_ACTUAL_TOTAL_WAGES_ONLY_CORRECTIONS"},{"key":"TK_GENIE_ACTUAL_TOTAL_DAYS_EXCLUDE_CORRECTIONS","alias":"TK_GENIE_ACTUAL_TOTAL_DAYS_EXCLUDE_CORRECTIONS"},{"key":"TK_GENIE_ACTUAL_TOTAL_DAYS_ONLY_CORRECTIONS","alias":"TK_GENIE_ACTUAL_TOTAL_DAYS_ONLY_CORRECTIONS"},{"key":"TK_GENIE_ACTUAL_TOTAL_HOURS","alias":"TK_GENIE_ACTUAL_TOTAL_HOURS"},{"key":"TK_GENIE_ACTUAL_TOTAL_HOURS_EXCLUDE_CORRECTIONS","alias":"TK_GENIE_ACTUAL_TOTAL_HOURS_EXCLUDE_CORRECTIONS"},{"key":"TK_GENIE_ACTUAL_TOTAL_HOURS_ONLY_CORRECTIONS","alias":"TK_GENIE_ACTUAL_TOTAL_HOURS_ONLY_CORRECTIONS"},{"key":"TK_GENIE_ACTUAL_TOTAL_WAGES","alias":"TK_GENIE_ACTUAL_TOTAL_WAGES"},{"key":"TK_GENIE_ACTUAL_TOTAL_WAGES_EXCLUDE_CORRECTIONS","alias":"TK_GENIE_ACTUAL_TOTAL_WAGES_EXCLUDE_CORRECTIONS"},{"key":"TK_GENIE_ACTUAL_TOTAL_WAGES_ONLY_CORRECTIONS","alias":"TK_GENIE_ACTUAL_TOTAL_WAGES_ONLY_CORRECTIONS"},{"key":"TK_GENIE_PROJECTED_TOTAL_DAYS","alias":"TK_GENIE_PROJECTED_TOTAL_DAYS"},{"key":"TK_GENIE_PROJECTED_TOTAL_DAYS_EXCLUDE_CORRECTIONS","alias":"TK_GENIE_PROJECTED_TOTAL_DAYS_EXCLUDE_CORRECTIONS"},{"key":"TK_GENIE_PROJECTED_TOTAL_DAYS_ONLY_CORRECTIONS","alias":"TK_GENIE_PROJECTED_TOTAL_DAYS_ONLY_CORRECTIONS"},{"key":"TK_GENIE_PROJECTED_TOTAL_WAGES","alias":"TK_GENIE_PROJECTED_TOTAL_WAGES"},{"key":"TK_GENIE_PROJECTED_TOTAL_WAGES_EXCLUDE_CORRECTIONS","alias":"TK_GENIE_PROJECTED_TOTAL_WAGES_EXCLUDE_CORRECTIONS"},{"key":"TK_GENIE_PROJECTED_TOTAL_WAGES_ONLY_CORRECTIONS","alias":"TK_GENIE_PROJECTED_TOTAL_WAGES_ONLY_CORRECTIONS"},{"key":"TK_GENIE_SCHEDULED_TOTAL_HOURS","alias":"TK_GENIE_SCHEDULED_TOTAL_HOURS"},{"key":"TK_GENIE_SCHEDULED_TOTAL_WAGES","alias":"TK_GENIE_SCHEDULED_TOTAL_WAGES"},{"key":"TK_PROJECTED_APPLY_DATE","alias":"TK_PROJECTED_APPLY_DATE"},{"key":"TK_PROJECTED_APPLY_DATE_EXCLUDE_CORRECTIONS","alias":"TK_PROJECTED_APPLY_DATE_EXCLUDE_CORRECTIONS"},{"key":"TK_PROJECTED_APPLY_DATE_ONLY_CORRECTIONS","alias":"TK_PROJECTED_APPLY_DATE_ONLY_CORRECTIONS"},{"key":"TK_PROJECTED_JOB_TRANSFER","alias":"TK_PROJECTED_JOB_TRANSFER"},{"key":"TK_PROJECTED_JOB_TRANSFER_EXCLUDE_CORRECTIONS","alias":"TK_PROJECTED_JOB_TRANSFER_EXCLUDE_CORRECTIONS"},{"key":"TK_PROJECTED_LABOR_TRANSFER_EXCLUDE_CORRECTIONS","alias":"TK_PROJECTED_LABOR_TRANSFER_EXCLUDE_CORRECTIONS"},{"key":"TK_PROJECTED_LABOR_TRANSFER_ONLY_CORRECTIONS","alias":"TK_PROJECTED_LABOR_TRANSFER_ONLY_CORRECTIONS"},{"key":"TK_PROJECTED_TOTAL_DAYS","alias":"TK_PROJECTED_TOTAL_DAYS"},{"key":"TK_PROJECTED_TOTAL_DAYS_EXCLUDE_CORRECTIONS","alias":"TK_PROJECTED_TOTAL_DAYS_EXCLUDE_CORRECTIONS"},{"key":"TK_PROJECTED_TOTAL_DAYS_ONLY_CORRECTIONS","alias":"TK_PROJECTED_TOTAL_DAYS_ONLY_CORRECTIONS"},{"key":"TK_PROJECTED_TOTAL_HOURS","alias":"TK_PROJECTED_TOTAL_HOURS"},{"key":"TK_PROJECTED_TOTAL_HOURS_EXCLUDE_CORRECTIONS","alias":"TK_PROJECTED_TOTAL_HOURS_EXCLUDE_CORRECTIONS"},{"key":"TK_PROJECTED_TOTAL_HOURS_ONLY_CORRECTIONS","alias":"TK_PROJECTED_TOTAL_HOURS_ONLY_CORRECTIONS"},{"key":"TK_PROJECTED_TOTAL_WAGES","alias":"TK_PROJECTED_TOTAL_WAGES"},{"key":"TK_PROJECTED_TOTAL_WAGES_EXCLUDE_CORRECTIONS","alias":"TK_PROJECTED_TOTAL_WAGES_EXCLUDE_CORRECTIONS"},{"key":"TK_PROJECTED_TOTAL_WAGES_ONLY_CORRECTIONS","alias":"TK_PROJECTED_TOTAL_WAGES_ONLY_CORRECTIONS"},{"key":"TK_SCHEDULED_APPLY_DATE","alias":"TK_SCHEDULED_APPLY_DATE"},{"key":"TK_SCHEDULED_JOB_TRANSFER","alias":"TK_SCHEDULED_JOB_TRANSFER"},{"key":"TK_SCHEDULED_LABOR_TRANSFER","alias":"TK_SCHEDULED_LABOR_TRANSFER"},{"key":"TK_SCHEDULED_TOTAL_DAYS","alias":"TK_SCHEDULED_TOTAL_DAYS"},{"key":"TK_SCHEDULED_TOTAL_HOURS","alias":"TK_SCHEDULED_TOTAL_HOURS"},{"key":"TK_SCHEDULED_TOTAL_WAGES","alias":"TK_SCHEDULED_TOTAL_WAGES"},{"key":"TK_ACTUAL_WAGE_ADD","alias":"TK_ACTUAL_WAGE_ADD"},{"key":"TK_ACTUAL_WAGE_MULTIPLIER","alias":"TK_ACTUAL_WAGE_MULTIPLIER"},{"key":"TK_ACTUAL_WAGE_ADD_EXCLUDE_CORRECTIONS","alias":"TK_ACTUAL_WAGE_ADD_EXCLUDE_CORRECTIONS"},{"key":"TK_ACTUAL_WAGE_MULTIPLIER_EXCLUDE_CORRECTIONS","alias":"TK_ACTUAL_WAGE_MULTIPLIER_EXCLUDE_CORRECTIONS"},{"key":"TK_ACTUAL_WAGE_ADD_ONLY_CORRECTIONS","alias":"TK_ACTUAL_WAGE_ADD_ONLY_CORRECTIONS"},{"key":"TK_ACTUAL_WAGE_MULTIPLIER_ONLY_CORRECTIONS","alias":"TK_ACTUAL_WAGE_MULTIPLIER_ONLY_CORRECTIONS"},{"key":"CORE_PAYCODE_VISIBLE_IN_REPORTS_TOTALS_INDICATOR","alias":"CORE_PAYCODE_VISIBLE_IN_REPORTS_TOTALS_INDICATOR"},{"key":"CORE_PAYCODE_VISIBLE_ON_TOTALS_ADDON_INDICATOR","alias":"CORE_PAYCODE_VISIBLE_ON_TOTALS_ADDON_INDICATOR"},{"key":"TK_ACTUAL_APPLY_DATE_ONLY_CORRECTIONS","alias":"TK_ACTUAL_APPLY_DATE_ONLY_CORRECTIONS"},{"key":"TK_ACTUAL_JOB_TRANSFER","alias":"TK_ACTUAL_JOB_TRANSFER"},{"key":"TK_ACTUAL_TOTAL_DAYS","alias":"TK_ACTUAL_TOTAL_DAYS"},{"key":"TK_GENIE_ACTUAL_TOTAL_DAYS","alias":"TK_GENIE_ACTUAL_TOTAL_DAYS"},{"key":"TK_GENIE_PROJECTED_TOTAL_HOURS","alias":"TK_GENIE_PROJECTED_TOTAL_HOURS"},{"key":"TK_GENIE_PROJECTED_TOTAL_HOURS_EXCLUDE_CORRECTIONS","alias":"TK_GENIE_PROJECTED_TOTAL_HOURS_EXCLUDE_CORRECTIONS"},{"key":"TK_GENIE_PROJECTED_TOTAL_HOURS_ONLY_CORRECTIONS","alias":"TK_GENIE_PROJECTED_TOTAL_HOURS_ONLY_CORRECTIONS"},{"key":"TK_GENIE_SCHEDULED_TOTAL_DAYS","alias":"TK_GENIE_SCHEDULED_TOTAL_DAYS"},{"key":"TK_PROJECTED_JOB_TRANSFER_ONLY_CORRECTIONS","alias":"TK_PROJECTED_JOB_TRANSFER_ONLY_CORRECTIONS"},{"key":"TK_PROJECTED_LABOR_TRANSFER","alias":"TK_PROJECTED_LABOR_TRANSFER"}],"from":{"view":"EMP","employeeSet":{"hyperfind":{"qualifier":"All Home"},"dateRange":{"startDate":"' . $startDate . 'T00:00","EndDate":"' . $endDate . 'T00:00"}}},"where":[{"key":"PEOPLE_ACCRUAL_PROFILE_NAME","alias":"PEOPLE_ACCRUAL_PROFILE_NAME","operator":"STARTS_WITH","values":["IBEW"]}]}';
        //$bodyRequest = '{"select":[{"key":"CORE_PAYCODE_IS_COMBINED","alias":"CORE_PAYCODE_IS_COMBINED"},{"key":"CORE_PAYCODE_IS_EXCUSED_ABSENCE","alias":"CORE_PAYCODE_IS_EXCUSED_ABSENCE"},{"key":"CORE_PAYCODE_IS_MONEY","alias":"CORE_PAYCODE_IS_MONEY"},{"key":"CORE_PAYCODE_IS_TOTALS","alias":"CORE_PAYCODE_IS_TOTALS"},{"key":"CORE_PAYCODE_SCHEDULED_HOURS_TYPE","alias":"CORE_PAYCODE_SCHEDULED_HOURS_TYPE"},{"key":"CORE_PAYCODE_SHORT_NAME","alias":"CORE_PAYCODE_SHORT_NAME"},{"key":"CORE_PAYCODE_UNIT","alias":"CORE_PAYCODE_UNIT"},{"key":"CORE_PAYCODE_WAGE_ADDITION","alias":"CORE_PAYCODE_WAGE_ADDITION"},{"key":"CORE_PAYCODE_WAGE_MULTIPLIER","alias":"CORE_PAYCODE_WAGE_MULTIPLIER"},{"key":"CORE_PAYCODE","alias":"CORE_PAYCODE"},{"key":"CORE_PAYCODE_TYPE","alias":"CORE_PAYCODE_TYPE"},{"key":"TK_ACTUAL_APPLY_DATE","alias":"TK_ACTUAL_APPLY_DATE"},{"key":"TK_ACTUAL_APPLY_DATE_EXCLUDE_CORRECTIONS","alias":"TK_ACTUAL_APPLY_DATE_EXCLUDE_CORRECTIONS"},{"key":"TK_ACTUAL_JOB_TRANSFER_EXCLUDE_CORRECTIONS","alias":"TK_ACTUAL_JOB_TRANSFER_EXCLUDE_CORRECTIONS"},{"key":"TK_ACTUAL_JOB_TRANSFER_ONLY_CORRECTIONS","alias":"TK_ACTUAL_JOB_TRANSFER_ONLY_CORRECTIONS"},{"key":"TK_ACTUAL_LABOR_TRANSFER","alias":"TK_ACTUAL_LABOR_TRANSFER"},{"key":"TK_ACTUAL_LABOR_TRANSFER_EXCLUDE_CORRECTIONS","alias":"TK_ACTUAL_LABOR_TRANSFER_EXCLUDE_CORRECTIONS"},{"key":"TK_ACTUAL_LABOR_TRANSFER_ONLY_CORRECTIONS","alias":"TK_ACTUAL_LABOR_TRANSFER_ONLY_CORRECTIONS"},{"key":"TK_ACTUAL_PAYPERIOD_NUMBER","alias":"TK_ACTUAL_PAYPERIOD_NUMBER"},{"key":"TK_ACTUAL_PAYPERIOD_NUMBER_EXCLUDE_CORRECTIONS","alias":"TK_ACTUAL_PAYPERIOD_NUMBER_EXCLUDE_CORRECTIONS"},{"key":"TK_ACTUAL_PAYPERIOD_NUMBER_ONLY_CORRECTIONS","alias":"TK_ACTUAL_PAYPERIOD_NUMBER_ONLY_CORRECTIONS"},{"key":"TK_ACTUAL_PAYPERIOD_WEEK","alias":"TK_ACTUAL_PAYPERIOD_WEEK"},{"key":"TK_ACTUAL_PAYPERIOD_WEEK_EXCLUDE_CORRECTIONS","alias":"TK_ACTUAL_PAYPERIOD_WEEK_EXCLUDE_CORRECTIONS"},{"key":"TK_ACTUAL_PAYPERIOD_WEEK_ONLY_CORRECTIONS","alias":"TK_ACTUAL_PAYPERIOD_WEEK_ONLY_CORRECTIONS"},{"key":"TK_ACTUAL_SIGNED_OFF","alias":"TK_ACTUAL_SIGNED_OFF"},{"key":"TK_ACTUAL_SIGNED_OFF_EXCLUDE_CORRECTIONS","alias":"TK_ACTUAL_SIGNED_OFF_EXCLUDE_CORRECTIONS"},{"key":"TK_ACTUAL_SIGNED_OFF_ONLY_CORRECTIONS","alias":"TK_ACTUAL_SIGNED_OFF_ONLY_CORRECTIONS"},{"key":"TK_ACTUAL_TOTAL_DAYS_ONLY_CORRECTIONS","alias":"TK_ACTUAL_TOTAL_DAYS_ONLY_CORRECTIONS"},{"key":"TK_ACTUAL_TOTAL_HOURS","alias":"TK_ACTUAL_TOTAL_HOURS"},{"key":"TK_ACTUAL_TOTAL_HOURS_EXCLUDE_CORRECTIONS","alias":"TK_ACTUAL_TOTAL_HOURS_EXCLUDE_CORRECTIONS"},{"key":"TK_ACTUAL_TOTAL_HOURS_ONLY_CORRECTIONS","alias":"TK_ACTUAL_TOTAL_HOURS_ONLY_CORRECTIONS"},{"key":"TK_ACTUAL_TOTAL_WAGES","alias":"TK_ACTUAL_TOTAL_WAGES"},{"key":"TK_ACTUAL_TOTAL_WAGES_EXCLUDE_CORRECTIONS","alias":"TK_ACTUAL_TOTAL_WAGES_EXCLUDE_CORRECTIONS"},{"key":"TK_ACTUAL_TOTAL_WAGES_ONLY_CORRECTIONS","alias":"TK_ACTUAL_TOTAL_WAGES_ONLY_CORRECTIONS"},{"key":"TK_GENIE_ACTUAL_TOTAL_DAYS_EXCLUDE_CORRECTIONS","alias":"TK_GENIE_ACTUAL_TOTAL_DAYS_EXCLUDE_CORRECTIONS"},{"key":"TK_GENIE_ACTUAL_TOTAL_DAYS_ONLY_CORRECTIONS","alias":"TK_GENIE_ACTUAL_TOTAL_DAYS_ONLY_CORRECTIONS"},{"key":"TK_GENIE_ACTUAL_TOTAL_HOURS","alias":"TK_GENIE_ACTUAL_TOTAL_HOURS"},{"key":"TK_GENIE_ACTUAL_TOTAL_HOURS_EXCLUDE_CORRECTIONS","alias":"TK_GENIE_ACTUAL_TOTAL_HOURS_EXCLUDE_CORRECTIONS"},{"key":"TK_GENIE_ACTUAL_TOTAL_HOURS_ONLY_CORRECTIONS","alias":"TK_GENIE_ACTUAL_TOTAL_HOURS_ONLY_CORRECTIONS"},{"key":"TK_GENIE_ACTUAL_TOTAL_WAGES","alias":"TK_GENIE_ACTUAL_TOTAL_WAGES"},{"key":"TK_GENIE_ACTUAL_TOTAL_WAGES_EXCLUDE_CORRECTIONS","alias":"TK_GENIE_ACTUAL_TOTAL_WAGES_EXCLUDE_CORRECTIONS"},{"key":"TK_GENIE_ACTUAL_TOTAL_WAGES_ONLY_CORRECTIONS","alias":"TK_GENIE_ACTUAL_TOTAL_WAGES_ONLY_CORRECTIONS"},{"key":"TK_GENIE_PROJECTED_TOTAL_DAYS","alias":"TK_GENIE_PROJECTED_TOTAL_DAYS"},{"key":"TK_GENIE_PROJECTED_TOTAL_DAYS_EXCLUDE_CORRECTIONS","alias":"TK_GENIE_PROJECTED_TOTAL_DAYS_EXCLUDE_CORRECTIONS"},{"key":"TK_GENIE_PROJECTED_TOTAL_DAYS_ONLY_CORRECTIONS","alias":"TK_GENIE_PROJECTED_TOTAL_DAYS_ONLY_CORRECTIONS"},{"key":"TK_GENIE_PROJECTED_TOTAL_WAGES","alias":"TK_GENIE_PROJECTED_TOTAL_WAGES"},{"key":"TK_GENIE_PROJECTED_TOTAL_WAGES_EXCLUDE_CORRECTIONS","alias":"TK_GENIE_PROJECTED_TOTAL_WAGES_EXCLUDE_CORRECTIONS"},{"key":"TK_GENIE_PROJECTED_TOTAL_WAGES_ONLY_CORRECTIONS","alias":"TK_GENIE_PROJECTED_TOTAL_WAGES_ONLY_CORRECTIONS"},{"key":"TK_GENIE_SCHEDULED_TOTAL_HOURS","alias":"TK_GENIE_SCHEDULED_TOTAL_HOURS"},{"key":"TK_GENIE_SCHEDULED_TOTAL_WAGES","alias":"TK_GENIE_SCHEDULED_TOTAL_WAGES"},{"key":"TK_PROJECTED_APPLY_DATE","alias":"TK_PROJECTED_APPLY_DATE"},{"key":"TK_PROJECTED_APPLY_DATE_EXCLUDE_CORRECTIONS","alias":"TK_PROJECTED_APPLY_DATE_EXCLUDE_CORRECTIONS"},{"key":"TK_PROJECTED_APPLY_DATE_ONLY_CORRECTIONS","alias":"TK_PROJECTED_APPLY_DATE_ONLY_CORRECTIONS"},{"key":"TK_PROJECTED_JOB_TRANSFER","alias":"TK_PROJECTED_JOB_TRANSFER"},{"key":"TK_PROJECTED_JOB_TRANSFER_EXCLUDE_CORRECTIONS","alias":"TK_PROJECTED_JOB_TRANSFER_EXCLUDE_CORRECTIONS"},{"key":"TK_PROJECTED_LABOR_TRANSFER_EXCLUDE_CORRECTIONS","alias":"TK_PROJECTED_LABOR_TRANSFER_EXCLUDE_CORRECTIONS"},{"key":"TK_PROJECTED_LABOR_TRANSFER_ONLY_CORRECTIONS","alias":"TK_PROJECTED_LABOR_TRANSFER_ONLY_CORRECTIONS"},{"key":"TK_PROJECTED_TOTAL_DAYS","alias":"TK_PROJECTED_TOTAL_DAYS"},{"key":"TK_PROJECTED_TOTAL_DAYS_EXCLUDE_CORRECTIONS","alias":"TK_PROJECTED_TOTAL_DAYS_EXCLUDE_CORRECTIONS"},{"key":"TK_PROJECTED_TOTAL_DAYS_ONLY_CORRECTIONS","alias":"TK_PROJECTED_TOTAL_DAYS_ONLY_CORRECTIONS"},{"key":"TK_PROJECTED_TOTAL_HOURS","alias":"TK_PROJECTED_TOTAL_HOURS"},{"key":"TK_PROJECTED_TOTAL_HOURS_EXCLUDE_CORRECTIONS","alias":"TK_PROJECTED_TOTAL_HOURS_EXCLUDE_CORRECTIONS"},{"key":"TK_PROJECTED_TOTAL_HOURS_ONLY_CORRECTIONS","alias":"TK_PROJECTED_TOTAL_HOURS_ONLY_CORRECTIONS"},{"key":"TK_PROJECTED_TOTAL_WAGES","alias":"TK_PROJECTED_TOTAL_WAGES"},{"key":"TK_PROJECTED_TOTAL_WAGES_EXCLUDE_CORRECTIONS","alias":"TK_PROJECTED_TOTAL_WAGES_EXCLUDE_CORRECTIONS"},{"key":"TK_PROJECTED_TOTAL_WAGES_ONLY_CORRECTIONS","alias":"TK_PROJECTED_TOTAL_WAGES_ONLY_CORRECTIONS"},{"key":"TK_SCHEDULED_APPLY_DATE","alias":"TK_SCHEDULED_APPLY_DATE"},{"key":"TK_SCHEDULED_JOB_TRANSFER","alias":"TK_SCHEDULED_JOB_TRANSFER"},{"key":"TK_SCHEDULED_LABOR_TRANSFER","alias":"TK_SCHEDULED_LABOR_TRANSFER"},{"key":"TK_SCHEDULED_TOTAL_DAYS","alias":"TK_SCHEDULED_TOTAL_DAYS"},{"key":"TK_SCHEDULED_TOTAL_HOURS","alias":"TK_SCHEDULED_TOTAL_HOURS"},{"key":"TK_SCHEDULED_TOTAL_WAGES","alias":"TK_SCHEDULED_TOTAL_WAGES"},{"key":"TK_ACTUAL_WAGE_ADD","alias":"TK_ACTUAL_WAGE_ADD"},{"key":"TK_ACTUAL_WAGE_MULTIPLIER","alias":"TK_ACTUAL_WAGE_MULTIPLIER"},{"key":"TK_ACTUAL_WAGE_ADD_EXCLUDE_CORRECTIONS","alias":"TK_ACTUAL_WAGE_ADD_EXCLUDE_CORRECTIONS"},{"key":"TK_ACTUAL_WAGE_MULTIPLIER_EXCLUDE_CORRECTIONS","alias":"TK_ACTUAL_WAGE_MULTIPLIER_EXCLUDE_CORRECTIONS"},{"key":"TK_ACTUAL_WAGE_ADD_ONLY_CORRECTIONS","alias":"TK_ACTUAL_WAGE_ADD_ONLY_CORRECTIONS"},{"key":"TK_ACTUAL_WAGE_MULTIPLIER_ONLY_CORRECTIONS","alias":"TK_ACTUAL_WAGE_MULTIPLIER_ONLY_CORRECTIONS"},{"key":"CORE_PAYCODE_VISIBLE_IN_REPORTS_TOTALS_INDICATOR","alias":"CORE_PAYCODE_VISIBLE_IN_REPORTS_TOTALS_INDICATOR"},{"key":"CORE_PAYCODE_VISIBLE_ON_TOTALS_ADDON_INDICATOR","alias":"CORE_PAYCODE_VISIBLE_ON_TOTALS_ADDON_INDICATOR"},{"key":"TK_ACTUAL_APPLY_DATE_ONLY_CORRECTIONS","alias":"TK_ACTUAL_APPLY_DATE_ONLY_CORRECTIONS"},{"key":"TK_ACTUAL_JOB_TRANSFER","alias":"TK_ACTUAL_JOB_TRANSFER"},{"key":"TK_ACTUAL_TOTAL_DAYS","alias":"TK_ACTUAL_TOTAL_DAYS"},{"key":"TK_GENIE_ACTUAL_TOTAL_DAYS","alias":"TK_GENIE_ACTUAL_TOTAL_DAYS"},{"key":"TK_GENIE_PROJECTED_TOTAL_HOURS","alias":"TK_GENIE_PROJECTED_TOTAL_HOURS"},{"key":"TK_GENIE_PROJECTED_TOTAL_HOURS_EXCLUDE_CORRECTIONS","alias":"TK_GENIE_PROJECTED_TOTAL_HOURS_EXCLUDE_CORRECTIONS"},{"key":"TK_GENIE_PROJECTED_TOTAL_HOURS_ONLY_CORRECTIONS","alias":"TK_GENIE_PROJECTED_TOTAL_HOURS_ONLY_CORRECTIONS"},{"key":"TK_GENIE_SCHEDULED_TOTAL_DAYS","alias":"TK_GENIE_SCHEDULED_TOTAL_DAYS"},{"key":"TK_PROJECTED_JOB_TRANSFER_ONLY_CORRECTIONS","alias":"TK_PROJECTED_JOB_TRANSFER_ONLY_CORRECTIONS"},{"key":"TK_PROJECTED_LABOR_TRANSFER","alias":"TK_PROJECTED_LABOR_TRANSFER"}],"from":{"view":"EMP","employeeSet":{"hyperfind":{"qualifier":"All Home"},"dateRange":{"startDate":"' . $startDate . 'T00:00","EndDate":"' . $endDate . 'T00:00"}}},"where":[{"key":"PEOPLE_ACCRUAL_PROFILE_NAME","alias":"PEOPLE_ACCRUAL_PROFILE_NAME","operator":"STARTS_WITH","values":["IBEW"]}]}';
        //$bodyRequest = '{"select":[{"key":"CORE_PAYCODE_IS_COMBINED","alias":"CORE_PAYCODE_IS_COMBINED"},{"key":"CORE_PAYCODE_IS_EXCUSED_ABSENCE","alias":"CORE_PAYCODE_IS_EXCUSED_ABSENCE"},{"key":"CORE_PAYCODE_IS_MONEY","alias":"CORE_PAYCODE_IS_MONEY"},{"key":"CORE_PAYCODE_IS_TOTALS","alias":"CORE_PAYCODE_IS_TOTALS"},{"key":"CORE_PAYCODE_SCHEDULED_HOURS_TYPE","alias":"CORE_PAYCODE_SCHEDULED_HOURS_TYPE"},{"key":"CORE_PAYCODE_SHORT_NAME","alias":"CORE_PAYCODE_SHORT_NAME"},{"key":"CORE_PAYCODE_UNIT","alias":"CORE_PAYCODE_UNIT"},{"key":"CORE_PAYCODE_WAGE_ADDITION","alias":"CORE_PAYCODE_WAGE_ADDITION"},{"key":"CORE_PAYCODE_WAGE_MULTIPLIER","alias":"CORE_PAYCODE_WAGE_MULTIPLIER"},{"key":"CORE_PAYCODE","alias":"CORE_PAYCODE"},{"key":"CORE_PAYCODE_TYPE","alias":"CORE_PAYCODE_TYPE"},{"key":"TK_ACTUAL_APPLY_DATE","alias":"TK_ACTUAL_APPLY_DATE"},{"key":"TK_ACTUAL_APPLY_DATE_EXCLUDE_CORRECTIONS","alias":"TK_ACTUAL_APPLY_DATE_EXCLUDE_CORRECTIONS"},{"key":"TK_ACTUAL_JOB_TRANSFER_EXCLUDE_CORRECTIONS","alias":"TK_ACTUAL_JOB_TRANSFER_EXCLUDE_CORRECTIONS"},{"key":"TK_ACTUAL_JOB_TRANSFER_ONLY_CORRECTIONS","alias":"TK_ACTUAL_JOB_TRANSFER_ONLY_CORRECTIONS"},{"key":"TK_ACTUAL_LABOR_TRANSFER","alias":"TK_ACTUAL_LABOR_TRANSFER"},{"key":"TK_ACTUAL_LABOR_TRANSFER_EXCLUDE_CORRECTIONS","alias":"TK_ACTUAL_LABOR_TRANSFER_EXCLUDE_CORRECTIONS"},{"key":"TK_ACTUAL_LABOR_TRANSFER_ONLY_CORRECTIONS","alias":"TK_ACTUAL_LABOR_TRANSFER_ONLY_CORRECTIONS"},{"key":"TK_ACTUAL_PAYPERIOD_NUMBER","alias":"TK_ACTUAL_PAYPERIOD_NUMBER"},{"key":"TK_ACTUAL_PAYPERIOD_NUMBER_EXCLUDE_CORRECTIONS","alias":"TK_ACTUAL_PAYPERIOD_NUMBER_EXCLUDE_CORRECTIONS"},{"key":"TK_ACTUAL_PAYPERIOD_NUMBER_ONLY_CORRECTIONS","alias":"TK_ACTUAL_PAYPERIOD_NUMBER_ONLY_CORRECTIONS"},{"key":"TK_ACTUAL_PAYPERIOD_WEEK","alias":"TK_ACTUAL_PAYPERIOD_WEEK"},{"key":"TK_ACTUAL_PAYPERIOD_WEEK_EXCLUDE_CORRECTIONS","alias":"TK_ACTUAL_PAYPERIOD_WEEK_EXCLUDE_CORRECTIONS"},{"key":"TK_ACTUAL_PAYPERIOD_WEEK_ONLY_CORRECTIONS","alias":"TK_ACTUAL_PAYPERIOD_WEEK_ONLY_CORRECTIONS"},{"key":"TK_ACTUAL_SIGNED_OFF","alias":"TK_ACTUAL_SIGNED_OFF"},{"key":"TK_ACTUAL_SIGNED_OFF_EXCLUDE_CORRECTIONS","alias":"TK_ACTUAL_SIGNED_OFF_EXCLUDE_CORRECTIONS"},{"key":"TK_ACTUAL_SIGNED_OFF_ONLY_CORRECTIONS","alias":"TK_ACTUAL_SIGNED_OFF_ONLY_CORRECTIONS"},{"key":"TK_ACTUAL_TOTAL_HOURS","alias":"TK_ACTUAL_TOTAL_HOURS"},{"key":"TK_ACTUAL_TOTAL_HOURS_EXCLUDE_CORRECTIONS","alias":"TK_ACTUAL_TOTAL_HOURS_EXCLUDE_CORRECTIONS"},{"key":"TK_ACTUAL_TOTAL_HOURS_ONLY_CORRECTIONS","alias":"TK_ACTUAL_TOTAL_HOURS_ONLY_CORRECTIONS"},{"key":"TK_ACTUAL_TOTAL_WAGES","alias":"TK_ACTUAL_TOTAL_WAGES"},{"key":"TK_ACTUAL_TOTAL_WAGES_EXCLUDE_CORRECTIONS","alias":"TK_ACTUAL_TOTAL_WAGES_EXCLUDE_CORRECTIONS"},{"key":"TK_ACTUAL_TOTAL_WAGES_ONLY_CORRECTIONS","alias":"TK_ACTUAL_TOTAL_WAGES_ONLY_CORRECTIONS"},{"key":"TK_GENIE_ACTUAL_TOTAL_DAYS_EXCLUDE_CORRECTIONS","alias":"TK_GENIE_ACTUAL_TOTAL_DAYS_EXCLUDE_CORRECTIONS"},{"key":"TK_GENIE_ACTUAL_TOTAL_DAYS_ONLY_CORRECTIONS","alias":"TK_GENIE_ACTUAL_TOTAL_DAYS_ONLY_CORRECTIONS"},{"key":"TK_GENIE_ACTUAL_TOTAL_HOURS","alias":"TK_GENIE_ACTUAL_TOTAL_HOURS"},{"key":"TK_GENIE_ACTUAL_TOTAL_HOURS_EXCLUDE_CORRECTIONS","alias":"TK_GENIE_ACTUAL_TOTAL_HOURS_EXCLUDE_CORRECTIONS"},{"key":"TK_GENIE_ACTUAL_TOTAL_HOURS_ONLY_CORRECTIONS","alias":"TK_GENIE_ACTUAL_TOTAL_HOURS_ONLY_CORRECTIONS"},{"key":"TK_GENIE_ACTUAL_TOTAL_WAGES","alias":"TK_GENIE_ACTUAL_TOTAL_WAGES"},{"key":"TK_GENIE_ACTUAL_TOTAL_WAGES_EXCLUDE_CORRECTIONS","alias":"TK_GENIE_ACTUAL_TOTAL_WAGES_EXCLUDE_CORRECTIONS"},{"key":"TK_GENIE_ACTUAL_TOTAL_WAGES_ONLY_CORRECTIONS","alias":"TK_GENIE_ACTUAL_TOTAL_WAGES_ONLY_CORRECTIONS"},{"key":"TK_GENIE_PROJECTED_TOTAL_DAYS","alias":"TK_GENIE_PROJECTED_TOTAL_DAYS"},{"key":"TK_GENIE_PROJECTED_TOTAL_DAYS_EXCLUDE_CORRECTIONS","alias":"TK_GENIE_PROJECTED_TOTAL_DAYS_EXCLUDE_CORRECTIONS"},{"key":"TK_GENIE_PROJECTED_TOTAL_DAYS_ONLY_CORRECTIONS","alias":"TK_GENIE_PROJECTED_TOTAL_DAYS_ONLY_CORRECTIONS"},{"key":"TK_GENIE_PROJECTED_TOTAL_WAGES","alias":"TK_GENIE_PROJECTED_TOTAL_WAGES"},{"key":"TK_GENIE_PROJECTED_TOTAL_WAGES_EXCLUDE_CORRECTIONS","alias":"TK_GENIE_PROJECTED_TOTAL_WAGES_EXCLUDE_CORRECTIONS"},{"key":"TK_GENIE_PROJECTED_TOTAL_WAGES_ONLY_CORRECTIONS","alias":"TK_GENIE_PROJECTED_TOTAL_WAGES_ONLY_CORRECTIONS"},{"key":"TK_GENIE_SCHEDULED_TOTAL_HOURS","alias":"TK_GENIE_SCHEDULED_TOTAL_HOURS"},{"key":"TK_GENIE_SCHEDULED_TOTAL_WAGES","alias":"TK_GENIE_SCHEDULED_TOTAL_WAGES"},{"key":"TK_PROJECTED_APPLY_DATE","alias":"TK_PROJECTED_APPLY_DATE"},{"key":"TK_PROJECTED_APPLY_DATE_EXCLUDE_CORRECTIONS","alias":"TK_PROJECTED_APPLY_DATE_EXCLUDE_CORRECTIONS"},{"key":"TK_PROJECTED_APPLY_DATE_ONLY_CORRECTIONS","alias":"TK_PROJECTED_APPLY_DATE_ONLY_CORRECTIONS"},{"key":"TK_PROJECTED_JOB_TRANSFER","alias":"TK_PROJECTED_JOB_TRANSFER"},{"key":"TK_PROJECTED_JOB_TRANSFER_EXCLUDE_CORRECTIONS","alias":"TK_PROJECTED_JOB_TRANSFER_EXCLUDE_CORRECTIONS"},{"key":"TK_PROJECTED_LABOR_TRANSFER_EXCLUDE_CORRECTIONS","alias":"TK_PROJECTED_LABOR_TRANSFER_EXCLUDE_CORRECTIONS"},{"key":"TK_PROJECTED_LABOR_TRANSFER_ONLY_CORRECTIONS","alias":"TK_PROJECTED_LABOR_TRANSFER_ONLY_CORRECTIONS"},{"key":"TK_PROJECTED_TOTAL_DAYS","alias":"TK_PROJECTED_TOTAL_DAYS"},{"key":"TK_PROJECTED_TOTAL_DAYS_EXCLUDE_CORRECTIONS","alias":"TK_PROJECTED_TOTAL_DAYS_EXCLUDE_CORRECTIONS"},{"key":"TK_PROJECTED_TOTAL_DAYS_ONLY_CORRECTIONS","alias":"TK_PROJECTED_TOTAL_DAYS_ONLY_CORRECTIONS"},{"key":"TK_PROJECTED_TOTAL_HOURS","alias":"TK_PROJECTED_TOTAL_HOURS"},{"key":"TK_PROJECTED_TOTAL_HOURS_EXCLUDE_CORRECTIONS","alias":"TK_PROJECTED_TOTAL_HOURS_EXCLUDE_CORRECTIONS"},{"key":"TK_PROJECTED_TOTAL_HOURS_ONLY_CORRECTIONS","alias":"TK_PROJECTED_TOTAL_HOURS_ONLY_CORRECTIONS"},{"key":"TK_PROJECTED_TOTAL_WAGES","alias":"TK_PROJECTED_TOTAL_WAGES"},{"key":"TK_PROJECTED_TOTAL_WAGES_EXCLUDE_CORRECTIONS","alias":"TK_PROJECTED_TOTAL_WAGES_EXCLUDE_CORRECTIONS"},{"key":"TK_PROJECTED_TOTAL_WAGES_ONLY_CORRECTIONS","alias":"TK_PROJECTED_TOTAL_WAGES_ONLY_CORRECTIONS"},{"key":"TK_SCHEDULED_APPLY_DATE","alias":"TK_SCHEDULED_APPLY_DATE"},{"key":"TK_SCHEDULED_JOB_TRANSFER","alias":"TK_SCHEDULED_JOB_TRANSFER"},{"key":"TK_SCHEDULED_LABOR_TRANSFER","alias":"TK_SCHEDULED_LABOR_TRANSFER"},{"key":"TK_SCHEDULED_TOTAL_DAYS","alias":"TK_SCHEDULED_TOTAL_DAYS"},{"key":"TK_SCHEDULED_TOTAL_HOURS","alias":"TK_SCHEDULED_TOTAL_HOURS"},{"key":"TK_SCHEDULED_TOTAL_WAGES","alias":"TK_SCHEDULED_TOTAL_WAGES"},{"key":"TK_ACTUAL_WAGE_ADD","alias":"TK_ACTUAL_WAGE_ADD"},{"key":"TK_ACTUAL_WAGE_MULTIPLIER","alias":"TK_ACTUAL_WAGE_MULTIPLIER"},{"key":"TK_ACTUAL_WAGE_ADD_EXCLUDE_CORRECTIONS","alias":"TK_ACTUAL_WAGE_ADD_EXCLUDE_CORRECTIONS"},{"key":"TK_ACTUAL_WAGE_MULTIPLIER_EXCLUDE_CORRECTIONS","alias":"TK_ACTUAL_WAGE_MULTIPLIER_EXCLUDE_CORRECTIONS"},{"key":"TK_ACTUAL_WAGE_ADD_ONLY_CORRECTIONS","alias":"TK_ACTUAL_WAGE_ADD_ONLY_CORRECTIONS"},{"key":"TK_ACTUAL_WAGE_MULTIPLIER_ONLY_CORRECTIONS","alias":"TK_ACTUAL_WAGE_MULTIPLIER_ONLY_CORRECTIONS"},{"key":"CORE_PAYCODE_VISIBLE_IN_REPORTS_TOTALS_INDICATOR","alias":"CORE_PAYCODE_VISIBLE_IN_REPORTS_TOTALS_INDICATOR"},{"key":"CORE_PAYCODE_VISIBLE_ON_TOTALS_ADDON_INDICATOR","alias":"CORE_PAYCODE_VISIBLE_ON_TOTALS_ADDON_INDICATOR"},{"key":"TK_ACTUAL_APPLY_DATE_ONLY_CORRECTIONS","alias":"TK_ACTUAL_APPLY_DATE_ONLY_CORRECTIONS"},{"key":"TK_ACTUAL_JOB_TRANSFER","alias":"TK_ACTUAL_JOB_TRANSFER"},{"key":"TK_ACTUAL_TOTAL_DAYS","alias":"TK_ACTUAL_TOTAL_DAYS"},{"key":"TK_GENIE_ACTUAL_TOTAL_DAYS","alias":"TK_GENIE_ACTUAL_TOTAL_DAYS"},{"key":"TK_GENIE_PROJECTED_TOTAL_HOURS","alias":"TK_GENIE_PROJECTED_TOTAL_HOURS"},{"key":"TK_GENIE_PROJECTED_TOTAL_HOURS_EXCLUDE_CORRECTIONS","alias":"TK_GENIE_PROJECTED_TOTAL_HOURS_EXCLUDE_CORRECTIONS"},{"key":"TK_GENIE_PROJECTED_TOTAL_HOURS_ONLY_CORRECTIONS","alias":"TK_GENIE_PROJECTED_TOTAL_HOURS_ONLY_CORRECTIONS"},{"key":"TK_GENIE_SCHEDULED_TOTAL_DAYS","alias":"TK_GENIE_SCHEDULED_TOTAL_DAYS"},{"key":"TK_PROJECTED_JOB_TRANSFER_ONLY_CORRECTIONS","alias":"TK_PROJECTED_JOB_TRANSFER_ONLY_CORRECTIONS"},{"key":"TK_PROJECTED_LABOR_TRANSFER","alias":"TK_PROJECTED_LABOR_TRANSFER"}],"from":{"view":"EMP","employeeSet":{"hyperfind":{"qualifier":"All Home"},"dateRange":{"startDate":"' . $startDate . 'T00:00","EndDate":"' . $endDate . 'T00:00"}}},"where":[{"key":"PEOPLE_ACCRUAL_PROFILE_NAME","alias":"PEOPLE_ACCRUAL_PROFILE_NAME","operator":"STARTS_WITH","values":["IBEW"]}]}';
        $bodyRequest = '{"select":[{"key":"CORE_PAYCODE_IS_COMBINED","alias":"CORE_PAYCODE_IS_COMBINED"},{"key":"CORE_PAYCODE_IS_EXCUSED_ABSENCE","alias":"CORE_PAYCODE_IS_EXCUSED_ABSENCE"},{"key":"CORE_PAYCODE_IS_MONEY","alias":"CORE_PAYCODE_IS_MONEY"},{"key":"CORE_PAYCODE_IS_TOTALS","alias":"CORE_PAYCODE_IS_TOTALS"},{"key":"CORE_PAYCODE_SCHEDULED_HOURS_TYPE","alias":"CORE_PAYCODE_SCHEDULED_HOURS_TYPE"},{"key":"CORE_PAYCODE_SHORT_NAME","alias":"CORE_PAYCODE_SHORT_NAME"},{"key":"CORE_PAYCODE_UNIT","alias":"CORE_PAYCODE_UNIT"},{"key":"CORE_PAYCODE_WAGE_ADDITION","alias":"CORE_PAYCODE_WAGE_ADDITION"},{"key":"CORE_PAYCODE_WAGE_MULTIPLIER","alias":"CORE_PAYCODE_WAGE_MULTIPLIER"},{"key":"CORE_PAYCODE","alias":"CORE_PAYCODE"},{"key":"CORE_PAYCODE_TYPE","alias":"CORE_PAYCODE_TYPE"},{"key":"TK_ACTUAL_APPLY_DATE","alias":"TK_ACTUAL_APPLY_DATE"},{"key":"TK_ACTUAL_APPLY_DATE_EXCLUDE_CORRECTIONS","alias":"TK_ACTUAL_APPLY_DATE_EXCLUDE_CORRECTIONS"},{"key":"TK_ACTUAL_JOB_TRANSFER_EXCLUDE_CORRECTIONS","alias":"TK_ACTUAL_JOB_TRANSFER_EXCLUDE_CORRECTIONS"},{"key":"TK_ACTUAL_JOB_TRANSFER_ONLY_CORRECTIONS","alias":"TK_ACTUAL_JOB_TRANSFER_ONLY_CORRECTIONS"},{"key":"TK_ACTUAL_LABOR_TRANSFER","alias":"TK_ACTUAL_LABOR_TRANSFER"},{"key":"TK_ACTUAL_LABOR_TRANSFER_EXCLUDE_CORRECTIONS","alias":"TK_ACTUAL_LABOR_TRANSFER_EXCLUDE_CORRECTIONS"},{"key":"TK_ACTUAL_LABOR_TRANSFER_ONLY_CORRECTIONS","alias":"TK_ACTUAL_LABOR_TRANSFER_ONLY_CORRECTIONS"},{"key":"TK_ACTUAL_PAYPERIOD_NUMBER","alias":"TK_ACTUAL_PAYPERIOD_NUMBER"},{"key":"TK_ACTUAL_PAYPERIOD_NUMBER_EXCLUDE_CORRECTIONS","alias":"TK_ACTUAL_PAYPERIOD_NUMBER_EXCLUDE_CORRECTIONS"},{"key":"TK_ACTUAL_PAYPERIOD_NUMBER_ONLY_CORRECTIONS","alias":"TK_ACTUAL_PAYPERIOD_NUMBER_ONLY_CORRECTIONS"},{"key":"TK_ACTUAL_PAYPERIOD_WEEK","alias":"TK_ACTUAL_PAYPERIOD_WEEK"},{"key":"TK_ACTUAL_PAYPERIOD_WEEK_EXCLUDE_CORRECTIONS","alias":"TK_ACTUAL_PAYPERIOD_WEEK_EXCLUDE_CORRECTIONS"},{"key":"TK_ACTUAL_PAYPERIOD_WEEK_ONLY_CORRECTIONS","alias":"TK_ACTUAL_PAYPERIOD_WEEK_ONLY_CORRECTIONS"},{"key":"TK_ACTUAL_SIGNED_OFF","alias":"TK_ACTUAL_SIGNED_OFF"},{"key":"TK_ACTUAL_SIGNED_OFF_EXCLUDE_CORRECTIONS","alias":"TK_ACTUAL_SIGNED_OFF_EXCLUDE_CORRECTIONS"},{"key":"TK_ACTUAL_SIGNED_OFF_ONLY_CORRECTIONS","alias":"TK_ACTUAL_SIGNED_OFF_ONLY_CORRECTIONS"},{"key":"TK_ACTUAL_TOTAL_HOURS_EXCLUDE_CORRECTIONS","alias":"TK_ACTUAL_TOTAL_HOURS_EXCLUDE_CORRECTIONS"},{"key":"TK_ACTUAL_TOTAL_HOURS_ONLY_CORRECTIONS","alias":"TK_ACTUAL_TOTAL_HOURS_ONLY_CORRECTIONS"},{"key":"TK_ACTUAL_TOTAL_WAGES","alias":"TK_ACTUAL_TOTAL_WAGES"},{"key":"TK_ACTUAL_TOTAL_WAGES_EXCLUDE_CORRECTIONS","alias":"TK_ACTUAL_TOTAL_WAGES_EXCLUDE_CORRECTIONS"},{"key":"TK_ACTUAL_TOTAL_WAGES_ONLY_CORRECTIONS","alias":"TK_ACTUAL_TOTAL_WAGES_ONLY_CORRECTIONS"},{"key":"TK_GENIE_ACTUAL_TOTAL_DAYS_EXCLUDE_CORRECTIONS","alias":"TK_GENIE_ACTUAL_TOTAL_DAYS_EXCLUDE_CORRECTIONS"},{"key":"TK_GENIE_ACTUAL_TOTAL_DAYS_ONLY_CORRECTIONS","alias":"TK_GENIE_ACTUAL_TOTAL_DAYS_ONLY_CORRECTIONS"},{"key":"TK_GENIE_ACTUAL_TOTAL_HOURS","alias":"TK_GENIE_ACTUAL_TOTAL_HOURS"},{"key":"TK_GENIE_ACTUAL_TOTAL_HOURS_EXCLUDE_CORRECTIONS","alias":"TK_GENIE_ACTUAL_TOTAL_HOURS_EXCLUDE_CORRECTIONS"},{"key":"TK_GENIE_ACTUAL_TOTAL_HOURS_ONLY_CORRECTIONS","alias":"TK_GENIE_ACTUAL_TOTAL_HOURS_ONLY_CORRECTIONS"},{"key":"TK_GENIE_ACTUAL_TOTAL_WAGES","alias":"TK_GENIE_ACTUAL_TOTAL_WAGES"},{"key":"TK_GENIE_ACTUAL_TOTAL_WAGES_EXCLUDE_CORRECTIONS","alias":"TK_GENIE_ACTUAL_TOTAL_WAGES_EXCLUDE_CORRECTIONS"},{"key":"TK_GENIE_ACTUAL_TOTAL_WAGES_ONLY_CORRECTIONS","alias":"TK_GENIE_ACTUAL_TOTAL_WAGES_ONLY_CORRECTIONS"},{"key":"TK_GENIE_PROJECTED_TOTAL_DAYS","alias":"TK_GENIE_PROJECTED_TOTAL_DAYS"},{"key":"TK_GENIE_PROJECTED_TOTAL_DAYS_EXCLUDE_CORRECTIONS","alias":"TK_GENIE_PROJECTED_TOTAL_DAYS_EXCLUDE_CORRECTIONS"},{"key":"TK_GENIE_PROJECTED_TOTAL_DAYS_ONLY_CORRECTIONS","alias":"TK_GENIE_PROJECTED_TOTAL_DAYS_ONLY_CORRECTIONS"},{"key":"TK_GENIE_PROJECTED_TOTAL_WAGES","alias":"TK_GENIE_PROJECTED_TOTAL_WAGES"},{"key":"TK_GENIE_PROJECTED_TOTAL_WAGES_EXCLUDE_CORRECTIONS","alias":"TK_GENIE_PROJECTED_TOTAL_WAGES_EXCLUDE_CORRECTIONS"},{"key":"TK_GENIE_PROJECTED_TOTAL_WAGES_ONLY_CORRECTIONS","alias":"TK_GENIE_PROJECTED_TOTAL_WAGES_ONLY_CORRECTIONS"},{"key":"TK_GENIE_SCHEDULED_TOTAL_HOURS","alias":"TK_GENIE_SCHEDULED_TOTAL_HOURS"},{"key":"TK_GENIE_SCHEDULED_TOTAL_WAGES","alias":"TK_GENIE_SCHEDULED_TOTAL_WAGES"},{"key":"TK_PROJECTED_APPLY_DATE","alias":"TK_PROJECTED_APPLY_DATE"},{"key":"TK_PROJECTED_APPLY_DATE_EXCLUDE_CORRECTIONS","alias":"TK_PROJECTED_APPLY_DATE_EXCLUDE_CORRECTIONS"},{"key":"TK_PROJECTED_APPLY_DATE_ONLY_CORRECTIONS","alias":"TK_PROJECTED_APPLY_DATE_ONLY_CORRECTIONS"},{"key":"TK_PROJECTED_JOB_TRANSFER","alias":"TK_PROJECTED_JOB_TRANSFER"},{"key":"TK_PROJECTED_JOB_TRANSFER_EXCLUDE_CORRECTIONS","alias":"TK_PROJECTED_JOB_TRANSFER_EXCLUDE_CORRECTIONS"},{"key":"TK_PROJECTED_LABOR_TRANSFER_EXCLUDE_CORRECTIONS","alias":"TK_PROJECTED_LABOR_TRANSFER_EXCLUDE_CORRECTIONS"},{"key":"TK_PROJECTED_LABOR_TRANSFER_ONLY_CORRECTIONS","alias":"TK_PROJECTED_LABOR_TRANSFER_ONLY_CORRECTIONS"},{"key":"TK_PROJECTED_TOTAL_DAYS","alias":"TK_PROJECTED_TOTAL_DAYS"},{"key":"TK_PROJECTED_TOTAL_DAYS_EXCLUDE_CORRECTIONS","alias":"TK_PROJECTED_TOTAL_DAYS_EXCLUDE_CORRECTIONS"},{"key":"TK_PROJECTED_TOTAL_DAYS_ONLY_CORRECTIONS","alias":"TK_PROJECTED_TOTAL_DAYS_ONLY_CORRECTIONS"},{"key":"TK_PROJECTED_TOTAL_HOURS","alias":"TK_PROJECTED_TOTAL_HOURS"},{"key":"TK_PROJECTED_TOTAL_HOURS_EXCLUDE_CORRECTIONS","alias":"TK_PROJECTED_TOTAL_HOURS_EXCLUDE_CORRECTIONS"},{"key":"TK_PROJECTED_TOTAL_HOURS_ONLY_CORRECTIONS","alias":"TK_PROJECTED_TOTAL_HOURS_ONLY_CORRECTIONS"},{"key":"TK_PROJECTED_TOTAL_WAGES","alias":"TK_PROJECTED_TOTAL_WAGES"},{"key":"TK_PROJECTED_TOTAL_WAGES_EXCLUDE_CORRECTIONS","alias":"TK_PROJECTED_TOTAL_WAGES_EXCLUDE_CORRECTIONS"},{"key":"TK_PROJECTED_TOTAL_WAGES_ONLY_CORRECTIONS","alias":"TK_PROJECTED_TOTAL_WAGES_ONLY_CORRECTIONS"},{"key":"TK_SCHEDULED_APPLY_DATE","alias":"TK_SCHEDULED_APPLY_DATE"},{"key":"TK_SCHEDULED_JOB_TRANSFER","alias":"TK_SCHEDULED_JOB_TRANSFER"},{"key":"TK_SCHEDULED_LABOR_TRANSFER","alias":"TK_SCHEDULED_LABOR_TRANSFER"},{"key":"TK_SCHEDULED_TOTAL_DAYS","alias":"TK_SCHEDULED_TOTAL_DAYS"},{"key":"TK_SCHEDULED_TOTAL_HOURS","alias":"TK_SCHEDULED_TOTAL_HOURS"},{"key":"TK_SCHEDULED_TOTAL_WAGES","alias":"TK_SCHEDULED_TOTAL_WAGES"},{"key":"TK_ACTUAL_WAGE_ADD","alias":"TK_ACTUAL_WAGE_ADD"},{"key":"TK_ACTUAL_WAGE_MULTIPLIER","alias":"TK_ACTUAL_WAGE_MULTIPLIER"},{"key":"TK_ACTUAL_WAGE_ADD_EXCLUDE_CORRECTIONS","alias":"TK_ACTUAL_WAGE_ADD_EXCLUDE_CORRECTIONS"},{"key":"TK_ACTUAL_WAGE_MULTIPLIER_EXCLUDE_CORRECTIONS","alias":"TK_ACTUAL_WAGE_MULTIPLIER_EXCLUDE_CORRECTIONS"},{"key":"TK_ACTUAL_WAGE_ADD_ONLY_CORRECTIONS","alias":"TK_ACTUAL_WAGE_ADD_ONLY_CORRECTIONS"},{"key":"TK_ACTUAL_WAGE_MULTIPLIER_ONLY_CORRECTIONS","alias":"TK_ACTUAL_WAGE_MULTIPLIER_ONLY_CORRECTIONS"},{"key":"CORE_PAYCODE_VISIBLE_IN_REPORTS_TOTALS_INDICATOR","alias":"CORE_PAYCODE_VISIBLE_IN_REPORTS_TOTALS_INDICATOR"},{"key":"CORE_PAYCODE_VISIBLE_ON_TOTALS_ADDON_INDICATOR","alias":"CORE_PAYCODE_VISIBLE_ON_TOTALS_ADDON_INDICATOR"},{"key":"TK_ACTUAL_APPLY_DATE_ONLY_CORRECTIONS","alias":"TK_ACTUAL_APPLY_DATE_ONLY_CORRECTIONS"},{"key":"TK_ACTUAL_JOB_TRANSFER","alias":"TK_ACTUAL_JOB_TRANSFER"},{"key":"TK_ACTUAL_TOTAL_DAYS","alias":"TK_ACTUAL_TOTAL_DAYS"},{"key":"TK_GENIE_ACTUAL_TOTAL_DAYS","alias":"TK_GENIE_ACTUAL_TOTAL_DAYS"},{"key":"TK_GENIE_PROJECTED_TOTAL_HOURS","alias":"TK_GENIE_PROJECTED_TOTAL_HOURS"},{"key":"TK_GENIE_PROJECTED_TOTAL_HOURS_EXCLUDE_CORRECTIONS","alias":"TK_GENIE_PROJECTED_TOTAL_HOURS_EXCLUDE_CORRECTIONS"},{"key":"TK_GENIE_PROJECTED_TOTAL_HOURS_ONLY_CORRECTIONS","alias":"TK_GENIE_PROJECTED_TOTAL_HOURS_ONLY_CORRECTIONS"},{"key":"TK_GENIE_SCHEDULED_TOTAL_DAYS","alias":"TK_GENIE_SCHEDULED_TOTAL_DAYS"},{"key":"TK_PROJECTED_JOB_TRANSFER_ONLY_CORRECTIONS","alias":"TK_PROJECTED_JOB_TRANSFER_ONLY_CORRECTIONS"},{"key":"TK_PROJECTED_LABOR_TRANSFER","alias":"TK_PROJECTED_LABOR_TRANSFER"}],"from":{"view":"EMP","employeeSet":{"hyperfind":{"qualifier":"All Home"},"dateRange":{"startDate":"' . $startDate . 'T00:00","EndDate":"' . $endDate . 'T00:00"}}},"where":[{"key":"PEOPLE_ACCRUAL_PROFILE_NAME","alias":"PEOPLE_ACCRUAL_PROFILE_NAME","operator":"STARTS_WITH","values":["IBEW"]}]}';


        $client = $this->Client();
        $url = 'commons/data/multi_read';
        //dd($client);

        $response = $client->post($url, [
            'body' => $bodyRequest
        ]);

        $bodyResponse = json_decode($response->getBody(), true);
        dd($bodyResponse);

        $bodyResponse = array_slice($bodyResponse, 0, 30);

        $childrens = data_get($bodyResponse, 'data.children');
        //$childrens = array_slice($childrens, 0, 30);

    }

    public function getPayCodeEditsByID()
    {
        $jsonString = '';
        $payCodeEditID = 105955;

        $client = $this->Client();

        $url = 'scheduling/schedule/pay_code_edits/' . $payCodeEditID;

        $response = $client->get($url);

        $body = json_decode($response->getBody(), true);

        dd($body);

        return $body;

    }

    public function retreiveSchedule()
    {
        $dateTime = Carbon::now();
        $startOfYear = $dateTime->startOfYear()->toDateString();
        $endOfYear = $dateTime->endOfYear()->toDateString();
        $employeeNumber = 85580;

        //$jsonString = '{"select":[{"key":"EMP_COMMON_FULL_NAME","alias":"EMP_COMMON_FULL_NAME"},{"key":"PEOPLE_PERSON_ID","alias":"PEOPLE_PERSON_ID"},{"key":"EMP_COMMON_PRIMARY_ORG","alias":"EMP_COMMON_PRIMARY_ORG"},{"key":"EMP_COMMON_PRIMARY_JOB","alias":"EMP_COMMON_PRIMARY_JOB"},{"key":"PEOPLE_SENIORITY_DATE","alias":"PEOPLE_SENIORITY_DATE"},{"key":"SCH_SCHEDULE_EVENT_INDEX","alias":"SCH_SCHEDULE_EVENT_INDEX"},{"key":"SCH_SCHEDULE_EVENT_ID","alias":"SCH_SCHEDULE_EVENT_ID"},{"key":"SCH_SCHEDULE_EVENT_PARENT_ID","alias":"SCH_SCHEDULE_EVENT_PARENT_ID"},{"key":"SCH_SCHEDULE_EVENT_TYPE","alias":"SCH_SCHEDULE_EVENT_TYPE"},{"key":"SCH_SCHEDULE_EVENT_CALENDAR_DATE","alias":"SCH_SCHEDULE_EVENT_CALENDAR_DATE"},{"key":"SCH_SCHEDULE_EVENT_START_DATE","alias":"SCH_SCHEDULE_EVENT_START_DATE"},{"key":"SCH_SCHEDULE_EVENT_END_DATE","alias":"SCH_SCHEDULE_EVENT_END_DATE"},{"key":"SCH_SCHEDULE_EVENT_SHIFT_LABEL","alias":"SCH_SCHEDULE_EVENT_SHIFT_LABEL"},{"key":"SCH_SCHEDULE_EVENT_IS_POSTED","alias":"SCH_SCHEDULE_EVENT_IS_POSTED"},{"key":"SCH_SCHEDULE_EVENT_SEGMENT_TYPE","alias":"SCH_SCHEDULE_EVENT_SEGMENT_TYPE"},{"key":"SCH_SCHEDULE_EVENT_ORG_JOB","alias":"SCH_SCHEDULE_EVENT_ORG_JOB"},{"key":"SCH_SCHEDULE_EVENT_IS_TRANSFER_ORG_JOB","alias":"SCH_SCHEDULE_EVENT_IS_TRANSFER_ORG_JOB"},{"key":"SCH_SCHEDULE_EVENT_IS_USER_ENTERED_ORG_JOB","alias":"SCH_SCHEDULE_EVENT_IS_USER_ENTERED_ORG_JOB"},{"key":"SCH_SCHEDULE_EVENT_PRIMARY_ORG_JOB","alias":"SCH_SCHEDULE_EVENT_PRIMARY_ORG_JOB"},{"key":"SCH_SCHEDULE_EVENT_LABOR_ACCOUNT","alias":"SCH_SCHEDULE_EVENT_LABOR_ACCOUNT"},{"key":"SCH_SCHEDULE_EVENT_IS_USER_ENTERED_LABOR_ACCOUNT","alias":"SCH_SCHEDULE_EVENT_IS_USER_ENTERED_LABOR_ACCOUNT"},{"key":"SCH_SCHEDULE_EVENT_USER_ENTERED_LABOR_ACCOUNT","alias":"SCH_SCHEDULE_EVENT_USER_ENTERED_LABOR_ACCOUNT"},{"key":"SCH_SCHEDULE_EVENT_IS_TRANSFER_LABOR_ACCOUNT","alias":"SCH_SCHEDULE_EVENT_IS_TRANSFER_LABOR_ACCOUNT"},{"key":"SCH_SCHEDULE_EVENT_PRIMARY_LABOR_ACCOUNT","alias":"SCH_SCHEDULE_EVENT_PRIMARY_LABOR_ACCOUNT"},{"key":"SCH_SCHEDULE_EVENT_SEGMENT_WORK_RULE","alias":"SCH_SCHEDULE_EVENT_SEGMENT_WORK_RULE"},{"key":"SCH_SCHEDULE_EVENT_SEGMENT_IS_USER_ENTERED_WORK_RULE","alias":"SCH_SCHEDULE_EVENT_SEGMENT_IS_USER_ENTERED_WORK_RULE"},{"key":"SCH_SCHEDULE_EVENT_SEGMENT_IS_TRANSFER_WORK_RULE","alias":"SCH_SCHEDULE_EVENT_SEGMENT_IS_TRANSFER_WORK_RULE"},{"key":"SCH_SCHEDULE_EVENT_SEGMENT_PRIMARY_WORK_RULE","alias":"SCH_SCHEDULE_EVENT_SEGMENT_PRIMARY_WORK_RULE"},{"key":"SCH_SCHEDULE_EVENT_PCE_START_DATE","alias":"SCH_SCHEDULE_EVENT_PCE_START_DATE"},{"key":"SCH_SCHEDULE_EVENT_PCE_END_DATE","alias":"SCH_SCHEDULE_EVENT_PCE_END_DATE"},{"key":"SCH_SCHEDULE_EVENT_PCE_START_TIME","alias":"SCH_SCHEDULE_EVENT_PCE_START_TIME"},{"key":"SCH_SCHEDULE_EVENT_PCE_END_TIME","alias":"SCH_SCHEDULE_EVENT_PCE_END_TIME"},{"key":"SCH_SCHEDULE_EVENT_PCE_MONEY_AMOUNT","alias":"SCH_SCHEDULE_EVENT_PCE_MONEY_AMOUNT"},{"key":"SCH_SCHEDULE_EVENT_PCE_DURATION_IN_TIME","alias":"SCH_SCHEDULE_EVENT_PCE_DURATION_IN_TIME"},{"key":"SCH_SCHEDULE_EVENT_PCE_DURATION_IN_DAYS","alias":"SCH_SCHEDULE_EVENT_PCE_DURATION_IN_DAYS"},{"key":"SCH_SCHEDULE_EVENT_PCE_PAYCODE_REF","alias":"SCH_SCHEDULE_EVENT_PCE_PAYCODE_REF"},{"key":"SCH_SCHEDULE_EVENT_PCE_SYMBOLIC_VALUE_REF","alias":"SCH_SCHEDULE_EVENT_PCE_SYMBOLIC_VALUE_REF"},{"key":"CORE_PAYCODE_IS_MONEY","alias":"CORE_PAYCODE_IS_MONEY"},{"key":"CORE_PAYCODE_TYPE","alias":"CORE_PAYCODE_TYPE"},{"key":"CORE_PAYCODE_SHORT_NAME","alias":"CORE_PAYCODE_SHORT_NAME"},{"key":"CORE_PAYCODE_UNIT","alias":"CORE_PAYCODE_UNIT"},{"key":"CORE_PAYCODE","alias":"CORE_PAYCODE"},{"key":"SCH_SCHEDULE_EVENT_SEGMENT_LABOR_CATEGORIES","alias":"SCH_SCHEDULE_EVENT_SEGMENT_LABOR_CATEGORIES"},{"key":"SCH_SCHEDULE_EVENT_SEGMENT_IS_TRANSFER_LABOR_CATEGORIES","alias":"SCH_SCHEDULE_EVENT_SEGMENT_IS_TRANSFER_LABOR_CATEGORIES"},{"key":"SCH_SCHEDULE_EVENT_SEGMENT_COST_CENTER","alias":"SCH_SCHEDULE_EVENT_SEGMENT_COST_CENTER"},{"key":"SCH_SCHEDULE_EVENT_SEGMENT_IS_TRANSFER_COST_CENTER","alias":"SCH_SCHEDULE_EVENT_SEGMENT_IS_TRANSFER_COST_CENTER"}],"from":{"view":0,"employeeSet":{"hyperfind":{"id":1,"qualifier":"All Home"},"dateRange":{"startDate":"' . $startOfYear . 'T00:00","EndDate":"' . $endOfYear . 'T00:00"}}}}';
        //$jsonString = '{"select":[{"key":"PEOPLE_PERSON_NUMBER","alias":"PEOPLE_PERSON_NUMBER"},{"key":"PEOPLE_PERSON_ID","alias":"PEOPLE_PERSON_ID"},{"key":"EMP_COMMON_FULL_NAME","alias":"EMP_COMMON_FULL_NAME"},{"key":"EMP_COMMON_PRIMARY_ORG","alias":"EMP_COMMON_PRIMARY_ORG"},{"key":"EMP_COMMON_PRIMARY_JOB","alias":"EMP_COMMON_PRIMARY_JOB"},{"key":"PEOPLE_SENIORITY_DATE","alias":"PEOPLE_SENIORITY_DATE"},{"key":"SCH_SCHEDULE_EVENT_INDEX","alias":"SCH_SCHEDULE_EVENT_INDEX"},{"key":"SCH_SCHEDULE_EVENT_ID","alias":"SCH_SCHEDULE_EVENT_ID"},{"key":"SCH_SCHEDULE_EVENT_PARENT_ID","alias":"SCH_SCHEDULE_EVENT_PARENT_ID"},{"key":"SCH_SCHEDULE_EVENT_TYPE","alias":"SCH_SCHEDULE_EVENT_TYPE"},{"key":"SCH_SCHEDULE_EVENT_CALENDAR_DATE","alias":"SCH_SCHEDULE_EVENT_CALENDAR_DATE"},{"key":"SCH_SCHEDULE_EVENT_START_DATE","alias":"SCH_SCHEDULE_EVENT_START_DATE"},{"key":"SCH_SCHEDULE_EVENT_END_DATE","alias":"SCH_SCHEDULE_EVENT_END_DATE"},{"key":"SCH_SCHEDULE_EVENT_SHIFT_LABEL","alias":"SCH_SCHEDULE_EVENT_SHIFT_LABEL"},{"key":"SCH_SCHEDULE_EVENT_IS_POSTED","alias":"SCH_SCHEDULE_EVENT_IS_POSTED"},{"key":"SCH_SCHEDULE_EVENT_SEGMENT_TYPE","alias":"SCH_SCHEDULE_EVENT_SEGMENT_TYPE"},{"key":"SCH_SCHEDULE_EVENT_ORG_JOB","alias":"SCH_SCHEDULE_EVENT_ORG_JOB"},{"key":"SCH_SCHEDULE_EVENT_IS_TRANSFER_ORG_JOB","alias":"SCH_SCHEDULE_EVENT_IS_TRANSFER_ORG_JOB"},{"key":"SCH_SCHEDULE_EVENT_IS_USER_ENTERED_ORG_JOB","alias":"SCH_SCHEDULE_EVENT_IS_USER_ENTERED_ORG_JOB"},{"key":"SCH_SCHEDULE_EVENT_PRIMARY_ORG_JOB","alias":"SCH_SCHEDULE_EVENT_PRIMARY_ORG_JOB"},{"key":"SCH_SCHEDULE_EVENT_LABOR_ACCOUNT","alias":"SCH_SCHEDULE_EVENT_LABOR_ACCOUNT"},{"key":"SCH_SCHEDULE_EVENT_IS_USER_ENTERED_LABOR_ACCOUNT","alias":"SCH_SCHEDULE_EVENT_IS_USER_ENTERED_LABOR_ACCOUNT"},{"key":"SCH_SCHEDULE_EVENT_USER_ENTERED_LABOR_ACCOUNT","alias":"SCH_SCHEDULE_EVENT_USER_ENTERED_LABOR_ACCOUNT"},{"key":"SCH_SCHEDULE_EVENT_IS_TRANSFER_LABOR_ACCOUNT","alias":"SCH_SCHEDULE_EVENT_IS_TRANSFER_LABOR_ACCOUNT"},{"key":"SCH_SCHEDULE_EVENT_PRIMARY_LABOR_ACCOUNT","alias":"SCH_SCHEDULE_EVENT_PRIMARY_LABOR_ACCOUNT"},{"key":"SCH_SCHEDULE_EVENT_SEGMENT_WORK_RULE","alias":"SCH_SCHEDULE_EVENT_SEGMENT_WORK_RULE"},{"key":"SCH_SCHEDULE_EVENT_SEGMENT_IS_USER_ENTERED_WORK_RULE","alias":"SCH_SCHEDULE_EVENT_SEGMENT_IS_USER_ENTERED_WORK_RULE"},{"key":"SCH_SCHEDULE_EVENT_SEGMENT_IS_TRANSFER_WORK_RULE","alias":"SCH_SCHEDULE_EVENT_SEGMENT_IS_TRANSFER_WORK_RULE"},{"key":"SCH_SCHEDULE_EVENT_SEGMENT_PRIMARY_WORK_RULE","alias":"SCH_SCHEDULE_EVENT_SEGMENT_PRIMARY_WORK_RULE"},{"key":"SCH_SCHEDULE_EVENT_PCE_START_DATE","alias":"SCH_SCHEDULE_EVENT_PCE_START_DATE"},{"key":"SCH_SCHEDULE_EVENT_PCE_END_DATE","alias":"SCH_SCHEDULE_EVENT_PCE_END_DATE"},{"key":"SCH_SCHEDULE_EVENT_PCE_START_TIME","alias":"SCH_SCHEDULE_EVENT_PCE_START_TIME"},{"key":"SCH_SCHEDULE_EVENT_PCE_END_TIME","alias":"SCH_SCHEDULE_EVENT_PCE_END_TIME"},{"key":"SCH_SCHEDULE_EVENT_PCE_MONEY_AMOUNT","alias":"SCH_SCHEDULE_EVENT_PCE_MONEY_AMOUNT"},{"key":"SCH_SCHEDULE_EVENT_PCE_DURATION_IN_TIME","alias":"SCH_SCHEDULE_EVENT_PCE_DURATION_IN_TIME"},{"key":"SCH_SCHEDULE_EVENT_PCE_DURATION_IN_DAYS","alias":"SCH_SCHEDULE_EVENT_PCE_DURATION_IN_DAYS"},{"key":"SCH_SCHEDULE_EVENT_PCE_PAYCODE_REF","alias":"SCH_SCHEDULE_EVENT_PCE_PAYCODE_REF"},{"key":"SCH_SCHEDULE_EVENT_PCE_SYMBOLIC_VALUE_REF","alias":"SCH_SCHEDULE_EVENT_PCE_SYMBOLIC_VALUE_REF"},{"key":"CORE_PAYCODE_IS_MONEY","alias":"CORE_PAYCODE_IS_MONEY"},{"key":"CORE_PAYCODE_TYPE","alias":"CORE_PAYCODE_TYPE"},{"key":"CORE_PAYCODE_SHORT_NAME","alias":"CORE_PAYCODE_SHORT_NAME"},{"key":"CORE_PAYCODE_UNIT","alias":"CORE_PAYCODE_UNIT"},{"key":"CORE_PAYCODE","alias":"CORE_PAYCODE"},{"key":"SCH_SCHEDULE_EVENT_SEGMENT_LABOR_CATEGORIES","alias":"SCH_SCHEDULE_EVENT_SEGMENT_LABOR_CATEGORIES"},{"key":"SCH_SCHEDULE_EVENT_SEGMENT_IS_TRANSFER_LABOR_CATEGORIES","alias":"SCH_SCHEDULE_EVENT_SEGMENT_IS_TRANSFER_LABOR_CATEGORIES"},{"key":"SCH_SCHEDULE_EVENT_SEGMENT_COST_CENTER","alias":"SCH_SCHEDULE_EVENT_SEGMENT_COST_CENTER"},{"key":"SCH_SCHEDULE_EVENT_SEGMENT_IS_TRANSFER_COST_CENTER","alias":"SCH_SCHEDULE_EVENT_SEGMENT_IS_TRANSFER_COST_CENTER"}],"where":{"employees":{"dateRange":{"endDate":"' . $endOfYear . '","startDate":"' . $startOfYear . '"},"employees":{"ids":[1038]}},"excludeBreaks":false,"hyperFind":{"dateRange":{"endDate":"' . $endOfYear . '","startDate":"' . $startOfYear . '"},"hyperFind":{"qualifier":"All Home"},"includeEmployeeTransfer":false},"locations":{"dateRange":{"endDate":"' . $endOfYear . '","startDate":"' . $startOfYear . '"},"includeEmployeeTransfer":false,"locations":{"ids":[1],"qualifiers":[""]}}}}';

        //$jsonString = '{"where":{"includeUnposted":false,"postingPeriods":[{"endDate":"2019-12-18","startDate":"2019-12-01"}]}}';

        $jsonString = '{"select":[""],"where":{"employees":{"dateRange":{"endDate":"2019-01-01","startDate":"2019-12-31"},"employees":{"ids":[1],"qualifiers":["85580"]}},"hyperFind":{"dateRange":{"EndDate":"2019-01-01","startDate":"2019-12-31"},"hyperFind":{"id":1,"qualifier":"All Home"}}}}';
        $client = $this->Client();

        //$url = 'commons/data/multi_read';
        $url = 'scheduling/schedule/multi_read';
        //$url = 'scheduling/schedule_management_actions/multi_read';

        $response = $client->post($url, [
            'body' => $jsonString
        ]);

        $body = json_decode($response->getBody(), true);

        dd($body);

        $childrens = data_get($body, 'data.children');
        //$childrens = array_slice($childrens, 0, 30);

        dd($childrens);

        return $body;
    }

    public function schedule()
    {
        $dateTime = Carbon::now();
        $startOfYear = $dateTime->startOfYear()->toDateString();
        $endOfYear = $dateTime->endOfYear()->toDateString();
        $employeeNumber = 85580;
        $jsonString = '{"select":[{"key":"PEOPLE_PERSON_NUMBER","alias":"PEOPLE_PERSON_NUMBER"},{"key":"PEOPLE_PERSON_ID","alias":"PEOPLE_PERSON_ID"},{"key":"EMP_COMMON_FULL_NAME","alias":"EMP_COMMON_FULL_NAME"},{"key":"EMP_COMMON_PRIMARY_ORG","alias":"EMP_COMMON_PRIMARY_ORG"},{"key":"EMP_COMMON_PRIMARY_JOB","alias":"EMP_COMMON_PRIMARY_JOB"},{"key":"PEOPLE_SENIORITY_DATE","alias":"PEOPLE_SENIORITY_DATE"},{"key":"SCH_SCHEDULE_EVENT_INDEX","alias":"SCH_SCHEDULE_EVENT_INDEX"},{"key":"SCH_SCHEDULE_EVENT_ID","alias":"SCH_SCHEDULE_EVENT_ID"},{"key":"SCH_SCHEDULE_EVENT_PARENT_ID","alias":"SCH_SCHEDULE_EVENT_PARENT_ID"},{"key":"SCH_SCHEDULE_EVENT_TYPE","alias":"SCH_SCHEDULE_EVENT_TYPE"},{"key":"SCH_SCHEDULE_EVENT_CALENDAR_DATE","alias":"SCH_SCHEDULE_EVENT_CALENDAR_DATE"},{"key":"SCH_SCHEDULE_EVENT_START_DATE","alias":"SCH_SCHEDULE_EVENT_START_DATE"},{"key":"SCH_SCHEDULE_EVENT_END_DATE","alias":"SCH_SCHEDULE_EVENT_END_DATE"},{"key":"SCH_SCHEDULE_EVENT_SHIFT_LABEL","alias":"SCH_SCHEDULE_EVENT_SHIFT_LABEL"},{"key":"SCH_SCHEDULE_EVENT_IS_POSTED","alias":"SCH_SCHEDULE_EVENT_IS_POSTED"},{"key":"SCH_SCHEDULE_EVENT_SEGMENT_TYPE","alias":"SCH_SCHEDULE_EVENT_SEGMENT_TYPE"},{"key":"SCH_SCHEDULE_EVENT_ORG_JOB","alias":"SCH_SCHEDULE_EVENT_ORG_JOB"},{"key":"SCH_SCHEDULE_EVENT_IS_TRANSFER_ORG_JOB","alias":"SCH_SCHEDULE_EVENT_IS_TRANSFER_ORG_JOB"},{"key":"SCH_SCHEDULE_EVENT_IS_USER_ENTERED_ORG_JOB","alias":"SCH_SCHEDULE_EVENT_IS_USER_ENTERED_ORG_JOB"},{"key":"SCH_SCHEDULE_EVENT_PRIMARY_ORG_JOB","alias":"SCH_SCHEDULE_EVENT_PRIMARY_ORG_JOB"},{"key":"SCH_SCHEDULE_EVENT_LABOR_ACCOUNT","alias":"SCH_SCHEDULE_EVENT_LABOR_ACCOUNT"},{"key":"SCH_SCHEDULE_EVENT_IS_USER_ENTERED_LABOR_ACCOUNT","alias":"SCH_SCHEDULE_EVENT_IS_USER_ENTERED_LABOR_ACCOUNT"},{"key":"SCH_SCHEDULE_EVENT_USER_ENTERED_LABOR_ACCOUNT","alias":"SCH_SCHEDULE_EVENT_USER_ENTERED_LABOR_ACCOUNT"},{"key":"SCH_SCHEDULE_EVENT_IS_TRANSFER_LABOR_ACCOUNT","alias":"SCH_SCHEDULE_EVENT_IS_TRANSFER_LABOR_ACCOUNT"},{"key":"SCH_SCHEDULE_EVENT_PRIMARY_LABOR_ACCOUNT","alias":"SCH_SCHEDULE_EVENT_PRIMARY_LABOR_ACCOUNT"},{"key":"SCH_SCHEDULE_EVENT_SEGMENT_WORK_RULE","alias":"SCH_SCHEDULE_EVENT_SEGMENT_WORK_RULE"},{"key":"SCH_SCHEDULE_EVENT_SEGMENT_IS_USER_ENTERED_WORK_RULE","alias":"SCH_SCHEDULE_EVENT_SEGMENT_IS_USER_ENTERED_WORK_RULE"},{"key":"SCH_SCHEDULE_EVENT_SEGMENT_IS_TRANSFER_WORK_RULE","alias":"SCH_SCHEDULE_EVENT_SEGMENT_IS_TRANSFER_WORK_RULE"},{"key":"SCH_SCHEDULE_EVENT_SEGMENT_PRIMARY_WORK_RULE","alias":"SCH_SCHEDULE_EVENT_SEGMENT_PRIMARY_WORK_RULE"},{"key":"SCH_SCHEDULE_EVENT_PCE_START_DATE","alias":"SCH_SCHEDULE_EVENT_PCE_START_DATE"},{"key":"SCH_SCHEDULE_EVENT_PCE_END_DATE","alias":"SCH_SCHEDULE_EVENT_PCE_END_DATE"},{"key":"SCH_SCHEDULE_EVENT_PCE_START_TIME","alias":"SCH_SCHEDULE_EVENT_PCE_START_TIME"},{"key":"SCH_SCHEDULE_EVENT_PCE_END_TIME","alias":"SCH_SCHEDULE_EVENT_PCE_END_TIME"},{"key":"SCH_SCHEDULE_EVENT_PCE_MONEY_AMOUNT","alias":"SCH_SCHEDULE_EVENT_PCE_MONEY_AMOUNT"},{"key":"SCH_SCHEDULE_EVENT_PCE_DURATION_IN_TIME","alias":"SCH_SCHEDULE_EVENT_PCE_DURATION_IN_TIME"},{"key":"SCH_SCHEDULE_EVENT_PCE_DURATION_IN_DAYS","alias":"SCH_SCHEDULE_EVENT_PCE_DURATION_IN_DAYS"},{"key":"SCH_SCHEDULE_EVENT_PCE_PAYCODE_REF","alias":"SCH_SCHEDULE_EVENT_PCE_PAYCODE_REF"},{"key":"SCH_SCHEDULE_EVENT_PCE_SYMBOLIC_VALUE_REF","alias":"SCH_SCHEDULE_EVENT_PCE_SYMBOLIC_VALUE_REF"},{"key":"CORE_PAYCODE_IS_MONEY","alias":"CORE_PAYCODE_IS_MONEY"},{"key":"CORE_PAYCODE_TYPE","alias":"CORE_PAYCODE_TYPE"},{"key":"CORE_PAYCODE_SHORT_NAME","alias":"CORE_PAYCODE_SHORT_NAME"},{"key":"CORE_PAYCODE_UNIT","alias":"CORE_PAYCODE_UNIT"},{"key":"CORE_PAYCODE","alias":"CORE_PAYCODE"},{"key":"SCH_SCHEDULE_EVENT_SEGMENT_LABOR_CATEGORIES","alias":"SCH_SCHEDULE_EVENT_SEGMENT_LABOR_CATEGORIES"},{"key":"SCH_SCHEDULE_EVENT_SEGMENT_IS_TRANSFER_LABOR_CATEGORIES","alias":"SCH_SCHEDULE_EVENT_SEGMENT_IS_TRANSFER_LABOR_CATEGORIES"},{"key":"SCH_SCHEDULE_EVENT_SEGMENT_COST_CENTER","alias":"SCH_SCHEDULE_EVENT_SEGMENT_COST_CENTER"},{"key":"SCH_SCHEDULE_EVENT_SEGMENT_IS_TRANSFER_COST_CENTER","alias":"SCH_SCHEDULE_EVENT_SEGMENT_IS_TRANSFER_COST_CENTER"}],"from":{"view":"EMP","employeeSet":{"hyperfind":{"qualifier":"All Home"},"dateRange":{"startDate":"' . $startOfYear . 'T00:00","EndDate":"' . $endOfYear . 'T00:00"}}},"sortBy":[{"key":"EMP_COMMON_FULL_NAME","alias":"EMP_COMMON_FULL_NAME","sortDirection":"ASC"},{"key":"SCH_SCHEDULE_EVENT_CALENDAR_DATE","alias":"SCH_SCHEDULE_EVENT_CALENDAR_DATE","sortDirection":"ASC"},{"key":"SCH_SCHEDULE_EVENT_INDEX","alias":"SCH_SCHEDULE_EVENT_INDEX","sortDirection":"ASC"}],"where":[{"key":"PEOPLE_PERSON_NUMBER","alias":"PEOPLE_PERSON_NUMBER","operator":"STARTS_WITH","values":["' . $employeeNumber . '"]}]}';

        $client = $this->Client();

        $url = 'commons/data/multi_read';

        $response = $client->post($url, [
            'body' => $jsonString
        ]);

        $body = json_decode($response->getBody(), true);

        //dd($body);

        $childrens = data_get($body, 'data.children');
        //$childrens = array_slice($childrens, 0, 30);

        dd($childrens);

        return $body;
    }

    /**
     * @return array|mixed
     */
    public function getEmployeeTimeOffRequest()
    {
        $dateTime = Carbon::now();
        $startOfYear = $dateTime->startOfYear()->toDateString();
        $endOfYear = $dateTime->endOfYear()->toDateString();

        $requestStatuses = [
            'DRAFT' => 1,
            'SUBMITTED' => 2,
            'APPROVED' => 4
        ];
        //$jsonString = '{"where":{"employee":{"employeeRef":{"id":1038},"endDate":"'. $endOfYear .'","startDate":"'. $startOfYear .'"}}}';

        //$jsonString = '{"where":{"employees":{"employeeRefs":{"ids":[1038],"qualifiers":[""]},"endDate":"'. $endOfYear .'","startDate":"'. $startOfYear .'"},"states":{"completionState":"DRAFT","employeeRefs":{"ids":[1038],"qualifiers":[""]},"endDate":"'. $endOfYear .'","startDate":"'. $startOfYear .'"}}}';
        //$jsonString = '{"where":{"states":{"completionState":"DRAFT","employeeRefs":{"ids":[1038],"qualifiers":[""]},"endDate":"'. $endOfYear .'","startDate":"'. $startOfYear .'"}}}';

        //$jsonString = '{"where":{"employees":{"employeeRefs":{"ids":[1038],"qualifiers":[""]},"endDate":"'. $endOfYear .'","startDate":"'. $startOfYear .'"},"currentStatus":"APPROVED"}}';


        //$jsonString = '{"where":{"employees":{"employeeRefs":{"ids":[1038],"qualifiers":[""]},"endDate":"' . $endOfYear . '","startDate":"' . $startOfYear . '"}}}';
        $jsonString = '{"where":{"employees":{"employeeRefs":{"ids":[1038],"qualifiers":[""]},"endDate":"' . $endOfYear . '","startDate":"2019-01-01"}}}';
        //$jsonString = '{"where":{"employees":{"employeeRefs":{"ids":[1024,1025,1026,1027,2051,1028,1030,1031,1032,1033,1034,1803,1035,1036,1037,1038,1039,1551,1040,1041,1042,1043,1044,1045,1046,1047,1048,1049,1054,1055,1056,1057,1058,1059,1060,1061,1062,1063,1064,2351,1851,1353,2151,883,884,1151,902,904,2201,2202,951,952,953,954,955,956,957,958,959,960,961,962,963,964,966,967,968,969,971,972,973,974,975,976,979,980,981,982,983,984,985,986,987,988,1501,989,990,992,993,994,1251,995,996,997,998,999,1000,1001,1002,1003,1004,1005,1006,1007,1009,1010,1011,1012,1013,1014,1015,1017,1018,1019,1020,1021,2301,1022,1023],"qualifiers":[""]},"endDate":"'. $endOfYear .'","startDate":"'. $startOfYear .'"}}}';

        $client = $this->Client();

        $url = 'scheduling/timeoff/multi_read';

        $response = $client->post($url, [
            'body' => $jsonString
        ]);

        $body = json_decode($response->getBody(), true);

        $body = Arr::dot($body);
        //dd($body);

        return $body;
    }

    public function getEmployeeTimeOffRequestSubTypes()
    {
        $dateTime = Carbon::now();
        $startOfYear = $dateTime->startOfYear()->toDateString();
        $endOfYear = $dateTime->endOfYear()->toDateString();


        $jsonString = '{"where":{"employee":{"employeeRef":{"id":1038},"endDate":"' . $endOfYear . '","startDate":"' . $startOfYear . '"}}}';


        $client = $this->Client();

        $url = 'scheduling/timeoff/request_subtypes';

        $response = $client->get($url, [
            'query' => [
                'employee_id' => 1038,
                'person_number' => 85580
            ]
        ]);

        $body = json_decode($response->getBody(), true);


        //dd($body);

        return $body;
    }

    public function getPaycodeEditsLimited()
    {
        //Creating a carbon instance
        $dateTime = new Carbon();
        $startOfYear = $dateTime->copy()->startOfYear()->toDateString();
        $endOfYear = $dateTime->copy()->endOfYear()->toDateString();

        $quartersInYear = 4; //Setting the number of quarters to loop
        $indexOffset = 1; //Setting the offset to be subtracted from quartersInYear within the for loop

        //looping through each quarter subtracting by 1 because the index is starting at 0. gathering employee paycode data.
        for ($index = 0; $index <= $quartersInYear - $indexOffset; $index++) {
            //setting the startdate by copying the carbon instance and adding the number of quarters based on the loop index
            $startDate = $dateTime->copy()->startOfYear()->addQuarters($index)->toDateString(); //2020-01-01 2020-04-01 2020-07-01 2020-10-01 2021-01-01
            //setting end date by copying the carbon instance and adding a quarter to be a quarter ahead of start date and then continueing to add quarters based on the loop index and subtracting 1 day to be a day behind the next start date so no duplication
            $endDate = $dateTime->copy()->startOfYear()->addQuarter()->addQuarters($index)->addDays(-1)->toDateString(); //2020-03-31 2020-06-30 2020-09-30 2020-12-31

            //setting kronos query string in json for the body request(specific SCH_PCE columns)
            $bodyRequest = '{"select":[{"key":"PEOPLE_PERSON_ID","alias":"PEOPLE_PERSON_ID"},{"key":"PEOPLE_PERSON_NUMBER","alias":"PEOPLE_PERSON_NUMBER"},{"key":"PEOPLE_FIRST_NAME","alias":"PEOPLE_FIRST_NAME"},{"key":"PEOPLE_LAST_NAME","alias":"PEOPLE_LAST_NAME"},{"key":"EMP_COMMON_FULL_NAME","alias":"EMP_COMMON_FULL_NAME"},{"key":"PEOPLE_PHONE_NUMBER","alias":"PEOPLE_PHONE_NUMBER","properties":[{"key":"1","value":"Phone 1"}]},{"key":"PEOPLE_EMAIL","alias":"PEOPLE_EMAIL"},{"key":"SCH_PCE_PRIMARY_ORG_JOB","alias":"SCH_PCE_PRIMARY_ORG_JOB"},{"key":"SCH_PCE_PAYCODE_REF","alias":"SCH_PCE_PAYCODE_REF"},{"key":"SCH_PCE_START_DATE","alias":"SCH_PCE_START_DATE"},{"key":"SCH_PCE_START_TIME","alias":"SCH_PCE_START_TIME"},{"key":"SCH_PCE_END_DATE","alias":"SCH_PCE_END_DATE"},{"key":"SCH_PCE_END_TIME","alias":"SCH_PCE_END_TIME"},{"key":"SCH_PCE_DURATION_IN_TIME","alias":"SCH_PCE_DURATION_IN_TIME"},{"key":"SCH_PCE_SYMBOLIC_VALUE_REF","alias":"SCH_PCE_SYMBOLIC_VALUE_REF"},{"key":"PEOPLE_ACCRUAL_PROFILE_NAME","alias":"PEOPLE_ACCRUAL_PROFILE_NAME"}],"from":{"view":"EMP","employeeSet":{"hyperfind":{"qualifier":"All Home"},"dateRange":{"startDate":"' . $startDate . 'T00:00","EndDate":"' . $endDate . 'T00:00"}}},"where":[{"key":"PEOPLE_ACCRUAL_PROFILE_NAME","alias":"PEOPLE_ACCRUAL_PROFILE_NAME","operator":"STARTS_WITH","values":["IBEW"]}]}';

            //initiating client
            $client = $this->Client();

            //setting the resource url
            $url = 'commons/data/multi_read';

            //sending the request and receiving the response data
            $response = $client->post($url, [
                'body' => $bodyRequest
            ]);
            //decoding the json response body to an array
            $bodyResponse = json_decode($response->getBody(), true);

            //getting the array of arrays for the data to be extracted
            $childrens = data_get($bodyResponse, 'data.children');

            //going through each children data array to get their attributes
            foreach ($childrens as $children) {
                //getting each childrens data attribute array
                $attributes = data_get($children, 'attributes');

                //if the array count is greater than 10 then it has paycode edit values that we need from the kronos query
                if (count($attributes) > 10) {
                    //going through each array in attributes to get the value of each attribute
                    foreach ($attributes as $attribute) {
                        //grabbing the alias key value in the attribute array
                        $alias = data_get($attribute, 'alias');
                        //using a switch statement to pull out the value of the column from the kronos query
                        switch ($alias) {
                            case "PEOPLE_PERSON_ID":
                                $employeeID = data_get($attribute, 'value', '');
                                break;
                            case "PEOPLE_PERSON_NUMBER":
                                $employeeNumber = data_get($attribute, 'value', '');
                                break;
                            case "PEOPLE_FIRST_NAME":
                                $firstName = data_get($attribute, 'value', '');
                                break;
                            case "PEOPLE_LAST_NAME":
                                $lastName = data_get($attribute, 'value', '');
                                break;
                            case "EMP_COMMON_FULL_NAME":
                                $employeeName = data_get($attribute, 'value', '');
                                break;
                            case "PEOPLE_PHONE_NUMBER":
                                $phone = data_get($attribute, 'value', '');
                                break;
                            case "PEOPLE_EMAIL":
                                $email = data_get($attribute, 'value', '');
                                break;
                            case "SCH_PCE_PRIMARY_ORG_JOB":
                                $pcePrimaryOrgJob = data_get($attribute, 'value', '');
                                break;
                            case "SCH_PCE_PAYCODE_REF":
                                $pcePaycodeRef = data_get($attribute, 'value', '');
                                break;
                            case "SCH_PCE_START_DATE":
                                $pceStartDate = data_get($attribute, 'value', '');
                                break;
                            case "SCH_PCE_START_TIME":
                                $pceStartTime = data_get($attribute, 'value', '');
                                break;
                            case "SCH_PCE_END_DATE":
                                $pceEndDate = data_get($attribute, 'value', '');
                                break;
                            case "SCH_PCE_END_TIME":
                                $pceEndTime = data_get($attribute, 'value', '');
                                break;
                            case "SCH_PCE_DURATION_IN_TIME":
                                $pceDurationInTime = data_get($attribute, 'value', '');
                                break;
                            case "SCH_PCE_SYMBOLIC_VALUE_REF":
                                $pceSymbolicValueRef = data_get($attribute, 'value', '');
                                break;
                        }
                    }
                    //putting the values into a new key value array for manipulation later
                    $employees[] = [
                        'employeeID' => $employeeID,
                        'employeeNumber' => $employeeNumber,
                        'firstName' => $firstName,
                        'lastName' => $lastName,
                        'fullName' => $employeeName,
                        'phone' => $phone,
                        'email' => $email,
                        'pcePrimaryOrgJob' => $pcePrimaryOrgJob,
                        'pcePaycodeRef' => $pcePaycodeRef,
                        'pceStartDate' => $pceStartDate,
                        'pceStartTime' => $pceStartTime,
                        'pceEndDate' => $pceEndDate,
                        'pceEndTime' => $pceEndTime,
                        'pceDurationInTime' => $pceDurationInTime,
                        'pceSymbolicValueRef' => $pceSymbolicValueRef,
                        'startDate' => $startDate,
                        'endDate' => $endDate
                    ];
                }
            }
        }
        //dumping the data into a csv file for verification
        $filename = 'Scheduled_Paycode_Edits_' . $startOfYear . '-' . $endOfYear . '_' . $dateTime->toDateString() . '.csv';
        $header = ['PEOPLE_PERSON_ID', 'PEOPLE_PERSON_NUMBER', 'PEOPLE_FIRST_NAME', 'PEOPLE_LAST_NAME', 'EMP_COMMON_FULL_NAME', 'PEOPLE_PHONE_NUMBER', 'PEOPLE_EMAIL', 'SCH_PCE_PRIMARY_ORG_JOB', 'SCH_PCE_PAYCODE_REF', 'SCH_PCE_START_DATE', 'SCH_PCE_START_TIME', 'SCH_PCE_END_DATE', 'SCH_PCE_END_TIME', 'SCH_PCE_DURATION_IN_TIME', 'SCH_PCE_SYMBOLIC_VALUE_REF', 'START_RANGE', 'END_RANGE'];

        $fp = fopen($filename, 'w');
        fputcsv($fp, $header);
        foreach ($employees as $employee) {
            fputcsv($fp, $employee);
        }
        fclose($fp);

        //returning an array of employees time-off schedule
        return $employees;
    }

    public function getAllPaycodeEditsLimited()
    {
        //Creating a carbon instance
        $dateTime = new Carbon();
        $startOfYear = $dateTime->copy()->startOfYear()->toDateString();
        $endOfYear = $dateTime->copy()->endOfYear()->toDateString();

        $quartersInYear = 4; //Setting the number of quarters to loop
        $indexOffset = 1; //Setting the offset to be subtracted from quartersInYear within the for loop

        //looping through each quarter subtracting by 1 because the index is starting at 0. gathering employee paycode data.
        for ($index = 0; $index <= $quartersInYear - $indexOffset; $index++) {
            //setting the startdate by copying the carbon instance and adding the number of quarters based on the loop index
            $startDate = $dateTime->copy()->startOfYear()->addQuarters($index)->toDateString(); //2020-01-01 2020-04-01 2020-07-01 2020-10-01 2021-01-01
            //setting end date by copying the carbon instance and adding a quarter to be a quarter ahead of start date and then continueing to add quarters based on the loop index and subtracting 1 day to be a day behind the next start date so no duplication
            $endDate = $dateTime->copy()->startOfYear()->addQuarter()->addQuarters($index)->addDays(-1)->toDateString(); //2020-03-31 2020-06-30 2020-09-30 2020-12-31

            //setting kronos query string in json for the body request(all SCH_PCE columns)
            $bodyRequest = '{"select":[{"key":"PEOPLE_PERSON_ID","alias":"PEOPLE_PERSON_ID"},{"key":"PEOPLE_PERSON_NUMBER","alias":"PEOPLE_PERSON_NUMBER"},{"key":"PEOPLE_FIRST_NAME","alias":"PEOPLE_FIRST_NAME"},{"key":"PEOPLE_LAST_NAME","alias":"PEOPLE_LAST_NAME"},{"key":"PEOPLE_EMAIL","alias":"PEOPLE_EMAIL"},{"key":"EMP_COMMON_FULL_NAME","alias":"EMP_COMMON_FULL_NAME"},{"key":"PEOPLE_PHONE_NUMBER","alias":"PEOPLE_PHONE_NUMBER","properties":[{"key":"1","value":"Phone 1"}]},{"key":"SCH_PCE_LABOR_CATEGORIES","alias":"SCH_PCE_LABOR_CATEGORIES"},{"key":"SCH_PCE_USER_ENTERED_LABOR_CATEGORIES","alias":"SCH_PCE_USER_ENTERED_LABOR_CATEGORIES"},{"key":"SCH_PCE_IS_TRANSFER_LABOR_CATEGORIES","alias":"SCH_PCE_IS_TRANSFER_LABOR_CATEGORIES"},{"key":"SCH_PCE_PRIMARY_LABOR_CATEGORIES","alias":"SCH_PCE_PRIMARY_LABOR_CATEGORIES"},{"key":"SCH_PCE_COST_CENTER","alias":"SCH_PCE_COST_CENTER"},{"key":"SCH_PCE_PRIMARY_COST_CENTER","alias":"SCH_PCE_PRIMARY_COST_CENTER"},{"key":"SCH_PCE_IS_USER_ENTERED_COST_CENTER","alias":"SCH_PCE_IS_USER_ENTERED_COST_CENTER"},{"key":"SCH_PCE_IS_TRANSFER_COST_CENTER","alias":"SCH_PCE_IS_TRANSFER_COST_CENTER"},{"key":"SCH_PCE_TRANSFER_STRING","alias":"SCH_PCE_TRANSFER_STRING"},{"key":"SCH_PCE_USER_ENTERED_LABOR_ACCOUNT","alias":"SCH_PCE_USER_ENTERED_LABOR_ACCOUNT"},{"key":"SCH_PCE_DURATION_IN_TIME","alias":"SCH_PCE_DURATION_IN_TIME"},{"key":"SCH_PCE_DURATION_IN_DAYS","alias":"SCH_PCE_DURATION_IN_DAYS"},{"key":"SCH_PCE_END_DATE","alias":"SCH_PCE_END_DATE"},{"key":"SCH_PCE_END_TIME","alias":"SCH_PCE_END_TIME"},{"key":"SCH_PCE_IS_GENERATED","alias":"SCH_PCE_IS_GENERATED"},{"key":"SCH_PCE_IS_POSTED","alias":"SCH_PCE_IS_POSTED"},{"key":"SCH_PCE_IS_TRANSFER_LABOR_ACCOUNT","alias":"SCH_PCE_IS_TRANSFER_LABOR_ACCOUNT"},{"key":"SCH_PCE_IS_TRANSFER_ORG_JOB","alias":"SCH_PCE_IS_TRANSFER_ORG_JOB"},{"key":"SCH_PCE_IS_USER_ENTERED_LABOR_ACCOUNT","alias":"SCH_PCE_IS_USER_ENTERED_LABOR_ACCOUNT"},{"key":"SCH_PCE_IS_USER_ENTERED_ORG_JOB","alias":"SCH_PCE_IS_USER_ENTERED_ORG_JOB"},{"key":"SCH_PCE_LABOR_ACCOUNT","alias":"SCH_PCE_LABOR_ACCOUNT"},{"key":"SCH_PCE_MONEY_AMOUNT","alias":"SCH_PCE_MONEY_AMOUNT"},{"key":"SCH_PCE_ORG_JOB","alias":"SCH_PCE_ORG_JOB"},{"key":"SCH_PCE_PAYCODE_REF","alias":"SCH_PCE_PAYCODE_REF"},{"key":"SCH_PCE_PRIMARY_LABOR_ACCOUNT","alias":"SCH_PCE_PRIMARY_LABOR_ACCOUNT"},{"key":"SCH_PCE_PRIMARY_ORG_JOB","alias":"SCH_PCE_PRIMARY_ORG_JOB"},{"key":"SCH_PCE_START_DATE","alias":"SCH_PCE_START_DATE"},{"key":"SCH_PCE_START_TIME","alias":"SCH_PCE_START_TIME"},{"key":"SCH_PCE_SYMBOLIC_VALUE_REF","alias":"SCH_PCE_SYMBOLIC_VALUE_REF"},{"key":"SCH_SCHEDULE_EVENT_ID","alias":"SCH_SCHEDULE_EVENT_ID"},{"key":"PEOPLE_ACCRUAL_PROFILE_NAME","alias":"PEOPLE_ACCRUAL_PROFILE_NAME"}],"from":{"view":"EMP","employeeSet":{"hyperfind":{"qualifier":"All Home"},"dateRange":{"startDate":"' . $startDate . 'T00:00","EndDate":"' . $endDate . 'T00:00"}}},"where":[{"key":"PEOPLE_ACCRUAL_PROFILE_NAME","alias":"PEOPLE_ACCRUAL_PROFILE_NAME","operator":"STARTS_WITH","values":["IBEW"]}]}';

            //initiating client
            $client = $this->Client();
            //setting the resource url
            $url = 'commons/data/multi_read';

            //sending the request and receiving the response data
            $response = $client->post($url, [
                'body' => $bodyRequest
            ]);
            //decoding the json response body to an array
            $bodyResponse = json_decode($response->getBody(), true);
            $bodyResponse = array_slice($bodyResponse, 0, 30);
            dd($bodyResponse);
            //getting the array of arrays for the data to be extracted
            $childrens = data_get($bodyResponse, 'data.children');
            $childrens = array_slice($childrens, 0, 30);
            dd($childrens);
            //going through each children data array to get their attributes
            foreach ($childrens as $children) {
                //getting each childrens data attribute array
                $attributes = data_get($children, 'attributes');

                //$attributes = array_slice($attributes, 0, 30);
                //dd($attributes);
                //if the array count is greater than 10 then it has paycode edit values that we need from the kronos query
                if (count($attributes) > 10) {
                    //going through each array in attributes to get the value of each attribute
                    foreach ($attributes as $attribute) {
                        //grabbing the alias key value in the attribute array
                        $alias = data_get($attribute, 'alias');
                        //using a switch statement to pull out the value of the column from the kronos query
                        switch ($alias) {
                            case "PEOPLE_PERSON_ID":
                                $employeeID = data_get($attribute, 'value', '');
                                break;
                            case "PEOPLE_PERSON_NUMBER":
                                $employeeNumber = data_get($attribute, 'value', '');
                                break;
                            case "PEOPLE_FIRST_NAME":
                                $firstName = data_get($attribute, 'value', '');
                                break;
                            case "PEOPLE_LAST_NAME":
                                $lastName = data_get($attribute, 'value', '');
                                break;
                            case "EMP_COMMON_FULL_NAME":
                                $employeeName = data_get($attribute, 'value', '');
                                break;
                            case "PEOPLE_PHONE_NUMBER":
                                $phone = data_get($attribute, 'value', '');
                                break;
                            case "PEOPLE_EMAIL":
                                $email = data_get($attribute, 'value', '');
                                break;
                            case "SCH_PCE_PRIMARY_ORG_JOB":
                                $pcePrimaryOrgJob = data_get($attribute, 'value', '');
                                break;
                            case "SCH_PCE_ORG_JOB":
                                $pceOrgJob = data_get($attribute, 'value', '');
                                break;
                            case "SCH_PCE_PAYCODE_REF":
                                $pcePaycodeRef = data_get($attribute, 'value', '');
                                break;
                            case "SCH_PCE_START_DATE":
                                $pceStartDate = data_get($attribute, 'value', '');
                                break;
                            case "SCH_PCE_START_TIME":
                                $pceStartTime = data_get($attribute, 'value', '');
                                break;
                            case "SCH_PCE_END_DATE":
                                $pceEndDate = data_get($attribute, 'value', '');
                                break;
                            case "SCH_PCE_END_TIME":
                                $pceEndTime = data_get($attribute, 'value', '');
                                break;
                            case "SCH_PCE_DURATION_IN_DAYS":
                                $pceDurationInDays = data_get($attribute, 'value', '');
                                break;
                            case "SCH_PCE_DURATION_IN_TIME":
                                $pceDurationInTime = data_get($attribute, 'value', '');
                                break;
                            case "SCH_PCE_SYMBOLIC_VALUE_REF":
                                $pceSymbolicValueRef = data_get($attribute, 'value', '');
                                break;
                            case "SCH_PCE_PRIMARY_LABOR_CATEGORIES":
                                $pcePrimaryLaborCategory = data_get($attribute, 'value', '');
                                break;
                            case "SCH_PCE_LABOR_CATEGORIES":
                                $pceLaborCategory = data_get($attribute, 'value', '');
                                break;
                            case "SCH_PCE_PRIMARY_LABOR_ACCOUNT":
                                $pcePrimaryLaborAccount = data_get($attribute, 'value', '');
                                break;
                            case "SCH_PCE_LABOR_ACCOUNT":
                                $pceLaborAccount = data_get($attribute, 'value', '');
                                break;
                            case "SCH_PCE_PRIMARY_COST_CENTER":
                                $pcePrimaryCostCenter = data_get($attribute, 'value', '');
                                break;
                            case "SCH_PCE_COST_CENTER":
                                $pceCostCenter = data_get($attribute, 'value', '');
                                break;
                            case "SCH_PCE_TRANSFER_STRING":
                                $pceTransferString = data_get($attribute, 'value', '');
                                break;
                            case "SCH_PCE_USER_ENTERED_LABOR_CATEGORIES":
                                $pceUserEnteredLaborCategory = data_get($attribute, 'value', '');
                                break;
                            case "SCH_PCE_USER_ENTERED_LABOR_ACCOUNT":
                                $pceUserEnteredLaborAccount = data_get($attribute, 'value', '');
                                break;
                            case "SCH_PCE_IS_USER_ENTERED_COST_CENTER":
                                $pceIsUserEnteredCostCenter = data_get($attribute, 'value', '');
                                break;
                            case "SCH_PCE_IS_USER_ENTERED_ORG_JOB":
                                $pceIsUserEnteredOrgJob = data_get($attribute, 'value', '');
                                break;
                            case "SCH_PCE_IS_USER_ENTERED_LABOR_ACCOUNT":
                                $pceIsUserEnteredLaborAccount = data_get($attribute, 'value', '');
                                break;
                            case "SCH_PCE_IS_TRANSFER_LABOR_CATEGORIES":
                                $pceIsTransferLaborCategory = data_get($attribute, 'value', '');
                                break;
                            case "SCH_PCE_IS_TRANSFER_COST_CENTER":
                                $pceIsTransferCostCenter = data_get($attribute, 'value', '');
                                break;
                            case "SCH_PCE_IS_TRANSFER_LABOR_ACCOUNT":
                                $pceIsTransferLaborAccount = data_get($attribute, 'value', '');
                                break;
                            case "SCH_PCE_IS_TRANSFER_ORG_JOB":
                                $pceIsTransferOrgJob = data_get($attribute, 'value', '');
                                break;
                            case "SCH_PCE_IS_GENERATED":
                                $pceIsGenerated = data_get($attribute, 'value', '');
                                break;
                            case "SCH_PCE_IS_POSTED":
                                $pceIsPosted = data_get($attribute, 'value', '');
                                break;
                            case "SCH_PCE_MONEY_AMOUNT":
                                $pceMoneyAmount = data_get($attribute, 'value', '');
                                break;
                        }

                    }
                    //putting the values into a new key value array for manipulation later
                    $employees[] = [
                        'employeeID' => $employeeID,
                        'employeeNumber' => $employeeNumber,
                        'firstName' => $firstName,
                        'lastName' => $lastName,
                        'fullName' => $employeeName,
                        'phone' => $phone,
                        'email' => $email,
                        'pcePrimaryOrgJob' => $pcePrimaryOrgJob,
                        'pceOrgJob' => $pceOrgJob,
                        'pcePaycodeRef' => $pcePaycodeRef,
                        'pceStartDate' => $pceStartDate,
                        'pceStartTime' => $pceStartTime,
                        'pceEndDate' => $pceEndDate,
                        'pceEndTime' => $pceEndTime,
                        'pceDurationInDays' => $pceDurationInDays,
                        'pceDurationInTime' => $pceDurationInTime,
                        'pceSymbolicValueRef' => $pceSymbolicValueRef,
                        'pcePrimaryLaborCategory' => $pcePrimaryLaborCategory,
                        'pceLaborCategory' => $pceLaborCategory,
                        'pcePrimaryLaborAccount' => $pcePrimaryLaborAccount,
                        'pceLaborAccount' => $pceLaborAccount,
                        'pcePrimaryCostCenter' => $pcePrimaryCostCenter,
                        'pceCostCenter' => $pceCostCenter,
                        'pceTransferString' => $pceTransferString,
                        'pceUserEnteredLaborCategory' => $pceUserEnteredLaborCategory,
                        'pceUserEnteredLaborAccount' => $pceUserEnteredLaborAccount,
                        'pceIsUserEnteredCostCenter' => $pceIsUserEnteredCostCenter,
                        'pceIsUserEnteredOrgJob' => $pceIsUserEnteredOrgJob,
                        'pceIsUserEnteredLaborAccount' => $pceIsUserEnteredLaborAccount,
                        'pceIsTransferLaborCategory' => $pceIsTransferLaborCategory,
                        'pceIsTransferCostCenter' => $pceIsTransferCostCenter,
                        'pceIsTransferLaborAccount' => $pceIsTransferLaborAccount,
                        'pceIsTransferOrgJob' => $pceIsTransferOrgJob,
                        'pceIsGenerated' => $pceIsGenerated,
                        'pceIsPosted' => $pceIsPosted,
                        'pceMoneyAmount' => $pceMoneyAmount,
                        'startDate' => $startDate,
                        'endDate' => $endDate
                    ];
                    //dd($employees);
                }
            }
        }
        //dumping the data into a csv file for verification
        $filename = 'Z:\IT_Development\Processes\KronosDimensions-API Integrations\All_Scheduled_Paycode_Edits_' . $startOfYear . '-' . $endOfYear . '_' . $dateTime->toDateString() . '.csv';
        $header = ['PEOPLE_PERSON_ID', 'PEOPLE_PERSON_NUMBER', 'PEOPLE_PHONE_NUMBER', 'PEOPLE_FIRST_NAME', 'PEOPLE_LAST_NAME', 'PEOPLE_EMAIL', 'EMP_COMMON_FULL_NAME', 'SCH_PCE_LABOR_CATEGORIES', 'SCH_PCE_USER_ENTERED_LABOR_CATEGORIES', 'SCH_PCE_IS_TRANSFER_LABOR_CATEGORIES', 'SCH_PCE_PRIMARY_LABOR_CATEGORIES', 'SCH_PCE_COST_CENTER', 'SCH_PCE_PRIMARY_COST_CENTER', 'SCH_PCE_IS_USER_ENTERED_COST_CENTER', 'SCH_PCE_IS_TRANSFER_COST_CENTER', 'SCH_PCE_TRANSFER_STRING', 'SCH_PCE_USER_ENTERED_LABOR_ACCOUNT', 'SCH_PCE_DURATION_IN_TIME', 'SCH_PCE_DURATION_IN_DAYS', 'SCH_PCE_END_DATE', 'SCH_PCE_END_TIME', 'SCH_PCE_IS_GENERATED', 'SCH_PCE_IS_POSTED', 'SCH_PCE_IS_TRANSFER_LABOR_ACCOUNT', 'SCH_PCE_IS_TRANSFER_ORG_JOB', 'SCH_PCE_IS_USER_ENTERED_LABOR_ACCOUNT', 'SCH_PCE_IS_USER_ENTERED_ORG_JOB', 'SCH_PCE_LABOR_ACCOUNT', 'SCH_PCE_MONEY_AMOUNT', 'SCH_PCE_ORG_JOB', 'SCH_PCE_PAYCODE_REF', 'SCH_PCE_PRIMARY_LABOR_ACCOUNT', 'SCH_PCE_PRIMARY_ORG_JOB', 'SCH_PCE_START_DATE', 'SCH_PCE_START_TIME', 'SCH_PCE_SYMBOLIC_VALUE_REF', 'START_RANGE', 'END_RANGE'];

        $fp = fopen($filename, 'w');
        fputcsv($fp, $header);
        foreach ($employees as $employee) {
            fputcsv($fp, $employee);
        }
        fclose($fp);

        //dd($employees);
        //returning an array of employees time-off schedule
        return $employees;
    }

    //  getting data to assist in query building process

    /*** Dataviews ***/
    public function getDataviewProfiles()
    {

        $client = $this->Client();

        $url = 'commons/dataview_profiles';

        $response = $client->get($url);

        $body = json_decode($response->getBody(), true);

        foreach ($body as $bod) {
            $profileName = data_get($bod, 'name');
            switch ($profileName) {
                case "1 Employee":
                    $employeeDataViews = data_get($bod, 'dataViews');
                    break;
                case "Human Resources Administrator":
                    $humanResourcesAdministratorDataViews = data_get($bod, 'dataViews');
                    break;
                case "All Dataviews Profile":
                    $allDataViewProfiles = data_get($bod, 'dataViews');
                    break;
                case "System Administrator":
                    $systemAdministratorDataViews = data_get($bod, 'dataViews');
                    break;
                case "1 Manager":
                    $managerDataViews = data_get($bod, 'dataViews');
                    break;
            }
        }
        $filename = 'Z:\IT_Development\Processes\KronosDimensions-API Integrations\dataviews.csv';
        $header = ['id', 'name'];

        $fp = fopen($filename, 'w');
        fputcsv($fp, $header);
        foreach ($allDataViewProfiles as $allDataViewProfile) {
            fputcsv($fp, $allDataViewProfile);
        }

        fclose($fp);
        dd($body);
    }

    public function getDataviewProfilesByID($id)
    {

        $client = $this->Client();

        $url = 'commons/dataview_profiles/' . $id;

        $response = $client->get($url);
        dd($response->getBody()->getContents());
        $body = json_decode($response->getBody(), true);

        dd($body);
    }

    public function getDataviews()
    {

        $client = $this->Client();

        $url = 'commons/dataviews';

        $response = $client->get($url);

        $body = json_decode($response->getBody(), true);

        dd($body);
    }

    public function getDataviewByID($id)
    {

        $client = $this->Client();

        $url = 'commons/dataviews/' . $id;

        $response = $client->get($url);

        dd($response->getBody()->getContents());
        $body = json_decode($response->getBody(), true);

        dd($body);
    }

    /*** Reports ***/
    public function getReportCategories()
    {
        $client = $this->Client();

        $url = 'platform/report_categories';

        $response = $client->get($url);

        $body = json_decode($response->getBody(), true);
        //dd($body);

        $filename = 'Z:\IT_Development\Processes\KronosDimensions-API Integrations\ReportCategories.csv';
        $header = ['id', 'label', 'name', 'description'];

        $fp = fopen($filename, 'w');

        fputcsv($fp, $header);
        foreach ($body as $bod) {
            fputcsv($fp, $bod);
        }
        fclose($fp);

        return $body;
    }

    public function getReportDataObjects()
    {
        $client = $this->Client();

        $url = 'platform/report_dataobjects';

        $response = $client->get($url);

        $body = json_decode($response->getBody(), true);

        $content = data_get($body[9], 'dataView.content');
        dd(json_encode($content));

        dd($body[9]);

        $filename = 'Z:\IT_Development\Processes\KronosDimensions-API Integrations\ReportDataObjectNames.csv';
        $header = ['id', 'name', 'label', 'description', 'forceUpdate', 'hasDataView', 'definitionTypeId', 'definitionTypeQualifier'];

        $fp = fopen($filename, 'w');
        fputcsv($fp, $header);

        foreach ($body as $bod) {
            $id = data_get($bod, 'id');
            $name = data_get($bod, 'name');
            $label = data_get($bod, 'label');
            $description = data_get($bod, 'description');
            $forceUpdate = data_get($bod, 'forceUpdate');
            if (array_has($bod, 'dataView')) {
                $hasDataview = true;
                $dataView = data_get($bod, 'dataView');
                $dataViewId = data_get($dataView, 'id');
            } else {
                $hasDataview = false;
            }
            $definitionTypeId = data_get($bod, 'definitionType.id');
            $definitionTypeQualifier = data_get($bod, 'definitionType.qualifier');


            $reportObjectData = [
                $id,
                $name,
                $label,
                $description,
                $forceUpdate,
                $hasDataview,
                $definitionTypeId,
                $definitionTypeQualifier
            ];

            if ($dataViewId === 496) {
                dd($dataView);
            }
            $dataviews[] = $dataView;
            fputcsv($fp, $reportObjectData);
        }

        fclose($fp);
        dd($dataviews);

        return $body;
    }

    public function getReportProfiles()
    {
        $client = $this->Client();

        $url = 'platform/report_profiles';

        $response = $client->get($url);

        $body = json_decode($response->getBody(), true);

        dd($body);
    }

    public function getReportPublished()
    {
        $client = $this->Client();

        $url = 'platform/reports';

        $response = $client->get($url);

        $body = json_decode($response->getBody(), true);

        dd($body);

    }

    public function getReportDataElements()
    {
        $client = $this->Client();

        $url = 'platform/report_dataobjects/dataelements';

        $response = $client->post($url, [
            'query' => [
                'rollupBy' => 2
            ]
        ]);

        $body = json_decode($response->getBody(), true);

        dd($body);
    }

    public function getReportDesigns()
    {
        $client = $this->Client();

        $url = 'platform/report_designs';

        $response = $client->get($url);

        $body = json_decode($response->getBody(), true);

        dd($body);
    }

    public function getReportDesignByName($reportName)
    {
        $client = $this->Client();

        $url = 'platform/report_designs/' . $reportName;

        $response = $client->get($url);

        $body = json_decode($response->getBody(), true);

        dd($body);
    }

    public function getReportDesignByNameParameters($reportName)
    {
        $client = $this->Client();

        $url = 'platform/report_designs/' . $reportName . '/parameters';

        $response = $client->get($url);

        $body = json_decode($response->getBody(), true);

        dd($body);
    }

    /*** Helper Functions ***/
    function floorToFraction($number, $denominator = 4)
    {
        $x = $number * $denominator;
        $x = floor($x);
        $x = $x / $denominator;
        return $x;
    }

    /*** Testing ***/
    public function getEmployeesTimeOffTest()
    {
        //Creating a carbon instance
        $dateTime = new Carbon();
        $startOfYear = $dateTime->copy()->startOfYear()->toDateString();
        $endOfYear = $dateTime->copy()->endOfYear()->toDateString();
        $employeesTimeOff = array();
        $startDate = '2020-01-01';
        $endDate = '2020-01-31';

        //$bodyRequest = '{"select":[{"key":"EMP_COMMON_FULL_NAME","alias":"EMP_COMMON_FULL_NAME"},{"key":"SCH_PCE_START_DATE","alias":"SCH_PCE_START_DATE"},{"key":"SCH_PCE_START_TIME","alias":"SCH_PCE_START_TIME"},{"key":"SCH_PCE_END_DATE","alias":"SCH_PCE_END_DATE"},{"key":"SCH_PCE_END_TIME","alias":"SCH_PCE_END_TIME"},{"key":"SCH_SCHEDULE_EVENT_ID","alias":"SCH_SCHEDULE_EVENT_ID"},{"key":"SCH_SCHEDULE_EVENT_PARENT_ID","alias":"SCH_SCHEDULE_EVENT_PARENT_ID"},{"key":"PEOPLE_ACCRUAL_PROFILE_NAME","alias":"PEOPLE_ACCRUAL_PROFILE_NAME"}],"from":{"view":"EMP","employeeSet":{"hyperfind":{"qualifier":"All Home"},"dateRange":{"startDate":"' . $startDate . 'T00:00","EndDate":"' . $endDate . 'T00:00"}}},"where":[{"key":"PEOPLE_ACCRUAL_PROFILE_NAME","alias":"PEOPLE_ACCRUAL_PROFILE_NAME","operator":"STARTS_WITH","values":["IBEW"]}]}';
        $bodyRequest = '{"select":[{"key":"EMP_COMMON_FULL_NAME","alias":"EMP_COMMON_FULL_NAME"},{"key":"SCH_PCE_START_DATE","alias":"SCH_PCE_START_DATE"},{"key":"SCH_PCE_START_TIME","alias":"SCH_PCE_START_TIME"},{"key":"SCH_PCE_END_DATE","alias":"SCH_PCE_END_DATE"},{"key":"SCH_PCE_END_TIME","alias":"SCH_PCE_END_TIME"},{"key":"TK_HW_ITEMID","alias":"TK_HW_ITEMID"},{"key":"TIMECARD_TRANS_WORK_ITEM_ID","alias":"TIMECARD_TRANS_WORK_ITEM_ID"},{"key":"PEOPLE_ACCRUAL_PROFILE_NAME","alias":"PEOPLE_ACCRUAL_PROFILE_NAME"}],"from":{"view":"EMP","employeeSet":{"hyperfind":{"qualifier":"All Home"},"dateRange":{"startDate":"' . $startDate . 'T00:00","EndDate":"' . $endDate . 'T00:00"}}},"where":[{"key":"PEOPLE_ACCRUAL_PROFILE_NAME","alias":"PEOPLE_ACCRUAL_PROFILE_NAME","operator":"STARTS_WITH","values":["IBEW"]}]}';
        //setting kronos query string in json for the body request(specific SCH_PCE columns)
        //$bodyRequest = '{"select":[{"key":"PEOPLE_PERSON_ID","alias":"PEOPLE_PERSON_ID"},{"key":"PEOPLE_PERSON_NUMBER","alias":"PEOPLE_PERSON_NUMBER"},{"key":"PEOPLE_FIRST_NAME","alias":"PEOPLE_FIRST_NAME"},{"key":"PEOPLE_LAST_NAME","alias":"PEOPLE_LAST_NAME"},{"key":"EMP_COMMON_FULL_NAME","alias":"EMP_COMMON_FULL_NAME"},{"key":"PEOPLE_PHONE_NUMBER","alias":"PEOPLE_PHONE_NUMBER","properties":[{"key":"1","value":"Phone 1"}]},{"key":"PEOPLE_EMAIL","alias":"PEOPLE_EMAIL"},{"key":"SCH_PCE_PRIMARY_ORG_JOB","alias":"SCH_PCE_PRIMARY_ORG_JOB"},{"key":"SCH_PCE_PAYCODE_REF","alias":"SCH_PCE_PAYCODE_REF"},{"key":"SCH_PCE_START_DATE","alias":"SCH_PCE_START_DATE"},{"key":"SCH_PCE_START_TIME","alias":"SCH_PCE_START_TIME"},{"key":"SCH_PCE_END_DATE","alias":"SCH_PCE_END_DATE"},{"key":"SCH_PCE_END_TIME","alias":"SCH_PCE_END_TIME"},{"key":"SCH_PCE_DURATION_IN_TIME","alias":"SCH_PCE_DURATION_IN_TIME"},{"key":"SCH_PCE_SYMBOLIC_VALUE_REF","alias":"SCH_PCE_SYMBOLIC_VALUE_REF"},{"key":"PEOPLE_ACCRUAL_PROFILE_NAME","alias":"PEOPLE_ACCRUAL_PROFILE_NAME"}],"from":{"view":"EMP","employeeSet":{"hyperfind":{"qualifier":"All Home"},"dateRange":{"startDate":"' . $startDate . 'T00:00","EndDate":"' . $endDate . 'T00:00"}}},"where":[{"key":"PEOPLE_ACCRUAL_PROFILE_NAME","alias":"PEOPLE_ACCRUAL_PROFILE_NAME","operator":"STARTS_WITH","values":["IBEW"]}]}';

        //initiating client
        $client = $this->Client();

        //setting the resource url
        $url = 'commons/data/multi_read';

        //sending the request and receiving the response data
        $response = $client->post($url, [
            'body' => $bodyRequest
        ]);
        //decoding the json response body to an array
        $bodyResponse = json_decode($response->getBody(), true);

        //getting the array of arrays for the data to be extracted
        $childrens = data_get($bodyResponse, 'data.children');

        //going through each children data array to get their attributes
        foreach ($childrens as $children) {
            //getting each childrens data attribute array
            $attributes = data_get($children, 'attributes');
            //dd($attributes);
            $employeeTimeOff = array();

            //if the array count is greater than 10 then it has paycode edit values that we need from the kronos query

            //going through each array in attributes to get the value of each attribute
            foreach ($attributes as $attribute) {

                $key = data_get($attribute, 'alias', '');
                $value = data_get($attribute, 'rawValue', '');
                $fancyArray = [
                    $key => $value
                ];
                $employeeTimeOff = Arr::add($employeeTimeOff, $key, $value);
            }
            $employeesTimeOff[] = $employeeTimeOff;
        }


        $filename = 'Z:\IT_Development\Processes\KronosDimensions-API Integrations\ScheduleTesting2.csv';
        $fp = fopen($filename, 'w');
        foreach ($employeesTimeOff as $employeeTimeOff) {
            fputcsv($fp, $employeeTimeOff);
        }

        fclose($fp);
        //dd($employeesTimeOff);

        return $employeesTimeOff;
    }

    public function getScheduledTimeOffTest()
    {

        $client = $this->Client();

        $url = 'scheduling/schedule';

        $response = $client->get($url, [
            'query' => [
                'start_date' => '2020-01-01',
                'end_date' => '2020-01-31',
                //'employee_id' => 1038,
                //'person_number' => 85580,
                'hyperfind_id' => 1,
                'hyperfind_name' => 'All Home',
                //'location_id' => '',
                //'location_path' => '',
                'exclude_breaks' => false,
                //'order_by' => ''
            ]
        ]);

        $body = $response->getBody();

        $body = \GuzzleHttp\json_decode($body, true);

        $scheduledTimeOffs = data_get($body, 'payCodeEdits');

        //dd($payCodeEdits);

        foreach ($scheduledTimeOffs as $scheduledTimeOff) {
            dd($scheduledTimeOff);
            $scheduleStartDate = data_get($scheduledTimeOff, 'startDate', '');
            $scheduleStartTime = data_get($scheduledTimeOff, 'startTime', '');
            $scheduleEndDate = data_get($scheduledTimeOff, 'endDate', '');
            $scheduleEndTime = data_get($scheduledTimeOff, 'endTime', '');

            $timeOffs[] = [
                'schedule_id' => data_get($scheduledTimeOff, 'id', ''),
                'kronos_id' => data_get($scheduledTimeOff, 'employee.id', ''),
                'employee_number' => data_get($scheduledTimeOff, 'employee.qualifier', ''),
                'start_date' => $scheduleStartDate,
                'start_time' => $scheduleStartTime,
                'end_date' => $scheduleEndDate,
                'end_time' => $scheduleEndTime,
                'start_datetime' => Carbon::createFromFormat('Y-m-d H:i:s', $scheduleStartDate . ' ' . $scheduleStartTime)->toDateTimeString(),
                'end_datetime' => Carbon::createFromFormat('Y-m-d H:i:s', $scheduleEndDate . ' ' . $scheduleEndTime)->toDateTimeString(),
                'locked' => data_get($scheduledTimeOff, 'locked', ''),
                'posted' => data_get($scheduledTimeOff, 'posted', ''),
                'generated' => data_get($scheduledTimeOff, 'generated', ''),
                'deleted' => data_get($scheduledTimeOff, 'deleted', false),
                'pay_code_id' => data_get($scheduledTimeOff, 'payCodeRef.id', ''),
                'pay_code_name' => data_get($scheduledTimeOff, 'payCodeRef.qualifier', ''),
            ];
        }

        return $timeOffs;

    }

    public function updateScheduledTimeOffTest()
    {
        $scheduledTimeOffs = $this->getScheduledTimeOffTest();

        foreach ($scheduledTimeOffs as $scheduledTimeOff) {
            $timeOff = TimeOffSchedule::updateOrCreate(['schedule_id' => $scheduledTimeOff['schedule_id']], $scheduledTimeOff);
        }

        return $timeOff;
    }

    public function accrualTransaction($employeeNumber)
    {
        //$employeeNumber = 85580;
        // testing custom date range TODO: allow user to adjust this themselves
        $dateTime = Carbon::now();
        $startOfYear = $dateTime->startOfYear()->toDateString();
        $endOfYear = $dateTime->endOfYear()->toDateString();

        //$bodyRequest = '{"select":[{"key":"PEOPLE_PERSON_NUMBER","alias":"Employee Number"},{"key":"EMP_COMMON_FULL_NAME","alias":"Full Name"},{"key":"PEOPLE_PAYRULE","alias":"Payrule"},{"key":"PEOPLE_HOME_LABOR_CATEGORY","alias":"Labor Category"},{"key":"EMP_COMMON_PRIMARY_JOB","alias":"Primary Job"},{"key":"EMP_COMMON_PRIMARY_ORG","alias":"Primary Org"},{"key":"TK_ACCRUAL_TRANSACTION_EFFECTIVE_DATE","alias":"TransactionDate"},{"key":"TK_ACCRUAL_TRANSACTION_TYPE","alias":"TransactionType"},{"key":"TK_ACCRUAL_TRANSACTION_HOURS_AMOUNT","alias":"TransactionHours"},{"key":"TK_ACCRUAL_TRANSACTION_ACCRUAL_CODE_NAME","alias":"TransactionAccrualCode"}],"from":{"view":"EMP","employeeSet":{"hyperfind":{"qualifier":"All Home"},"dateRange":{"startDate":"' . $startOfYear . 'T00:00","EndDate":"' . $endOfYear . 'T00:00"}}}}';

        $bodyRequest = '{"select":[{"key":"PEOPLE_PERSON_NUMBER","alias":"Employee Number"},{"key":"EMP_COMMON_FULL_NAME","alias":"Full Name"},{"key":"PEOPLE_PAYRULE","alias":"Payrule"},{"key":"PEOPLE_HOME_LABOR_CATEGORY","alias":"Labor Category"},{"key":"EMP_COMMON_PRIMARY_JOB","alias":"Primary Job"},{"key":"EMP_COMMON_PRIMARY_ORG","alias":"Primary Org"},{"key":"TK_ACCRUAL_TRANSACTION_EFFECTIVE_DATE","alias":"TransactionDate"},{"key":"TK_ACCRUAL_TRANSACTION_TYPE","alias":"TransactionType"},{"key":"TK_ACCRUAL_TRANSACTION_HOURS_AMOUNT","alias":"TransactionHours"},{"key":"TK_ACCRUAL_TRANSACTION_ACCRUAL_CODE_NAME","alias":"TransactionAccrualCode"}],"from":{"view":"EMP","employeeSet":{"hyperfind":{"qualifier":"All Home"},"dateRange":{"startDate":"' . $startOfYear . 'T00:00","EndDate":"' . $endOfYear . 'T00:00"}}},"where":[{"key":"PEOPLE_PERSON_NUMBER","alias":"Employee Number","operator":"STARTS_WITH","values":["' . $employeeNumber . '"]}]}';

        $client = $this->Client();
        $url = 'commons/data/multi_read';

        $response = $client->post($url, [
            'body' => $bodyRequest
        ]);

        $bodyResponse = json_decode($response->getBody(), true);


        $metadata = data_get($bodyResponse, 'metadata');
        $totalElements = data_get($bodyResponse, 'metadata.totalElements');


        $keys = data_get($bodyResponse, 'data.key');
        $childrens = data_get($bodyResponse, 'data.children');

        //$childrens = array_slice($childrens, 0, 30);

        //dd($childrens);
        foreach ($childrens as $children) {
            //$children = array_sort_recursive($children);

            $attributes = data_get($children, 'attributes');
            $employeeNumber = data_get($children, 'coreEntityKey.EMP.qualifier');

            //dd($attributes);
            foreach ($attributes as $attribute) {

                $alias = data_get($attribute, 'alias');

                switch ($alias) {
                    case "Employee Number":
                        $employeeNumber = data_get($attribute, 'rawValue', '');
                        break;
                    case "Full Name":
                        $employeeName = data_get($attribute, 'rawValue', '');
                        break;
                    case "Payrule":
                        $payrule = data_get($attribute, 'rawValue', '');
                        break;
                    case "Labor Category":
                        $laborCategory = data_get($attribute, 'rawValue', '');
                        break;
                    case "Primary Job":
                        $primaryJob = data_get($attribute, 'rawValue', '');
                        break;
                    case "Primary Org":
                        $primaryOrg = data_get($attribute, 'rawValue', '');
                        break;
                    case "TransactionType":
                        $transactionType = data_get($attribute, 'rawValue', '');
                        break;
                    case "TransactionHours":
                        $transactionHours = data_get($attribute, 'rawValue', '');
                        break;
                    case "TransactionAccrualCode":
                        $transactionAccrualCode = data_get($attribute, 'rawValue', '');
                        break;
                    case "TransactionDate":
                        $transactionDate = data_get($attribute, 'rawValue', '');
                        break;
                }

            }
            $newAttributes[] = [
                'employeeNumber' => $employeeNumber,
                'employeeName' => $employeeName,
                'payrule' => $payrule,
                'laborCategory' => $laborCategory,
                'primaryJob' => $primaryJob,
                'primaryOrg' => $primaryOrg,
                'transactionType' => $transactionType,
                'transactionHours' => $transactionHours,
                'transactionAccrualCode' => $transactionAccrualCode,
                'transactionDate' => $transactionDate
            ];

            //dd($children);
        }
        /*
        $filename = 'Z:\IT_Development\Processes\KronosDimensions-API Integrations\AccrualTransactions.csv';
        $header = ['employeeNumber', 'employeeName', 'payrule', 'laborCategory', 'primaryJob', 'primaryOrg', 'transactionType', 'transactionHours', 'transactionAccrualCode', 'transactionDate'];

        $fp = fopen($filename, 'w');

        fputcsv($fp, $header);
        foreach ($newAttributes as $newAttribute) {
            fputcsv($fp, $newAttribute);
        }
        fclose($fp);
        //dd($newAttributes);
        */
        return $newAttributes;
    }

    public function retrievePayCodes()
    {
        $client = $this->Client();

        $url = 'timekeeping/setup/pay_codes';

        $response = $client->get($url);

        //dd($response->getBody());
        $body = json_decode($response->getBody(), true);

        dd($body);

        return $body;
    }
    /*** Example Requests ***/
    public function examplePostRequest()
    {

        $dateTime = Carbon::now();
        $startOfYear = $dateTime->startOfYear()->toDateString();
        $endOfYear = $dateTime->endOfYear()->toDateString();

        $jsonString = '';

        $client = $this->Client();

        $url = 'commons/data/multi_read';

        $response = $client->post($url, [
            'body' => $jsonString
        ]);

        $body = json_decode($response->getBody(), true);

        dd($body);

        return $body;
    }

    public function exampleGetRequest()
    {
        $jsonString = '';

        $client = $this->Client();

        $url = 'commons/data/multi_read';

        $response = $client->get($url);

        $body = json_decode($response->getBody(), true);

        dd($body);
    }

}
