<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class DaDataService
{
    public const CACHE_TTL = 3600; // 1 hour cache
    private const API_URL = 'https://suggestions.dadata.ru/suggestions/api/4_1/rs/suggest/address';
    private string $apiKey;

    public function __construct()
    {
        $this->apiKey = config('services.dadata.api_key');
    }


    public function searchAddress(string $query): array
    {
        $cacheKey = $this->generateCacheKey($query);

        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $response = $this->sendApiRequest($query);

        if ($response->successful()) {
            $data = $response->json();
            Cache::put($cacheKey, $data, self::CACHE_TTL);
            return $data;
        }

        throw new \Exception('Failed to fetch addresses from DaData API');
    }

    private function generateCacheKey(string $query): string
    {
        return 'dadata_' . md5($query);
    }

    private function sendApiRequest(string $query)
    {
        return Http::withHeaders([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Authorization' => 'Token ' .$this->apiKey,
        ])->post(self::API_URL, [
            'query' => $query,
        ]);
    }
}
