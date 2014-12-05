<?php

/**
 * (c) Rob Bast <rob.bast@gmail.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Alcohol\Tests;

use Alcohol\SoundCloud;

class IntegrationTests extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SoundCloud
     */
    protected $soundcloud;

    public function setUp()
    {
        $options = [
            'client_id' => getenv('clientId'),
            'secret' => getenv('clientSecret')
        ];

        $this->soundcloud = new SoundCloud($options);
    }

    /**
     * @test
     * @group integration
     * @expectedException \GuzzleHttp\Exception\ClientException
     */
    public function calling_getTokenUsingCredentials_with_invalid_credentials_should_throw_exception()
    {
        $username = 'username';
        $password = 'password';

        $this->soundcloud->getTokenUsingCredentials($username, $password);
    }

    /**
     * @test
     * @group integration
     */
    public function calling_getTokenUsingCredentials_with_valid_credentials_should_return_token()
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
     * @test
     * @group integration
     * @expectedException \GuzzleHttp\Exception\ClientException
     */
    public function calling_getStream_with_invalid_token_should_throw_exception()
    {
        $this->soundcloud->setToken('invalid-token');
        $this->soundcloud->getStream();
    }

    /**
     * @test
     * @group integration
     * @depends calling_getTokenUsingCredentials_with_valid_credentials_should_return_token
     *
     * @param string $token
     */
    public function calling_getStream_with_valid_token_should_return_stream($token)
    {
        $this->soundcloud->setToken($token);
        $result = $this->soundcloud->getStream();

        $this->assertTrue(array_key_exists('collection', $result));
        $this->assertTrue(array_key_exists('next_href', $result));
        $this->assertTrue(array_key_exists('future_href', $result));
    }

    /**
     * @test
     * @group integration
     * @expectedException \GuzzleHttp\Exception\ClientException
     */
    public function calling_getFavorites_with_invalid_token_should_throw_exception()
    {
        $this->soundcloud->setToken('invalid-token');
        $this->soundcloud->getFavorites();
    }

    /**
     * @test
     * @group integration
     * @depends calling_getTokenUsingCredentials_with_valid_credentials_should_return_token
     *
     * @param string $token
     */
    public function calling_getFavorites_with_valid_token_should_return_favorites($token)
    {
        $this->soundcloud->setToken($token);
        $result = $this->soundcloud->getFavorites();

        $this->assertTrue(is_array($result));
    }

    /**
     * @test
     * @group integration
     * @expectedException \GuzzleHttp\Exception\ClientException
     */
    public function calling_getPlaylists_with_invalid_token_should_throw_exception()
    {
        $this->soundcloud->setToken('invalid-token');
        $this->soundcloud->getPlaylists();
    }

    /**
     * @test
     * @group integration
     * @depends calling_getTokenUsingCredentials_with_valid_credentials_should_return_token
     *
     * @param string $token
     */
    public function calling_getPlaylists_with_valid_token_should_return_playlists($token)
    {
        $this->soundcloud->setToken($token);
        $result = $this->soundcloud->getPlaylists();

        $this->assertTrue(is_array($result));
        $first = current($result);
        $this->assertEquals('playlist', $first['kind']);
    }

    /**
     * @test
     * @group integration
     */
    public function calling_getPlaylist_anonymously_returns_playlist_details()
    {
        $this->soundcloud->setToken(null);

        $playlistId = getenv('playlistId');

        $result = $this->soundcloud->getPlaylist($playlistId);

        $this->assertTrue(is_array($result));
        $this->assertTrue(array_key_exists('kind', $result));
        $this->assertEquals('playlist', $result['kind']);
    }

    /**
     * @test
     * @group integration
     */
    public function calling_getTrack_anonymously_returns_track_details()
    {
        $this->soundcloud->setToken(null);

        $trackId = getenv('trackId');

        $result = $this->soundcloud->getTrack($trackId);

        $this->assertTrue(is_array($result));
        $this->assertTrue(array_key_exists('kind', $result));
        $this->assertEquals('track', $result['kind']);
    }

    /**
     * @test
     * @group integration
     */
    public function calling_getTrackStreamUri_anonymously_returns_streaming_uri()
    {
        $this->soundcloud->setToken(null);

        $trackId = getenv('trackId');

        $this->assertInternalType('string', $this->soundcloud->getTrackStreamUri($trackId));
    }

    /**
     * @test
     * @group integration
     */
    public function calling_resolveUri_anonymously_returns_correct_resolved_target_uri()
    {
        $this->soundcloud->setToken(null);

        $playlistId = getenv('playlistId');
        $playlistUri = getenv('playlistUri');

        $result = $this->soundcloud->resolveUri($playlistUri);

        $this->assertEquals(1, preg_match('/(\d+)/', $result, $matches));
        $this->assertEquals($playlistId, $matches[1]);
    }
}
