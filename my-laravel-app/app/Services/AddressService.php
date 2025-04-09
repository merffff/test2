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

    public function saveAddress(User $user, AddressDTO $addressDTO): Address
    {
        $this->checkAddressLimit($user);

        $existingAddress = $this->findExistingAddress($user, $addressDTO->fullAddress);

        if ($existingAddress) {
            return $existingAddress;
        }


        $address = $user->addresses()->create($addressDTO->toArray());

        return $address;
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
