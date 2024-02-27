<?php

namespace App\Jobs;

use App\API\KronosDimensions;
use App\EmployeeTimeOff;
use App\TimeOffSchedule;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Microsoft\Graph\Exception\GraphException;
use Microsoft\Graph\Graph;
use Microsoft\Graph\Model;

class UpdateEmployeeTimeOff implements ShouldQueue
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
        //creating carbon datetime and setting range of beginning of this week to the end of next week.
        $dateTime = Carbon::now();
        $thisWeek = $dateTime->copy()->startOfWeek()->toDateTimeLocalString();
        $twoWeeks = $dateTime->copy()->startOfWeek()->addWeek()->endOfWeek()->toDateTimeLocalString();

        //setting user principal name for microsoft graph request to get users calendar events
        $userPrincipalName = 'OutlookAPI@wgeld.org';

        //creating an OAuth2 client
        $oauthClient = new \League\OAuth2\Client\Provider\GenericProvider([
            'clientId' => env('OAUTH_APP_ID'),
            'clientSecret' => env('OAUTH_APP_PASSWORD'),
            'redirectUri' => env('OAUTH_REDIRECT_URI'),
            'urlAuthorize' => env('OAUTH_AUTHORITY') . env('OAUTH_TENANT') . env('OAUTH_AUTHORIZE_ENDPOINT'),
            'urlAccessToken' => env('OAUTH_AUTHORITY') . env('OAUTH_TENANT') . env('OAUTH_TOKEN_ENDPOINT'),
            'urlResourceOwnerDetails' => '',
            'scopes' => env('OAUTH_SCOPE')
        ]);

        try {
            // Make the token request
            $accessToken = $oauthClient->getAccessToken('client_credentials', [
                'scope' => env('OAUTH_SCOPE')
            ]);
        } catch (\League\OAuth2\Client\Provider\Exception\IdentityProviderException $e) {
            exit('ERROR getting tokens: ' . $e->getMessage());
        }

        //create a microsoft graph instance
        $graph = new Graph();

        //set the access token to the access token received from the OAuth2 client request
        $graph->setAccessToken($accessToken->getToken());

        //Setting the full url
        $getEventsUrl = '/users/' . $userPrincipalName . '/calendar/events?select=subject,start,end&orderby=start/DateTime&filter=start/dateTime ge \'' . $thisWeek . '\' and end/dateTime le \'' . $twoWeeks . '\'&top=1000';

        try {
            //Requesting events from microsoft using the graph
            $events = $graph->createRequest('GET', $getEventsUrl)
                ->setReturnType(Model\Event::class)
                ->execute();
        } catch (GraphException $e) {

        }

        //Gathering the specific information needed from the response data
        if (!empty($events)) {
            //delete all events
            foreach ($events as $event) {
                $deleteEventUrl = '/users/' . $userPrincipalName . '/events/' . $event->getEventID();
                //delete request/response
                $response = $graph->createRequest('DELETE', $deleteEventUrl)
                    ->setReturnType(Model\Event::class)
                    ->execute();
            }
        }
        //Creating a KronosDimensions class instance
        $kronosDimensionsAPI = new KronosDimensions;

        //Creating a collection of the employee schedule pay code edits from Kronos to compare with existing events
        $kronosEmployeeTimeOffs = collect($kronosDimensionsAPI->getEmployeeTimeOffs($thisWeek, $twoWeeks))->sortBy('SCH_PCE_START_DATETIME');

        //Looping through each new event that needs to be created. Setting up the create event to the calendar
        foreach ($kronosEmployeeTimeOffs as $kronosEmployeeTimeOff) {

            //Creating the data to be sent to microsoft graph api to create the event
            $data = [
                'Subject' => $kronosEmployeeTimeOff['EMP_COMMON_FULL_NAME'] . ' - OFF',
                'Body' => [
                    'ContentType' => 'HTML',
                    'Content' => $kronosEmployeeTimeOff['EMP_COMMON_FULL_NAME'] . ' is off today',
                ],
                'Start' => [
                    'DateTime' => $kronosEmployeeTimeOff['SCH_PCE_START_DATE'] . 'T' . $kronosEmployeeTimeOff['SCH_PCE_START_TIME'],
                    'TimeZone' => 'Eastern Standard Time',
                ],
                'End' => [
                    'DateTime' => $kronosEmployeeTimeOff['SCH_PCE_END_DATE'] . 'T' . $kronosEmployeeTimeOff['SCH_PCE_END_TIME'],
                    'TimeZone' => 'Eastern Standard Time',
                ],
            ];

            //The resource URL to send the data to
            $url = '/users/' . $userPrincipalName . '/events';

            //Send POST request to create the event
            $response = $graph->createRequest('POST', $url)
                ->attachBody($data)
                ->setReturnType(Model\Event::class)
                ->execute();

        }
    }
}
