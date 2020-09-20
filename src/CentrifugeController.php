<?php

namespace IliaKologrivov\Centrifuge;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Routing\Controller;

/**
 * Class CentrifugeController
 *
 * @package IliaKologrivov\Centrifuge
 */
class CentrifugeController extends Controller
{
    /**
     * @param Request $request
     *
     * @return mixed
     */
    public function subscribe(Request $request)
    {
        return Broadcast::auth($request);
    }

    /**
     * @param Request $request
     * @param Centrifuge $centrifuge
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh(Request $request, Centrifuge $centrifuge)
    {
        return response()->json([
            'token' => $centrifuge->connToken($request->user()->getKey())
        ]);
    }
}
