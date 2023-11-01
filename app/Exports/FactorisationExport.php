<?php

namespace App\Exports;

use App\Models\City;
use App\Models\Order;
use Illuminate\Support\Arr;
use App\Helpers\OrderHelper;
use App\Models\DeliveryPlace;
use App\Models\Factorisation;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\BeforeExport;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;
use Maatwebsite\Excel\Events\BeforeWriting;

class FactorisationExport implements
    FromCollection,
    WithMapping,
    WithHeadings,
    WithEvents,
    ShouldAutoSize
{

    use RegistersEventListeners;

    protected $factorisation;

    public function __construct($factorisation)
    {
        $this->factorisation = $factorisation;
    }

    public function headings(): array
    {
        return [
            'Order Id',
            'Client',
            'Product Name',
            'Quantity',
            'Price',
            'Shipping Fees',
            'COD Fees',
            'Payment',
            'Created At',
            'Delivered At'
        ];
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $orders = Order::with('items', 'items.product')->where('factorisation_id', $this->factorisation->id)->orWhere('seller_factorisation_id',$this->factorisation->id)->get();
        return $orders;
    }

    public function map($order): array {

        // $shippingFee = DeliveryPlace::where('delivery_id', $order->affectation)
        // ->where('city_id', City::where('name', $order->city)->first()->id ?? 0)->first()->fee ?? 0;

        $shippingFee = $order->upsell == 'oui' ? 10 : 8;

        $price = OrderHelper::getPrice($order);

        $product_info = $order->items->map(function ($item) {
            $name = $item->product->name;
            $quantity = $item->quantity;
            return compact('name', 'quantity');
        });

        $product_names = $product_info->map(fn ($info) => $info['name'])->values()->toArray();
        $quantities = $product_info->map(fn ($info) => $info['quantity'])->values()->toArray();
        // $products_name = 'x';
        $codFee = 4 ;
        $payment = $price - ($order->upsell == 'oui' ? 10 : 8) - $codFee;

        return [
            $order->id,
            $order->fullname,
            implode(', ', $product_names),
            implode(', ', $quantities),
            $price,
            $shippingFee,
            $codFee,
            $payment,
            $order->created_at,
            $order->delivery_date
        ];
    }


    public function registerEvents(): array
    {
        return [
            // BeforeExport::class => function (BeforeExport $event) {
            //     // $event->writer->getSheetByIndex()
            // },
            // // Handle by a closure.
            // BeforeWriting::class => function(BeforeWriting $event) {
            //     $worksheet = $event->writer->getActiveSheet();

            //     // Calcul total price
            //     $lastRow = $worksheet->getHighestRow('D');
            //     // Define the formula to calculate
            //     $formula = '=SOMME(D2:D' . $lastRow . ')';
            //     // Calculate the formula
            //     $worksheet->setCellValue('D' . ($lastRow + 1), $formula);


            //     // Calcul total shipping
            //     $lastRow = $worksheet->getHighestRow('E');
            //     // Define the formula to calculate
            //     $formula = '=SOMME(E2:E' . $lastRow . ')';
            //     // Calculate the formula
            //     $worksheet->setCellValue('E' . ($lastRow + 1), $formula);


            //     // Calcul total to pay
            //     $lastRow = $worksheet->getHighestRow('F');
            //     // Define the formula to calculate
            //     $formula = '=SOMME(F2:F' . $lastRow . ')';
            //     // Calculate the formula
            //     $worksheet->setCellValue('F' . ($lastRow + 1), $formula);

            // },

        ];
    }



}
