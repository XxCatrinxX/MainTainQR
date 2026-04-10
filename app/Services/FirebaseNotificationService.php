<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class FirebaseNotificationService
{
    private $credentials;
    private $projectId;

    public function __construct()
    {
        $credentialsPath = env('FIREBASE_CREDENTIALS_PATH');
        
        if ($credentialsPath && file_exists($credentialsPath)) {
            try {
                $this->credentials = json_decode(file_get_contents($credentialsPath), true);
                $this->projectId = $this->credentials['project_id'] ?? null;
            } catch (\Exception $e) {
                Log::error('Firebase: Error leyendo credenciales', ['error' => $e->getMessage()]);
                $this->credentials = null;
            }
        }
    }

    /**
     * Envía notificación de forma silenciosa (no bloquea si falla)
     */
    public function enviar(string $fcmToken, string $titulo, string $cuerpo, array $datos = []): void
    {
        if (!$fcmToken || !$this->credentials || !$this->projectId) {
            return;
        }

        try {
            // Obtener access token
            $accessToken = $this->obtenerAccessToken();
            if (!$accessToken) {
                return;
            }

            // Asegurar que todos los datos sean strings
            $datosSerializados = array_map(fn($value) => (string)$value, $datos);

            // Construir mensaje FCM v1
            $mensaje = [
                'message' => [
                    'token' => $fcmToken,
                    'notification' => [
                        'title' => $titulo,
                        'body' => $cuerpo,
                    ],
                    'data' => $datosSerializados,
                    'android' => [
                        'priority' => 'high',
                    ],
                ],
            ];

            // Enviar a FCM
            $response = Http::withToken($accessToken)->post(
                "https://fcm.googleapis.com/v1/projects/{$this->projectId}/messages:send",
                $mensaje
            );

            if ($response->successful()) {
                Log::info('FCM: Notificación enviada', [
                    'titulo' => $titulo,
                    'orden_id' => $datosSerializados['orden_id'] ?? null,
                ]);
            } else {
                Log::error('FCM: Error en respuesta', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('FCM: Excepción al enviar', [
                'error' => $e->getMessage(),
                'titulo' => $titulo,
            ]);
        }
    }

    /**
     * Obtiene un access token OAuth2 válido para FCM
     */
    private function obtenerAccessToken(): ?string
    {
        // Cachear el token durante 55 minutos (expira en 60)
        $cacheKey = 'firebase_access_token_' . md5($this->projectId);
        
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        try {
            // Crear JWT
            $jwt = $this->generarJwt();

            // Intercambiar JWT por access token
            $response = Http::post('https://oauth2.googleapis.com/token', [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $jwt,
            ]);

            if ($response->successful()) {
                $token = $response->json()['access_token'];
                Cache::put($cacheKey, $token, 3300); // 55 minutos
                return $token;
            } else {
                Log::error('FCM: Error obteniendo access token', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return null;
            }
        } catch (\Exception $e) {
            Log::error('FCM: Excepción obteniendo token', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Genera un JWT firmado para el flujo de credenciales de servicio
     */
    private function generarJwt(): string
    {
        $now = time();
        $expiry = $now + 3600; // 1 hora

        $header = [
            'alg' => 'RS256',
            'typ' => 'JWT',
        ];

        $claim = [
            'iss' => $this->credentials['client_email'],
            'scope' => 'https://www.googleapis.com/auth/cloud-platform',
            'aud' => 'https://oauth2.googleapis.com/token',
            'exp' => $expiry,
            'iat' => $now,
        ];

        $header64 = rtrim(strtr(base64_encode(json_encode($header)), '+/', '-_'), '=');
        $claim64 = rtrim(strtr(base64_encode(json_encode($claim)), '+/', '-_'), '=');
        $message = "{$header64}.{$claim64}";

        // Firmar con la clave privada
        $privateKey = $this->credentials['private_key'];
        openssl_sign($message, $signature, $privateKey, 'RSA-SHA256');
        $signature64 = rtrim(strtr(base64_encode($signature), '+/', '-_'), '=');

        return "{$message}.{$signature64}";
    }
}
