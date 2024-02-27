<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Microsoft\Graph\Graph;
use Microsoft\Graph\Model;

class UpdateSharepointWCFCustomerStats implements ShouldQueue
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

        $listID = 'c30a9c40-85ac-455b-a031-c2edd0742af6';

        $siteID = 'wgeld.sharepoint.com';

        //$itemID = '1'; //Westfield
        //$itemID = '2'; //Hilltowns
        //$itemID = '3'; //Wired West

        $wcfCustomerStats = DB::connection('sqlsrv2')->table('SharepointWCFCustomerStats')->get();

        $oauthClient = new \League\OAuth2\Client\Provider\GenericProvider([
            'clientId'                => env('OAUTH_APP_ID'),
            'clientSecret'            => env('OAUTH_APP_PASSWORD'),
            'redirectUri'             => env('OAUTH_REDIRECT_URI'),
            'urlAuthorize'            => env('OAUTH_AUTHORITY').env('OAUTH_TENANT').env('OAUTH_AUTHORIZE_ENDPOINT'),
            'urlAccessToken'          => env('OAUTH_AUTHORITY').env('OAUTH_TENANT').env('OAUTH_TOKEN_ENDPOINT'),
            'urlResourceOwnerDetails' => '',
            'scopes'                  => env('OAUTH_SCOPE')
        ]);

        try {
            // Make the token request
            $accessToken = $oauthClient->getAccessToken('client_credentials', [
                'scope' => env('OAUTH_SCOPE')
            ]);
        }
        catch (\League\OAuth2\Client\Provider\Exception\IdentityProviderException $e) {
            exit('ERROR getting tokens: '.$e->getMessage());
        }

        //create a microsoft graph instance
        $graph = new Graph();

        //set the access token to the access token received from the OAuth2 client request
        $graph->setAccessToken($accessToken->getToken());

        foreach($wcfCustomerStats as $wcfCustomerStat) {

            $itemID = $wcfCustomerStat->SharepointItemID;

            $url = '/sites/' . $siteID . '/lists/' . $listID . '/items/' . $itemID . '/fields';
            //$url = '/sites/'. $siteID .'/lists/' . $listID .'/items?expand=fields';
            //$url = '/sites/'. $siteID .'/lists/' . $listID . '/columns';
            //$url = '/sites/'. $siteID .'/lists/';

            $data = [
                'Customers' => $wcfCustomerStat->numberOfCustomers
            ];

            $listItems = $graph->createRequest('PATCH', $url)
                ->attachBody(json_encode($data))
                ->setReturnType(Model\ListItem::class)
                ->execute();

            //var_dump($listItems);
        }
        //dd($listItems);

    }
}
