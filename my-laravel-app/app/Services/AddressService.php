<?php

namespace App\Services;

use App\DTO\AddressDTO;
use App\Models\Address;
use App\Models\User;

class AddressService
{
    public const MAX_ADDRESSES = 10;

    public function getUserAddresses(User $user): array
    {
        return $user->addresses()
            ->orderBy('created_at', 'desc')
            ->get()
            ->toArray();
    }

    public function saveAddress(User $user, AddressDTO $addressDTO): array
    {
        $this->checkAddressLimit($user);

        $existingAddress = $this->findExistingAddress($user, $addressDTO->fullAddress);

        if ($existingAddress) {
            return [
                'message' => 'Address already saved',
                'address' => $existingAddress,
                'status' => 200
            ];
        }

        $address = $user->addresses()->create($addressDTO->toArray());

        return [
            'message' => 'Address saved successfully',
            'address' => $address,
            'status' => 201
        ];
    }

    private function checkAddressLimit(User $user): void
    {
        if ($user->addresses()->count() >= self::MAX_ADDRESSES) {
            throw new \Exception('You can save maximum 10 addresses');
        }
    }

    private function findExistingAddress(User $user, string $fullAddress): ?Address
    {
        return $user->addresses()
            ->where('full_address', $fullAddress)
            ->first();
    }
}
