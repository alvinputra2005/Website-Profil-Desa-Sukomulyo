<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class RegionController extends Controller
{
    public function provinces(): JsonResponse
    {
        return $this->fetch('provinces');
    }

    public function regencies(string $province): JsonResponse
    {
        return $this->fetch("regencies/{$province}");
    }

    public function districts(string $regency): JsonResponse
    {
        return $this->fetch("districts/{$regency}");
    }

    public function villages(string $district): JsonResponse
    {
        return $this->fetch("villages/{$district}");
    }

    private function fetch(string $endpoint): JsonResponse
    {
        $this->authorize('manage-content');

        try {
            $payload = Cache::remember(
                'wilayah-id:'.sha1($endpoint),
                now()->addDay(),
                function () use ($endpoint): array {
                    $baseUrl = rtrim((string) config('services.wilayah.url'), '/');
                    $response = Http::acceptJson()
                        ->connectTimeout(5)
                        ->timeout(10)
                        ->retry(2, 200)
                        ->get("{$baseUrl}/api/{$endpoint}.json");

                    $response->throw();
                    $payload = $response->json();

                    if (! is_array($payload) || ! is_array($payload['data'] ?? null)) {
                        throw new RequestException($response);
                    }

                    return $payload;
                }
            );

            return response()->json($payload);
        } catch (Throwable $exception) {
            Log::warning('Gagal mengambil data wilayah Indonesia.', [
                'endpoint' => $endpoint,
                'exception' => $exception->getMessage(),
            ]);

            return response()->json([
                'data' => [],
                'message' => 'Data wilayah sedang tidak dapat dimuat. Silakan coba lagi.',
            ], 503);
        }
    }
}
