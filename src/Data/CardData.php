<?php

declare(strict_types=1);

namespace Shamarkellman\PowerTranz\Data;

class CardData
{
    public function __construct(
        public readonly string $number,
        public readonly string $expiryMonth,
        public readonly string $expiryYear,
        public readonly string $cvv,
        public readonly ?string $name = null,
        public readonly ?string $firstName = null,
        public readonly ?string $lastName = null,
        public readonly ?string $address1 = null,
        public readonly ?string $address2 = null,
        public readonly ?string $city = null,
        public readonly ?string $state = null,
        public readonly ?string $postcode = null,
        public readonly ?string $country = null,
        public readonly ?string $email = null,
        public readonly ?string $phone = null,
    ) {}

    public function toArray(): array
    {
        return [
            'number' => $this->number,
            'expiryMonth' => $this->expiryMonth,
            'expiryYear' => $this->expiryYear,
            'cvv' => $this->cvv,
            'name' => $this->name,
            'firstName' => $this->firstName,
            'lastName' => $this->lastName,
            'Address1' => $this->address1,
            'Address2' => $this->address2,
            'City' => $this->city,
            'State' => $this->state,
            'Postcode' => $this->postcode,
            'Country' => $this->country,
            'email' => $this->email,
            'Phone' => $this->phone,
        ];
    }
}
