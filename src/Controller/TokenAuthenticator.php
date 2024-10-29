<?php

namespace App\Controller;

use Firebase\JWT\Key;
use Firebase\JWT\JWT;
use Firebase\JWT\ExpiredException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class TokenAuthenticator
{
    private $jwtSecret;

    public function __construct()
    {
        $this->jwtSecret = $_ENV['JWT_SECRET'];
    }

    public function validateToken(Request $request): void
    {
        $authorizationHeader = $request->headers->get('Authorization');

        if (!$authorizationHeader || !preg_match('/Bearer\s(\S+)/', $authorizationHeader, $matches)) {
            throw new UnauthorizedHttpException('Bearer token not found');
        }

        $token = $matches[1];

        try {
            // Décode le token
            $payload = JWT::decode($token, new Key($this->jwtSecret, 'HS256'));

            // Vous pouvez maintenant accéder à l'ID de l'utilisateur si nécessaire
            $userId = $payload->user_id; // Assurez-vous de vérifier si user_id existe dans le payload

        } catch (ExpiredException $e) {
            throw new UnauthorizedHttpException('Token expired');
        } catch (\Exception $e) {
            throw new UnauthorizedHttpException('Invalid token');
        }
    }
}


?>