<?php
namespace App\Http\Controllers;

use App\DTO\AddressDTO;
use App\Http\Requests\SearchAddressRequest;
use App\Http\Requests\StoreAddressRequest;
use App\Services\AddressService;
use App\Services\DaDataService;
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
        $query = $request->input('query');
        $data = $this->daDataService->searchAddress($query);
        return response()->json($data);
    }

    public function store(StoreAddressRequest $request): JsonResponse
    {
        $addressDTO = AddressDTO::fromArray($request->validated());
        $result = $this->addressService->saveAddress($request->user(), $addressDTO);

        return response()->json([
            'message' => $result['message'],
            'address' => $result['address'],
        ], $result['status']);
    }

    public function index(Request $request): JsonResponse
    {
        $addresses = $this->addressService->getUserAddresses($request->user());
        return response()->json($addresses);
    }
}
