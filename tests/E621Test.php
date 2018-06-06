<?php

namespace jacklul\E621API\Tests;

use jacklul\E621API\E621;
use PHPUnit\Framework\TestCase;

final class E621Test extends TestCase
{
    /**
     * @var E621
     */
    private $api;

    protected function setUp()
    {
        $this->api = new E621(
            [
                'headers' => [
                    'User-Agent' => explode(' by', \PHPUnit\Runner\Version::getVersionString())[0] . ' @ ' . php_uname()
                ]
            ]
        );
    }

    protected function tearDown()
    {
        $this->api = null;
    }

    public function testConstructWithInvalidOptions()
    {
        if ((float)phpversion() < 7.0) {
            /** @noinspection PhpUndefinedClassInspection */
            /** @noinspection PhpUndefinedMethodInspection */
            $this->setExpectedException(\PHPUnit_Framework_Error::class);
        } else {
            /** @noinspection PhpParamsInspection */
            $this->expectException(\TypeError::class);
        }

        new E621(null);
    }

    public function testDebugLogHandler()
    {
        $tmp = '';
        $this->api->setDebugLogHandler(
            function ($text) use (&$tmp) {
                $tmp .= $text;
            }
        );

        $this->api->postIndex(['limit' => 1]);

        $this->assertContains('Verbose HTTP Request output', $tmp);

        if ((float)phpversion() < 7.0) {
            /** @noinspection PhpUndefinedMethodInspection */
            $this->setExpectedException(\InvalidArgumentException::class);
        } else {
            /** @noinspection PhpParamsInspection */
            $this->expectException(\InvalidArgumentException::class);
        }

        $this->api->setDebugLogHandler(['InvalidClass', 'debugLogHandler']);

        $this->api->setDebugLogHandler(null);   // Nothing should happen
    }

    public function testProgressHandler()
    {
        $tmp = [];
        $this->api->setProgressHandler(
            function ($downloadTotal, $downloadedBytes, $uploadTotal, $uploadedBytes) use (&$tmp) {
                $tmp = [
                    'downloadTotal'   => $downloadTotal,
                    'downloadedBytes' => $downloadedBytes,
                    'uploadTotal'     => $uploadTotal,
                    'uploadedBytes'   => $uploadedBytes,
                ];
            }
        );

        $this->api->postIndex(['limit' => 1]);

        $this->assertGreaterThan(0, $tmp['downloadTotal']);

        if ((float)phpversion() < 7.0) {
            /** @noinspection PhpUndefinedMethodInspection */
            $this->setExpectedException(\InvalidArgumentException::class);
        } else {
            /** @noinspection PhpParamsInspection */
            $this->expectException(\InvalidArgumentException::class);
        }

        $this->api->setProgressHandler(['InvalidClass', 'progressHandler']);

        $this->api->setProgressHandler(null);   // Nothing should happen
    }

    /**
     * @expectedException \jacklul\E621API\Exception\LoginRequiredException
     */
    public function testLoginRequiredMethodWithoutLoginData()
    {
        $this->api->dmailInbox();
    }

    public function testPostListingMethod()
    {
        $post_id = null;
        $result = $this->api->postIndex(['limit' => 1]);

        if ($result->isSuccessful()) {
            $results = $result->getResult();

            /** @var \jacklul\E621API\Entity\Post $post */
            $post = $results[0];
            $post_id = $post->getId();
        }

        $this->assertInternalType("int", $post_id);
    }
}
