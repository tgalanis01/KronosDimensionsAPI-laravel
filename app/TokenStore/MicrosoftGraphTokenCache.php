<?php

namespace App\TokenStore;

class MicrosoftGraphTokenCache
{

    //TODO: Store in database
    public function storeTokens($access_token, $expires)
    {
        $_SESSION['access_token'] = $access_token;
        $_SESSION['token_expires'] = $expires;
    }

    public function clearTokens()
    {
        unset($_SESSION['access_token']);
        unset($_SESSION['token_expires']);
    }

    public function getAccessToken()
    {
        // Check if tokens exist
        if (empty($_SESSION['access_token']) ||
            empty($_SESSION['token_expires'])) {
            return redirect()->route('authorize');
        }

        // Check if token is expired
        //Get current time + 5 minutes (to allow for time differences)
        $now = time() + 300;
        if ($_SESSION['token_expires'] <= $now) {
            // Token is expired (or very close to it)
            // so let's refresh

            // Initialize the OAuth client
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
                $newToken = $oauthClient->getAccessToken('client_credentials', [
                    'scope' => env('OAUTH_SCOPE')
                ]);

                // Store the new values
                $this->storeTokens($newToken->getToken(), $newToken->getExpires());

                return $newToken->getToken();
            } catch (\League\OAuth2\Client\Provider\Exception\IdentityProviderException $e) {
                return '';
            }
        } else {
            // Token is still valid, just return it
            return $_SESSION['access_token'];
        }
    }
}
