<?php

namespace IliaKologrivov\Centrifuge;

use Carbon\Carbon;
use Exception;
use GuzzleHttp\Client;
use Firebase\JWT\JWT;

/**
 * Class Centrifuge
 *
 * @package IliaKologrivov\Centrifuge
 */
class Centrifuge
{
    const API_PATH = '/api';

    /**
     * @var Client
     */
    protected $httpClient;

    /**
     * @var array
     */
    protected $config = [
        'url'              => 'http://localhost:8000',
        'api_key'          => null,
        'secret'           => null,
        'ssl_key'          => null,
        'verify'           => true,
        'broadcast_error'  => true,
        'connection_url'  => 'ws://localhost:8000/connection/websocket',
    ];

    /**
     * Create a new Centrifuge instance.
     *
     * @param array $config
     * @param Client $httpClient
     */
    public function __construct(array $config, Client $httpClient)
    {
        $this->httpClient = $httpClient;
        $this->config = array_intersect_key($config, array_flip(array_keys($this->config)));
    }

    /**
     * Get connection url for WS
     *
     * @return string
     */
    public function getWsConnectUrl(): string
    {
        return $this->config['connection_url'];
    }

    /**
     * Send message into channel.
     *
     * @param string $channel
     * @param array $data
     * @param string $client
     *
     * @return mixed
     *
     * @throws CentrifugeException
     */
    public function publish($channel, array $data, $client = null)
    {
        $params = [
            'channel' => $channel,
            'data' => $data
        ];

        if (! is_null($client)) {
            $params['client'] = $client;
        }

        return $this->send('publish', $params);
    }

    /**
     * Send message into multiple channel.
     *
     * @param array $channels
     * @param array $data
     * @param string $client
     *
     * @return mixed
     *
     * @throws CentrifugeException
     */
    public function broadcast(array $channels, array $data, $client = null)
    {
        $params = [
            'channels' => $channels,
            'data' => $data
        ];

        if (! is_null($client)) {
            $params['client'] = $client;
        }

        try {
            return $this->send('broadcast', $params);
        } catch (CentrifugeException $exception) {
            if ($this->config['broadcast_error']) {
                throw $exception;
            }

            return [];
        }
    }

    /**
     * Get channel presence information (all clients currently subscribed on this channel).
     *
     * @param string $channel
     *
     * @return mixed
     *
     * @throws CentrifugeException
     */
    public function presence($channel)
    {
        return $this->send('presence', [
            'channel' => $channel
        ]);
    }

    /**
     * Get short channel presence information.
     * @param $channel
     *
     * @return mixed
     *
     * @throws CentrifugeException
     */
    public function presenceStats($channel)
    {
        return $this->send('presence_stats', [
            'channel' => $channel
        ]);
    }

    /**
     * Get channel history information (list of last messages sent into channel).
     *
     * @param string $channel
     *
     * @return mixed
     *
     * @throws CentrifugeException
     */
    public function history($channel)
    {
        return $this->send('history', [
            'channel' => $channel
        ]);
    }

    /**
     * Unsubscribe user from channel.
     *
     * @param string $user_id
     * @param string $channel
     *
     * @return mixed
     *
     * @throws CentrifugeException
     */
    public function unsubscribe($user_id, $channel = null)
    {
        $params = [
            'user' => (string) $user_id
        ];

        if (! is_null($channel)) {
            $params['channel'] = $channel;
        }

        return $this->send('unsubscribe', $params);
    }

    /**
     * Disconnect user by its ID.
     *
     * @param string $user_id
     *
     * @return mixed
     *
     * @throws CentrifugeException
     */
    public function disconnect($user_id)
    {
        return $this->send('disconnect', [
            'user' => (string) $user_id
        ]);
    }

    /**
     * Get channels information (list of currently active channels).
     *
     * @return mixed
     *
     * @throws CentrifugeException
     */
    public function channels()
    {
        return $this->send('channels');
    }

    /**
     * Get stats information about running server nodes.
     *
     * @return mixed
     *
     * @throws CentrifugeException
     */
    public function info()
    {
        return $this->send('info');
    }

    /**
     * Get subscribe token.
     * @param $client
     * @param $chanel
     * @param $info
     *
     * @return string
     */
    public function subscribeToken($client, $chanel, $info)
    {
        $payload = [
            'channel' => (string) $chanel,
            'client' => (string) $client,
            'info' => $info
        ];

        return JWT::encode($payload, $this->getSecret());
    }

    /**
     * Generate token.
     *
     * @param string $user
     * @param int|null $exp
     *
     * @return string
     */
    public function connToken(string $user, int $exp = null)
    {
        $payload = [
            'sub' => $user,
        ];

        if (! empty($exp)) {
            $payload['exp'] = Carbon::now()->timestamp + $exp;
        }

        return JWT::encode($payload, $this->getSecret());
    }

    /**
     * Get secret key.
     *
     * @return string
     */
    protected function getSecret()
    {
        return $this->config['secret'];
    }

    /**
     * Get centrifugo api key.
     *
     * @return string
     */
    protected function getApiKey()
    {
        return $this->config['api_key'];
    }

    /**
     * Send message to centrifuge server.
     *
     * @param string $method
     * @param array $params
     * @return mixed
     *
     * @throws CentrifugeException
     */
    protected function send($method, array $params = [])
    {
        try {
            $url = parse_url($this->prepareUrl());

            $config = [
                'headers' => [
                    'Content-type' => 'application/json',
                    'Authorization' => 'apikey ' . $this->getApiKey(),
                ],
                'body' => json_encode([
                    'method' => $method,
                    'params' => $params
                ]),
            ];

            if ($url['scheme'] == 'https') {
                $config['verify'] = $this->config['verify'] ?? false;

                if (! empty($this->config['ssl_key'])) {
                    $config['ssl_key'] = $this->config['ssl_key'];
                }
            }

            $response = $this->httpClient->post($this->prepareUrl(), $config);

            return json_decode((string) $response->getBody()->getContents(), true);
        } catch (Exception $e) {
            throw new CentrifugeException($e->getMessage(), $method, $params);
        }
    }

    /**
     * Prepare URL to send the http request.
     *
     * @return string
     */
    protected function prepareUrl()
    {
        $address = rtrim($this->config['url'], '/');

        if (substr_compare($address, static::API_PATH, -strlen(static::API_PATH)) !== 0) {
            $address .= static::API_PATH;
        }

        return $address;
    }
}
