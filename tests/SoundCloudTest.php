<?php

namespace Alcohol\PhpSoundCloud\Tests;

use Alcohol\PhpSoundCloud\SoundCloud;

class PhpSoundCloudTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SoundCloud
     */
    protected $soundcloud;

    public function setUp()
    {
        $clientId = getenv('clientId');
        $clientSecret = getenv('clientSecret');

        $this->soundcloud = new SoundCloud($clientId, $clientSecret);
    }

    /**
     * @group anonymous
     * @expectedException \GuzzleHttp\Exception\ClientException
     */
    public function testGetTokenUsingCredentialsWithInvalidCredentials()
    {
        $username = 'username';
        $password = 'password';

        $this->soundcloud->getTokenUsingCredentials($username, $password);
    }

    /**
     * @group token
     */
    public function testGetTokenUsingCredentials()
    {
        $username = getenv('username');
        $password = getenv('password');

        $result = $this->soundcloud->getTokenUsingCredentials($username, $password);

        $this->assertTrue(array_key_exists('access_token', $result));
        $this->assertTrue(array_key_exists('expires_in', $result));
        $this->assertTrue(array_key_exists('scope', $result));
        $this->assertTrue(array_key_exists('refresh_token', $result));

        return $result['access_token'];
    }

    /**
     * @group anonymous
     * @expectedException \GuzzleHttp\Exception\ClientException
     */
    public function testGetStreamWithInvalidToken()
    {
        $this->soundcloud->setToken('invalid-token');
        $this->soundcloud->getStream();
    }

    /**
     * @group token
     * @depends testCredentialsLogin
     *
     * @param string $token
     */
    public function testGetStreamWithToken($token)
    {
        $this->soundcloud->setToken($token);
        $result = $this->soundcloud->getStream();

        $this->assertTrue(array_key_exists('collection', $result));
        $this->assertTrue(array_key_exists('next_href', $result));
        $this->assertTrue(array_key_exists('future_href', $result));
    }

    /**
     * @group anonymous
     * @expectedException \GuzzleHttp\Exception\ClientException
     */
    public function testGetFavoritesWithInvalidToken()
    {
        $this->soundcloud->setToken('invalid-token');
        $this->soundcloud->getFavorites();
    }

    /**
     * @group token
     * @depends testCredentialsLogin
     *
     * @param string $token
     */
    public function testGetFavoritesWithToken($token)
    {
        $this->soundcloud->setToken($token);
        $result = $this->soundcloud->getFavorites();

        $this->assertTrue(is_array($result));
    }

    /**
     * @group anonymous
     * @expectedException \GuzzleHttp\Exception\ClientException
     */
    public function testGetPlaylistsWithInvalidToken()
    {
        $this->soundcloud->setToken('invalid-token');
        $this->soundcloud->getPlaylists();
    }

    /**
     * @group token
     * @depends testCredentialsLogin
     *
     * @param string $token
     */
    public function testGetPlaylistsWithToken($token)
    {
        $this->soundcloud->setToken($token);
        $result = $this->soundcloud->getPlaylists();

        $this->assertTrue(is_array($result));
    }

    /**
     * @group anonymous
     */
    public function testGetPlaylistAnonymous()
    {
        $this->soundcloud->setToken(null);

        $playlistId = getenv('playlistId');

        $result = $this->soundcloud->getPlaylist($playlistId);

        $this->assertTrue(is_array($result));
        $this->assertTrue(array_key_exists('kind', $result));
        $this->assertEquals('playlist', $result['kind']);
    }

    /**
     * @group anonymous
     */
    public function testGetTrackAnonymous()
    {
        $this->soundcloud->setToken(null);

        $trackId = getenv('trackId');

        $result = $this->soundcloud->getTrack($trackId);

        $this->assertTrue(is_array($result));
        $this->assertTrue(array_key_exists('kind', $result));
        $this->assertEquals('track', $result['kind']);
    }

    /**
     * @group anonymous
     */
    public function testResolveUriAnonymous()
    {
        $this->soundcloud->setToken(null);

        $playlistId = getenv('playlistId');
        $playlistUri = getenv('playlistUri');

        $result = $this->soundcloud->resolveUri($playlistUri);

        $this->assertEquals(1, preg_match('/(\d+)/', $result, $matches));
        $this->assertEquals($playlistId, $matches[1]);
    }
}
