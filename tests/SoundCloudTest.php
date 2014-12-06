<?php

/**
 * (c) Rob Bast <rob.bast@gmail.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Alcohol\Tests;

use Alcohol\SoundCloud;
use GuzzleHttp\Subscriber\Mock;
use GuzzleHttp\Message\Response;
use GuzzleHttp\Stream\Stream;

class SoundCloudTest extends \PHPUnit_Framework_TestCase
{
    /** @var SoundCloud */
    protected $soundcloud;

    /** @var Mock */
    protected $mock;

    public function setUp()
    {
        $options = [
            'client_id' => getenv('client_id') ?: 'myId',
            'client_secret' => getenv('client_secret') ?: 'mySecret',
            'redirect_uri' => getenv('redirect_uri') ?: 'http://domain.tld/redirect'
        ];

        $this->soundcloud = new SoundCloud($options);

        if (!getenv('client_id')) {
            $this->mock = new Mock();
            $this->soundcloud->getClient()->getEmitter()->attach($this->mock);
        }
    }

    /**
     * @test
     * @group functional
     *
     * @expectedException \BadMethodCallException
     */
    public function instantiating_class_without_required_options_throws_exception()
    {
        new SoundCloud(array());
    }

    /**
     * @test
     * @group functional
     */
    public function calling_getTokenAuthUri_returns_valid_uri()
    {
        $string = $this->soundcloud->getTokenAuthUri();

        list($uri, $query) = explode('?', $string);

        $this->assertRegExp('#(http|https)://soundcloud.com/connect#', $uri);

        $pairs = explode('&', $query);
        $keys = [];

        foreach ($pairs as $pair) {
            list($keys[], /* $value */) = explode('=', $pair);
        }

        $this->assertTrue(in_array('client_id', $keys));
        $this->assertTrue(in_array('client_secret', $keys));
        $this->assertTrue(in_array('redirect_uri', $keys));
        $this->assertTrue(in_array('response_type', $keys));
        $this->assertTrue(in_array('scope', $keys));
    }

    /**
     * @test
     * @group integration
     * @expectedException \GuzzleHttp\Exception\ClientException
     */
    public function calling_getTokenUsingCredentials_with_invalid_credentials_should_throw_exception()
    {
        $username = 'myUsername';
        $password = 'myPassword';

        if (isset($this->mock)) {
            $this->mock->addResponse(new Response(401));
        }

        $this->soundcloud->getTokenUsingCredentials($username, $password);
    }

    /**
     * @test
     * @group integration
     */
    public function calling_getTokenUsingCredentials_with_valid_credentials_should_return_token()
    {
        $username = getenv('soundcloud_username');
        $password = getenv('soundcloud_password');

        if (isset($this->mock)) {
            $this->mock->addResponse(
                new Response(
                    200,
                    ['Content-type' => 'application/json'],
                    Stream::factory(json_encode([
                        'access_token' => 'dummy-access-token',
                        'expires_in' => 300,
                        'scope' => '*',
                        'refresh_token' => 'dummy-refresh-token'
                    ]))
                )
            );
        }

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
        if (isset($this->mock)) {
            $this->mock->addResponse(new Response(401));
        }

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
        if (isset($this->mock)) {
            $this->mock->addResponse(
                new Response(
                    200,
                    ['Content-type' => 'application/json'],
                    Stream::factory(json_encode([
                        'collection' => [],
                        'next_href' => 'http://soundcloud.com/next',
                        'future_href' => 'http://soundcloud.com/future'
                    ]))
                )
            );
        }

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
        if (isset($this->mock)) {
            $this->mock->addResponse(new Response(401));
        }

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
        if (isset($this->mock)) {
            $this->mock->addResponse(
                new Response(200, ['Content-type' => 'application/json'], Stream::factory(json_encode([])))
            );
        }

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
        if (isset($this->mock)) {
            $this->mock->addResponse(new Response(401));
        }

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
        if (isset($this->mock)) {
            $this->mock->addResponse(
                new Response(
                    200,
                    ['Content-type' => 'application/json'],
                    Stream::factory(json_encode([
                        ['kind' => 'playlist']
                    ]))
                )
            );
        }

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
        if (isset($this->mock)) {
            $this->mock->addResponse(
                new Response(
                    200,
                    ['Content-type' => 'application/json'],
                    Stream::factory(json_encode([
                        'kind' => 'playlist'
                    ]))
                )
            );
        }

        $this->soundcloud->setToken(null);
        $playlistId = getenv('playlist_id');
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
        if (isset($this->mock)) {
            $this->mock->addResponse(
                new Response(
                    200,
                    ['Content-type' => 'application/json'],
                    Stream::factory(json_encode([
                        'kind' => 'track'
                    ]))
                )
            );
        }

        $this->soundcloud->setToken(null);
        $trackId = getenv('track_id');
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
        if (isset($this->mock)) {
            $this->mock->addMultiple([
                new Response(
                    200,
                    ['Content-type' => 'application/json'],
                    Stream::factory(json_encode([
                        'kind' => 'track',
                        'stream_url' => 'http://soundcloud/placeholder'
                    ]))
                ),
                new Response(
                    302,
                    ['Location' => 'http://soundcloud.com/streamuri']
                )
            ]);
        }

        $this->soundcloud->setToken(null);
        $trackId = getenv('track_id');
        $result = $this->soundcloud->getTrackStreamUri($trackId);
        $this->assertInternalType('string', $result);

        if (isset($this->mock)) {
            $this->assertEquals('http://soundcloud.com/streamuri', $result);
        }
    }

    /**
     * @test
     * @group integration
     */
    public function calling_resolveUri_anonymously_returns_correct_resolved_target_uri()
    {
        $this->soundcloud->setToken(null);
        $playlistId = getenv('playlist_id') ?: '1';
        $playlistUri = getenv('playlist_uri') ?: 'https://soundcloud.com/myUsername/sets/myPlaylist';

        if (isset($this->mock)) {
            $this->mock->addMultiple([
                new Response(
                    302,
                    ['Location' => 'http://soundcloud.com/resolved/' . $playlistId]
                ),
                new Response(200)
            ]);
        }

        $result = $this->soundcloud->resolveUri($playlistUri);
        $this->assertEquals(1, preg_match('/(\d+)/', $result, $matches));
        $this->assertEquals($playlistId, $matches[1]);

        if (isset($this->mock)) {
            $this->assertEquals('http://soundcloud.com/resolved/' . $playlistId, $result);
        }
    }
}
