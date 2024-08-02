<?php

namespace App\Guards;

use Illuminate\Auth\GuardHelpers;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Http\Request;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JwtGuard
{
    use GuardHelpers;

    protected $request;
    protected $provider;
    protected $key;

    public function __construct(UserProvider $provider, Request $request, $key)
    {
        $this->provider = $provider;
        $this->request = $request;
        $this->key = $key;
    }

    public function user()
    {
        if ($this->check()) {
            return $this->user;
        }

        return null;
    }

    public function check()
    {
        if ($this->user) {
            return true;
        }

        $token = $this->request->bearerToken();

        if ($token) {
            try {
                $decoded = JWT::decode($token, new Key($this->key, 'HS256'));
                $this->user = $this->provider->retrieveById($decoded->sub);
                return true;
            } catch (\Exception $e) {
                return false;
            }
        }

        return false;
    }

    // Implement other required methods here...
}
