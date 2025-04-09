<?php


namespace App\Http\Controllers;

use App\Models\Address;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class AddressController extends Controller
{
    const MAX_ADDRESSES = 10;
    const CACHE_TTL = 60 * 60; // 1 hour cache

    public function search(Request $request)
    {
        $request->validate([
            'query' => 'required|string|min:3',
        ]);

        $query = $request->input('query');

        // Try to get from cache first
        $cacheKey = 'dadata_' . md5($query);

        if (Cache::has($cacheKey)) {
            return response()->json(Cache::get($cacheKey));
        }

        // Make request to DaData API
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Authorization' => 'Token ' . env('DADATA_API_KEY'),
        ])->post('https://suggestions.dadata.ru/suggestions/api/4_1/rs/suggest/address', [
            'query' => $query,
        ]);

        if ($response->successful()) {
            $data = $response->json();

            // Cache the response
            Cache::put($cacheKey, $data, self::CACHE_TTL);

            return response()->json($data);
        }

        return response()->json(['error' => 'Failed to fetch addresses'], 500);
    }

    public function store(Request $request)
    {
        $request->validate([
            'full_address' => 'required|string',
            'address_data' => 'required|array',
        ]);

        $user = $request->user();

        // Check if user already has 10 addresses
        if ($user->addresses()->count() >= self::MAX_ADDRESSES) {
            return response()->json([
                'message' => 'You can save maximum 10 addresses',
            ], 400);
        }

        // Check if address already exists for this user
        $existingAddress = $user->addresses()
            ->where('full_address', $request->full_address)
            ->first();

        if ($existingAddress) {
            return response()->json([
                'message' => 'Address already saved',
                'address' => $existingAddress,
            ], 200);
        }

        // Create new address
        $address = $user->addresses()->create([
            'full_address' => $request->full_address,
            'address_data' => $request->address_data,
        ]);

        return response()->json([
            'message' => 'Address saved successfully',
            'address' => $address,
        ], 201);
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $addresses = $user->addresses()->orderBy('created_at', 'desc')->get();

        return response()->json($addresses);
    }
}
