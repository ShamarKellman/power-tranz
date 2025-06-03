<?php

declare(strict_types=1);

namespace Shamarkellman\PowerTranz\Data;

class CardData
{
    public function __construct(
        public readonly ?string $number = null,
        public readonly ?string $expiryMonth = null,
        public readonly ?string $expiryYear = null,
        public readonly ?string $cvv = null,
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

    /**
     * @return array<string, string|null>
     */
    public function toArray(): array
    {
        return [
            'Number' => $this->number,
            'ExpiryMonth' => $this->expiryMonth,
            'ExpiryYear' => $this->expiryYear,
            'Cvv' => $this->cvv,
            'Name' => $this->name,
            'FirstName' => $this->firstName,
            'LastName' => $this->lastName,
            'Address1' => $this->address1,
            'Address2' => $this->address2,
            'City' => $this->city,
            'State' => $this->state,
            'Postcode' => $this->postcode,
            'Country' => $this->country,
            'Email' => $this->email,
            'Phone' => $this->phone,
        ];
    }
}
