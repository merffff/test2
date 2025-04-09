<?php


namespace App\DTO;

class AddressDTO
{
    public function __construct(
        public readonly string $fullAddress,
        public readonly array $addressData
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            fullAddress: $data['full_address'],
            addressData: $data['address_data']
        );
    }

    public function toArray(): array
    {
        return [
            'full_address' => $this->fullAddress,
            'address_data' => $this->addressData
        ];
    }
}
