<?php

namespace Services\Auth;

use Google\Client;
use Google\Service\Oauth2;

class GoogleOAuthService
{
    private Client $client;

    public function __construct()
    {
        $this->client = new Client();

        $this->client->setClientId((string)config('oauth.google.client_id', ''));
        $this->client->setClientSecret((string)config('oauth.google.client_secret', ''));
        $this->client->setRedirectUri((string)config('oauth.google.redirect_uri', ''));

        $this->client->setAccessType('online');
        $this->client->setPrompt('select_account');

        $this->client->setScopes([
            'openid',
            'email',
            'profile',
        ]);
    }

    public function getAuthUrl(string $state): string
    {
        $this->client->setState($state);
        return $this->client->createAuthUrl();
    }

    public function fetchUserByCode(string $code): array
    {
        $token = $this->client->fetchAccessTokenWithAuthCode($code);

        if (isset($token['error'])) {
            return [
                'success' => false,
                'message' => $token['error_description'] ?? $token['error'] ?? 'Google token exchange failed',
                'token' => $token,
            ];
        }

        $this->client->setAccessToken($token);

        $oauth2 = new Oauth2($this->client);
        $googleUser = $oauth2->userinfo->get();

        return [
            'success' => true,
            'token' => $token,
            'google_user' => [
                'id' => $googleUser->getId(),
                'email' => $googleUser->getEmail(),
                'verified_email' => $googleUser->getVerifiedEmail(),
                'name' => $googleUser->getName(),
                'given_name' => $googleUser->getGivenName(),
                'family_name' => $googleUser->getFamilyName(),
                'picture' => $googleUser->getPicture(),
            ],
        ];
    }
}
