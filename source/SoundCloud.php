<?php

namespace Alcohol\PhpSoundCloud;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Client;

class SoundCloud
{
    /**
     * @var string
     */
    protected $clientId;

    /**
     * @var string
     */
    protected $clientSecret;

    /**
     * @var string
     */
    protected $token;

    /**
     * @var string
     */
    protected $apiBase = 'https://api.soundcloud.com';

    /**
     * @var ClientInterface
     */
    protected $client;

    /**
     * @param string $clientId
     * @param string $clientSecret
     * @throws \InvalidArgumentException
     */
    public function __construct($clientId, $clientSecret = null)
    {
        $this->clientId = $clientId;

        if (!empty($clientSecret)) {
            $this->clientSecret = $clientSecret;
        }
    }

    /**
     * @param string $uri
     * @return $this
     */
    public function setApiBase($uri)
    {
        $this->apiBase = $uri;

        return $this;
    }

    /**
     * @param string $token
     * @return $this
     */
    public function setToken($token)
    {
        $this->token = $token;

        return $this;
    }

    /**
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param ClientInterface $client
     * @return $this
     */
    public function setClient(ClientInterface $client)
    {
        $this->client = $client;

        return $this;
    }

    /**
     * @return ClientInterface
     */
    public function getClient()
    {
        if (is_null($this->client)) {
            $this->client = new Client();
        }

        return $this->client;
    }

    /**
     * @param string $redirectUri
     * @param array $options
     * @param string $connectUri
     * @return string
     */
    public function getAuthorizeUri($redirectUri, array $options = [], $connectUri = 'https://soundcloud.com/connect')
    {
        $defaults = [
            'client_id' => $this->clientId,
            'redirect_uri' => $redirectUri,
            'response_type' => 'code',
            'scope' => 'non-expiring'
        ];

        $query = http_build_query(array_merge($defaults, $options));

        return sprintf('%s?%s', $connectUri, $query);
    }

    /**
     * @param string $username
     * @param string $password
     * @return array
     * @throws \RuntimeException
     */
    public function getTokenUsingCredentials($username, $password)
    {
        $response = $this->getClient()->post(
            $this->apiBase . '/oauth2/token',
            [
                'headers' => ['Accept' => 'application/json'],
                'body' => [
                    'client_id' => $this->clientId,
                    'client_secret' => $this->clientSecret,
                    'username' => $username,
                    'password' => $password,
                    'grant_type' => 'password'
                ]
            ]
        );

        return $response->json();
    }

    /**
     * @return array
     * @throws \GuzzleHttp\Exception\ClientException
     */
    public function getStream()
    {
        $response = $this->getClient()->get(
            $this->apiBase . '/me/activities/tracks/affiliated',
            [
                'headers' => ['Accept' => 'application/json'],
                'query' => ['oauth_token' => $this->token]
            ]
        );

        return $response->json();
    }

    /**
     * @return array
     * @throws \GuzzleHttp\Exception\ClientException
     */
    public function getPlaylists()
    {
        $response = $this->getClient()->get(
            $this->apiBase . '/me/playlists',
            [
                'headers' => ['Accept' => 'application/json'],
                'query' => ['oauth_token' => $this->token]
            ]
        );

        return $response->json();
    }

    /**
     * @return array
     * @throws \GuzzleHttp\Exception\ClientException
     */
    public function getFavorites()
    {
        $response = $this->getClient()->get(
            $this->apiBase . '/me/favorites',
            [
                'headers' => ['Accept' => 'application/json'],
                'query' => ['oauth_token' => $this->token]
            ]
        );

        return $response->json();
    }

    /**
     * @param integer $trackId
     * @return array
     * @throws \GuzzleHttp\Exception\ClientException
     */
    public function getTrack($trackId)
    {
        if (!is_null($this->token)) {
            $query = ['oauth_token' => $this->token];
        } else {
            $query = ['client_id' => $this->clientId];
        }

        $response = $this->getClient()->get(
            $this->apiBase . '/tracks/' . (int) $trackId,
            [
                'headers' => ['Accept' => 'application/json'],
                'query' => $query
            ]
        );

        return $response->json();
    }

    /**
     * @param integer $playlistId
     * @return array
     * @throws \GuzzleHttp\Exception\ClientException
     */
    public function getPlaylist($playlistId)
    {
        if (!is_null($this->token)) {
            $query = ['oauth_token' => $this->token];
        } else {
            $query = ['client_id' => $this->clientId];
        }

        $response = $this->getClient()->get(
            $this->apiBase . '/playlists/' . (int) $playlistId,
            [
                'headers' => ['Accept' => 'application/json'],
                'query' => $query
            ]
        );

        return $response->json();
    }

    /**
     * @param integer $id
     * @return string
     */
    public function getTrackStreamUri($id)
    {
        $uri = $this->getTrack($id)['stream_url'];

        if (!is_null($this->token)) {
            $query = ['oauth_token' => $this->token];
        } else {
            $query = ['client_id' => $this->clientId];
        }

        $response = $this->getClient()->get(
            $uri,
            [
                'headers' => ['Accept' => 'application/json'],
                'query' => $query,
                'allow_redirects' => false
            ]
        );

        return $response->getHeader('Location');
    }

    /**
     * @param string $uri
     * @return string
     * @throws \GuzzleHttp\Exception\ClientException
     */
    public function resolveUri($uri)
    {
        $query = ['url' => $uri, 'client_id' => $this->clientId];

        $response = $this->getClient()->get(
            $this->apiBase . '/resolve',
            [
                'headers' => ['Accept' => 'application/json'],
                'query' => $query
            ]
        );

        return $response->getEffectiveUrl();
    }
}
