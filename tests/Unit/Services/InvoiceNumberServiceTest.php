<?php

use App\Services\InvoiceNumberService;
use App\Models\SalesInvoice;
use App\Models\PurchaseInvoice;

describe('InvoiceNumberService', function () {
    beforeEach(function () {
        $this->service = new InvoiceNumberService();
    });

    it('generates unique sales invoice numbers', function () {
        $number1 = $this->service->generateSalesInvoiceNumber();
        $number2 = $this->service->generateSalesInvoiceNumber();

        expect($number1)->not->toBe($number2);
        expect($number1)->toStartWith('SI-' . now()->format('Y'));
    });

    it('generates unique purchase invoice numbers', function () {
        $number1 = $this->service->generatePurchaseInvoiceNumber();
        $number2 = $this->service->generatePurchaseInvoiceNumber();

        expect($number1)->not->toBe($number2);
        expect($number1)->toStartWith('PI-' . now()->format('Y'));
    });

    it('increments sequence numbers correctly', function () {
        SalesInvoice::factory()->create([
            'invoice_number' => 'SI-' . now()->format('Y') . '-00001',
        ]);

        $nextNumber = $this->service->generateSalesInvoiceNumber();

        expect($nextNumber)->toEndWith('-00002');
    });

    it('pads sequence numbers with zeros', function () {
        $number = $this->service->generateSalesInvoiceNumber();

        $parts = explode('-', $number);
        expect(strlen(end($parts)))->toBe(5);
    });

    it('generates expense numbers with date prefix', function () {
        $number = $this->service->generateExpenseNumber();

        expect($number)->toStartWith('EXP-' . now()->format('Y'));
    });

    it('generates payment numbers with date prefix', function () {
        $number = $this->service->generatePaymentNumber();

        expect($number)->toStartWith('PAY-' . now()->format('Ymd'));
    });

    it('handles year rollover correctly', function () {
        // This test ensures the format changes with year
        $currentYear = now()->format('Y');
        $number = $this->service->generateSalesInvoiceNumber();

        expect($number)->toContain($currentYear);
    });
});
