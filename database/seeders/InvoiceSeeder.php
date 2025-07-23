<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Customer;

class InvoiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $customers = Customer::all();

        if ($customers->count() > 0) {
            // إنشاء فواتير تجريبية
            for ($i = 1; $i <= 5; $i++) {
                $customer = $customers->random();

                $invoice = Invoice::create([
                    'invoice_number' => 'INV-' . str_pad($i, 6, '0', STR_PAD_LEFT),
                    'customer_id' => $customer->id,
                    'invoice_date' => now()->subDays(rand(1, 30)),
                    'due_date' => now()->addDays(rand(15, 45)),
                    'subtotal' => 1000 + ($i * 500),
                    'tax_amount' => (1000 + ($i * 500)) * 0.15,
                    'discount_amount' => rand(0, 200),
                    'total_amount' => (1000 + ($i * 500)) + ((1000 + ($i * 500)) * 0.15) - rand(0, 200),
                    'paid_amount' => rand(0, 1) ? 0 : (1000 + ($i * 500)) + ((1000 + ($i * 500)) * 0.15) - rand(0, 200),
                    'status' => ['draft', 'sent', 'paid', 'overdue'][rand(0, 3)],
                    'notes' => 'فاتورة تجريبية رقم ' . $i
                ]);

                // إضافة عناصر للفاتورة
                $items = [
                    ['description' => 'خدمات تطوير موقع إلكتروني', 'quantity' => 1, 'unit_price' => 800],
                    ['description' => 'صيانة شهرية', 'quantity' => 12, 'unit_price' => 50],
                    ['description' => 'تدريب المستخدمين', 'quantity' => 4, 'unit_price' => 100],
                    ['description' => 'استضافة سنوية', 'quantity' => 1, 'unit_price' => 300],
                ];

                $numItems = rand(1, 3);
                $selectedItems = array_slice($items, 0, $numItems);

                foreach ($selectedItems as $item) {
                    InvoiceItem::create([
                        'invoice_id' => $invoice->id,
                        'description' => $item['description'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'total_price' => $item['quantity'] * $item['unit_price']
                    ]);
                }
            }
        }
    }
}
