<?php

use App\DTOs\CustomerDTO;

describe('CustomerDTO', function () {
    it('can be created from array', function () {
        $data = [
            'code' => 'CUST-001',
            'name' => 'Test Customer',
            'email' => 'test@example.com',
            'phone' => '1234567890',
        ];

        $dto = CustomerDTO::fromArray($data);

        expect($dto->code)->toBe('CUST-001');
        expect($dto->name)->toBe('Test Customer');
        expect($dto->email)->toBe('test@example.com');
    });

    it('converts to array correctly', function () {
        $dto = new CustomerDTO(
            code: 'CUST-001',
            name: 'Test',
            email: 'test@test.com',
            phone: null,
            tax_number: null,
            billing_address: null,
            shipping_address: null,
            city: null,
            country: null,
            postal_code: null,
            credit_limit: 0,
            status: 1
        );

        $array = $dto->toArray();

        expect($array)->toHaveKey('code');
        expect($array)->toHaveKey('name');
        expect($array['code'])->toBe('CUST-001');
    });

    it('uses default values for optional fields', function () {
        $dto = new CustomerDTO();

        expect($dto->status)->toBe(1);
        expect($dto->credit_limit)->toBe(0.0);
        expect($dto->uuid)->toBeNull();
    });

    it('handles special characters in data', function () {
        $dto = new CustomerDTO(
            name: "O'Reilly's & Sons <script>alert('xss')</script>",
            code: 'CUST-001'
        );

        expect($dto->name)->toContain("O'Reilly");
        expect($dto->name)->toContain('<script>');
    });
});
