<?php

namespace Alcohol\PhpSoundCloud;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Message\ResponseInterface;
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
    protected $redirectUri;

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
     * @var string
     */
    protected $headers = ['Accept' => 'application/json'];

    /**
     * @param string $clientId
     * @param string $clientSecret
     * @param string $redirectUri
     * @throws \InvalidArgumentException
     */
    public function __construct($clientId, $clientSecret = null, $redirectUri = null)
    {
        $this->setClientId($clientId);
        $this->setClientSecret($clientSecret);
        $this->setRedirectUri($redirectUri);
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
     * @return string
     */
    public function getApiBase()
    {
        return $this->apiBase;
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
     * @param array $headers
     * @return $this
     */
    public function setHeaders(array $headers)
    {
        $this->headers = $headers;

        return $this;
    }

    /**
     * @return string
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * @param string $clientId
     * @return $this
     */
    public function setClientId($clientId)
    {
        $this->clientId = $clientId;

        return $this;
    }

    /**
     * @return string
     */
    public function getClientId()
    {
        return $this->clientId;
    }

    /**
     * @param string $clientSecret
     * @return $this
     */
    public function setClientSecret($clientSecret)
    {
        $this->clientSecret = $clientSecret;

        return $this;
    }

    /**
     * @return string
     */
    public function getClientSecret()
    {
        return $this->clientSecret;
    }

    /**
     * @param string $redirectUri
     * @return $this
     */
    public function setRedirectUri($redirectUri)
    {
        $this->redirectUri = $redirectUri;

        return $this;
    }

    /**
     * @return string
     */
    public function getRedirectUri()
    {
        return $this->redirectUri;
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
     * @return bool
     */
    public function returnJson()
    {
        $this->headers = array_merge($this->headers, ['Accept' => 'application/json']);

        return $this;
    }

    /**
     * @return bool
     */
    public function returnXml()
    {
        $this->headers = array_merge($this->headers, ['Accept' => 'application/xml']);

        return $this;
    }

    /**
     * @param array $options
     * @param string $connectUri
     * @return string
     */
    public function getTokenAuthUri(array $options = [], $connectUri = 'https://soundcloud.com/connect')
    {
        $query = http_build_query(
            array_merge(
                [
                    'client_id' => $this->getClientId(),
                    'client_secret' => $this->getClientSecret(),
                    'response_type' => 'code',
                    'scope' => 'non-expiring'
                ],
                $options
            )
        );

        return sprintf('%s?%s', $connectUri, $query);
    }

    /**
     * @param string $username
     * @param string $password
     * @return mixed
     */
    public function getTokenUsingCredentials($username, $password)
    {
        $options = [
            'headers' => $this->getHeaders(),
            'body' => [
                'client_id' => $this->getClientId(),
                'client_secret' => $this->getClientSecret(),
                'username' => $username,
                'password' => $password,
                'grant_type' => 'password'
            ]
        ];

        $response = $this->getClient()->post($this->getApiBase() . '/oauth2/token', $options);

        return $this->handleResponse($response);
    }

    /**
     * @param string $code
     * @param array $body
     * @return mixed
     */
    public function getTokenUsingCode($code, array $body = [])
    {
        $options = [
            'headers' => $this->getHeaders(),
            'body' => [
                'code' => $code,
                'client_id' => $this->getClientId(),
                'client_secret' => $this->getClientSecret(),
                'redirect_uri' => $this->getRedirectUri(),
                'grant_type' => 'authorization_code'
            ] + $body
        ];

        $response = $this->getClient()->post($this->getApiBase() . '/oauth2/token', $options);

        return $this->handleResponse($response);
    }

    /**
     * @param string $refreshToken
     * @param array $body
     * @return mixed
     */
    public function refreshToken($refreshToken, array $body = [])
    {
        $options = [
            'headers' => $this->getHeaders(),
            'body' => [
                'refresh_token' => $refreshToken,
                'client_id' => $this->getClientId(),
                'client_secret' => $this->getClientSecret(),
                'redirect_uri' => $this->getRedirectUri(),
                'grant_type' => 'refresh_token'
            ] + $body
        ];

        $response = $this->getClient()->post($this->getApiBase() . '/oauth2/token', $options);

        return $this->handleResponse($response);
    }

    /**
     * @return mixed
     * @throws \GuzzleHttp\Exception\ClientException
     */
    public function getStream()
    {
        $options = [
            'headers' => $this->getHeaders(),
            'query' => ['oauth_token' => $this->getToken()]
        ];

        $response = $this->getClient()->get($this->getApiBase() . '/me/activities/tracks/affiliated', $options);

        return $this->handleResponse($response);
    }

    /**
     * @return mixed
     * @throws \GuzzleHttp\Exception\ClientException
     */
    public function getPlaylists()
    {
        $options = [
            'headers' => $this->getHeaders(),
            'query' => ['oauth_token' => $this->getToken()]
        ];

        $response = $this->getClient()->get($this->getApiBase() . '/me/playlists', $options);

        return $this->handleResponse($response);
    }

    /**
     * @return mixed
     * @throws \GuzzleHttp\Exception\ClientException
     */
    public function getFavorites()
    {
        $options = [
            'headers' => $this->getHeaders(),
            'query' => ['oauth_token' => $this->getToken()]
        ];

        $response = $this->getClient()->get($this->getApiBase() . '/me/favorites', $options);

        return $this->handleResponse($response);
    }

    /**
     * @param integer $trackId
     * @return mixed
     * @throws \GuzzleHttp\Exception\ClientException
     */
    public function getTrack($trackId)
    {
        if (!is_null($this->getToken())) {
            $query = ['oauth_token' => $this->getToken()];
        } else {
            $query = ['client_id' => $this->getClientId()];
        }

        $options = [
            'headers' => $this->getHeaders(),
            'query' => $query
        ];

        $response = $this->getClient()->get($this->getApiBase() . '/tracks/' . (int) $trackId, $options);

        return $this->handleResponse($response);
    }

    /**
     * @param integer $playlistId
     * @return mixed
     * @throws \GuzzleHttp\Exception\ClientException
     */
    public function getPlaylist($playlistId)
    {
        if (!is_null($this->getToken())) {
            $query = ['oauth_token' => $this->getToken()];
        } else {
            $query = ['client_id' => $this->getClientId()];
        }

        $options = [
            'headers' => $this->getHeaders(),
            'query' => $query
        ];

        $response = $this->getClient()->get($this->getApiBase() . '/playlists/' . (int) $playlistId, $options);

        return $this->handleResponse($response);
    }

    /**
     * @param integer $trackId
     * @return string
     */
    public function getTrackStreamUri($trackId)
    {
        $headers = $this->getHeaders();
        $this->returnJson();
        $streamUri = $this->getTrack($trackId)['stream_url'];
        $this->setHeaders($headers);

        if (!is_null($this->getToken())) {
            $query = ['oauth_token' => $this->getToken()];
        } else {
            $query = ['client_id' => $this->getClientId()];
        }

        $options = [
            'headers' => $this->getHeaders(),
            'query' => $query,
            'allow_redirects' => false
        ];

        $response = $this->getClient()->get($streamUri, $options);

        return $response->getHeader('Location');
    }

    /**
     * @param string $uri
     * @return string
     * @throws \GuzzleHttp\Exception\ClientException
     */
    public function resolveUri($uri)
    {
        $options = [
            'headers' => $this->getHeaders(),
            'query' => ['url' => $uri, 'client_id' => $this->getClientId()]
        ];

        $response = $this->getClient()->get($this->getApiBase() . '/resolve', $options);

        return $response->getEffectiveUrl();
    }

    /**
     * @param ResponseInterface $response
     * @return mixed
     */
    protected function handleResponse(ResponseInterface $response)
    {
        $contentType = $response->getHeader('Content-type');

        if (false !== stripos($contentType, 'application/json')) {
            return $response->json();
        }

        if (false !== stripos($contentType, 'application/xml')) {
            return $response->xml();
        }

        return $response->getBody();
    }
}
