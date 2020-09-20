<?php

namespace IliaKologrivov\Centrifuge;

use Exception;
use Illuminate\Broadcasting\BroadcastException;
use Illuminate\Broadcasting\Broadcasters\Broadcaster;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Class CentrifugeBroadcaster
 *
 * @package IliaKologrivov\Centrifuge
 */
class CentrifugeBroadcaster extends Broadcaster
{
    /**
     * The Centrifuge SDK instance.
     *
     * @var Centrifuge
     */
    protected $centrifuge;

    /**
     * Create a new broadcaster instance.
     *
     * @param  Centrifuge  $centrifuge
     */
    public function __construct(Centrifuge $centrifuge)
    {
        $this->centrifuge = $centrifuge;
    }

    /**
     * Authenticate the incoming request for a given channel.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function auth($request)
    {
        if ($request->user()) {
            $client = $request->get('client', '');
            $channels = (array)$request->get('channels', []);
            $response = [];

            foreach ($channels as $channel) {
                $channelName = (substr($channel, 0, 1) === '$') ? substr($channel, 1) : $channel;

                if ($this->verifyUserCanAccessChannel($request, $channelName)) {
                    $response[] = [
                        'channel' => $channel,
                        'token' => $this->getCentrifuge()->subscribeToken($client, $channel, '[]'),
                    ];
                }
            }

            return response()->json([
                'channels' => $response
            ]);
        }

        throw new HttpException(401);
    }

    /**
     * Return the valid authentication response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $result
     * @return mixed
     */
    public function validAuthenticationResponse($request, $result)
    {
        return $result;
    }

    /**
     * Broadcast the given event.
     *
     * @param array $channels
     * @param string $event
     * @param array $payload
     *
     * @return void
     *
     * @throws CentrifugeException
     */
    public function broadcast(array $channels, $event, array $payload = [])
    {
        $payload['event'] = $event;
        $socket = null;

        if (array_key_exists('socket', $payload)) {
            $socket = $payload['socket'];
            unset($payload['socket']);
        }

        $response = $this->getCentrifuge()->broadcast($this->formatChannels($channels), $payload, $socket);
        if (is_array($response) && is_null($response['error'] ?? null)) {
            return;
        }

        throw new BroadcastException(
            $response['error'] instanceof Exception ? $response['error']->getMessage() : $response['error']
        );
    }

    /**
     * Get the Centrifuge instance.
     *
     * @return Centrifuge
     */
    public function getCentrifuge(): Centrifuge
    {
        return $this->centrifuge;
    }
}
