<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Laravel\Passport\Token;
use Laravel\Passport\HasApiTokens;

class CheckToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */

    use HasApiTokens;

    public function handle(Request $request, Closure $next)
    {
        try {
            // Dynamically get the base URL of your site
            $baseUrl = url('/'); // This returns the full base URL (e.g., 'https://your-domain.com')

            // Define different endpoints dynamically
            $passportEndpoint = $baseUrl . '/api/user'; // Example Passport endpoint
            $anotherEndpoint = $baseUrl . '/api/another-endpoint'; // Another example endpoint

            // Send the request to the Passport endpoint
            $client = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'Authorization' => $request->header('Authorization')
            ]);

            // Send GET request to the passport endpoint
            $response = $client->get($passportEndpoint);

            if ($response->status() === 200) {
                $body = $response->object();
                // Optionally set the authenticated user globally
                auth()->loginUsingId($body->id);

                return $next($request);
            }
        } catch (RequestException $exception) {
            // Handle the exception (e.g., failed request)
        }

        return abort(401, 'You are not authenticated to this service');
    }

    // $token = $request->bearerToken(); // Get the token from the Authorization header

    // $accessTokenId = $this->tokens()->first()->id;

    // $tokenId = explode('|', $token)[0]; // Extract the token ID (first part of the token)

    // $tokenModel = Token::find($tokenId);

    // if (!$tokenModel || $tokenModel->revoked) {
    //     return response()->json(['status' => false, 'message' => 'Token is invalid'], 401);
    // }


    // return $next($request);
    // }
}
