<?php

namespace App\Http\Controllers;

use App\DTO\AddressDTO;
use App\Http\Requests\SearchAddressRequest;
use App\Http\Requests\StoreAddressRequest;
use App\Services\AddressService;
use App\Services\DaDataService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    public function __construct(
        private readonly DaDataService $daDataService,
        private readonly AddressService $addressService
    ) {
    }

    public function search(SearchAddressRequest $request): JsonResponse
    {
        try {
            $query = $request->input('query');
            $data = $this->daDataService->searchAddress($query);
            return response()->json($data);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function store(StoreAddressRequest $request): JsonResponse
    {
        try {
            $addressDTO = AddressDTO::fromArray($request->validated());
            $result = $this->addressService->saveAddress($request->user(), $addressDTO);

            return response()->json([
                'message' => $result['message'],
                'address' => $result['address'],
            ], $result['status']);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function index(Request $request): JsonResponse
    {
        $addresses = $this->addressService->getUserAddresses($request->user());
        return response()->json($addresses);
    }
}
