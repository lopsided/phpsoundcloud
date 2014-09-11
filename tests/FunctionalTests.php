<?php namespace Alcohol\Tests;

use Alcohol\SoundCloud;

class FunctionalTests extends \PHPUnit_Framework_TestCase
{
    protected $clientId = 'myId';

    protected $clientSecret = 'mySecret';

    protected $redirectUri = 'http://domain.tld/redirect';

    /**
     * @test
     * @group functional
     */
    public function class_SoundCloud_exists()
    {
        $this->assertTrue(class_exists('Alcohol\SoundCloud'));

        $soundcloud = new SoundCloud(
            $this->clientId,
            $this->clientSecret,
            $this->redirectUri
        );

        $this->assertInstanceOf('Alcohol\SoundCloud', $soundcloud);

        return $soundcloud;
    }

    /**
     * @test
     * @group functional
     * @depends class_SoundCloud_exists
     *
     * @param SoundCloud $soundcloud
     */
    public function calling_getTokenAuthUri_returns_valid_uri(SoundCloud $soundcloud)
    {
        $string = $soundcloud->getTokenAuthUri();

        list($uri, $query) = explode('?', $string);

        $this->assertRegExp('#(http|https)://soundcloud.com/connect#', $uri);

        $pairs = explode('&', $query);
        $keys = [];

        foreach ($pairs as $pair) {
            list($keys[], /* $value */) = explode('=', $pair);
        }

        $this->assertTrue(in_array('client_id', $keys));
        $this->assertTrue(in_array('client_secret', $keys));
        $this->assertTrue(in_array('response_type', $keys));
        $this->assertTrue(in_array('scope', $keys));
    }
}
