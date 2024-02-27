<?php

namespace App\Http\Controllers;

class AuthController extends Controller
{

    //sigin final after initial signin is tested
    public function signin()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        // Initialize the OAuth client
        $oauthClient = new \League\OAuth2\Client\Provider\GenericProvider([
            'clientId'                => env('OAUTH_APP_ID'),
            'clientSecret'            => env('OAUTH_APP_PASSWORD'),
            'redirectUri'             => env('OAUTH_REDIRECT_URI'),
            'urlAuthorize'            => env('OAUTH_AUTHORITY').env('OAUTH_TENANT').env('OAUTH_AUTHORIZE_ENDPOINT'),
            'urlAccessToken'          => env('OAUTH_AUTHORITY').env('OAUTH_TENANT').env('OAUTH_TOKEN_ENDPOINT'),
            'urlResourceOwnerDetails' => '',
            'scopes'                  => env('OAUTH_SCOPE')
        ]);

        // Generate the auth URL
        $authorizationUrl = $oauthClient->getAuthorizationUrl();

        // Save client state so we can validate in response
        $_SESSION['oauth_state'] = $oauthClient->getState();

        // Redirect to authorization endpoint
        header('Location: '.$authorizationUrl);
        exit();
    }

    //gettoken for delegated permissions storing the token in cache TODO: change to a database structure
    public function gettoken()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        // Authorization code should be in the "code" query param
        if (isset($_GET['admin_consent'])) {
            // Check that state matches
            if (empty($_GET['state']) || ($_GET['state'] !== $_SESSION['oauth_state'])) {
                exit('State provided in redirect does not match expected value.');
            }

            // Clear saved state
            unset($_SESSION['oauth_state']);

            // Initialize the OAuth client
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

                // Save the access token and refresh tokens in session
                // This is for demo purposes only. A better method would
                // be to store the refresh token in a secured database
                $tokenCache = new \App\TokenStore\MicrosoftGraphTokenCache;
                $tokenCache->storeTokens($accessToken->getToken(), $accessToken->getExpires());

                // Redirect back to mail page
                //return redirect()->route('home');
            }
            catch (\League\OAuth2\Client\Provider\Exception\IdentityProviderException $e) {
                exit('ERROR getting tokens: '.$e->getMessage());
            }
            exit();
        }
        elseif (isset($_GET['error'])) {
            exit('ERROR: '.$_GET['error'].' - '.$_GET['error_description']);
        }
    }

}
