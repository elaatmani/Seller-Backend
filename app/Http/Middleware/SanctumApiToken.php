<?php
namespace App\Http\Middleware;

use Closure;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\Sanctum;
use Laravel\Sanctum\PersonalAccessToken;

class SanctumApiToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $ability)
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }


        $accessToken = PersonalAccessToken::findToken($token);

        if (!$accessToken) {
            return response()->json(['error' => 'Invalid token'], 401);
        }

        if (!!$accessToken->expires_at && $accessToken->expires_at->isPast()) {
            return response()->json(['error' => 'Token expired'], 401);
        }

        $ability = implode(':', explode('-', $ability));
        $abilities = collect($accessToken->abilities);

        if (!$abilities->contains($ability)) {
            return response()->json(['error' => 'Token doesn\'t have the required abilities'], 401);
        }

        Sanctum::actingAs($accessToken->tokenable, $accessToken->abilities);

        $accessToken->last_used_at = now();
        $accessToken->save();

        return $next($request);
    }
}
