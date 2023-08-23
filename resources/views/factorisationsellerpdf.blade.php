@php
function salePrice($sale)
{
return App\Services\RoadRunnerService::getPrice($sale);
}
$totalPrice = 0;


$shippingFees = 0;
$totalCOD = 0;

@endphp

@foreach ($salesSeller as $sale)
  @php
    $totalCOD += (salePrice($sale) * 0.04);
    $totalPrice += salePrice($sale);
  @endphp
  @if ($sale->upsell == "oui")
    @php
      $shippingFees += 10
    @endphp
  @else
    @php
      $shippingFees += 8
    @endphp
  @endif

@endforeach



<!DOCTYPE html>
<html>

<head>
  <title>Larave Generate Invoice PDF - Nicesnippest.com</title>
</head>
<style type="text/css">
  body {
    font-family: 'Roboto Condensed', sans-serif;
  }

  .m-0 {
    margin: 0px;
  }

  .p-0 {
    padding: 0px;
  }

  .pt-5 {
    padding-top: 5px;
  }

  .mt-10 {
    margin-top: 10px;
  }

  .text-center {
    text-align: center !important;
  }

  .w-100 {
    width: 100%;
  }

  .w-50 {
    width: 50%;
  }

  .w-85 {
    width: 85%;
  }

  .w-15 {
    width: 15%;
  }

  .logo img {
    width: 45px;
    height: 45px;
    padding-top: 30px;
  }

  .logo span {
    margin-left: 8px;
    top: 19px;
    position: absolute;
    font-weight: bold;
    font-size: 25px;
  }

  .gray-color {
    color: #5D5D5D;
  }

  .text-bold {
    font-weight: bold;
  }

  .border {
    border: 1px solid black;
  }

  table tr,
  th,
  td {
    border: 1px solid #d2d2d2;
    border-collapse: collapse;
    padding: 7px 8px;
  }

  table tr th {
    background: #F4F4F4;
    font-size: 12px;
    white-space: nowrap;
  }

  table tr td {
    font-size: 13px;
  }

  table {
    border-collapse: collapse;
  }

  .box-text p {
    line-height: 10px;
  }

  .float-left {
    float: left;
  }

  .total-part {
    font-size: 16px;
    line-height: 12px;
  }

  .total-right p {
    padding-right: 20px;
  }
</style>

<body>
  <table style="width: 100%;">
    <tr>
      <td style="text-align: left; border:1px solid white;">
        <h1 class="m-0 p-0 gray-color">INVOICE <br /> <span style="font-size:medium; color:gray;">#</span><span style="font-size:medium; color:#f97316;"> {{$factorisation->factorisation_id}} </span></h1>
      </td>
      <td style="text-align: right; border:1px solid white;">
        <h1>VLDO</h1>
      </td>
    </tr>
  </table>



  <div class="add-detail mt-10">
    <h4>Invoice To: </h4>
    <div class="w-50 float-left mt-0" style="border-left:3px solid #f97316; margin-right:10px;">
      <div style="margin-left:5px;">

        <p class="m-0 pt-5 text-bold w-100" style="font-size:small;">Seller Fullname: <span class="gray-color">{{ucfirst($factorisation->seller->firstname)}} {{ucfirst($factorisation->seller->lastname)}} </span></p>
        <p class="m-0 pt-5 text-bold w-100" style="font-size:small;">Warehouse: <span class="gray-color">Lebnon</span></p>
        <p class="m-0 pt-5 text-bold w-100" style="font-size:small;">Date Payment: <span class="gray-color">{{ $factorisation->close_at }}</span></p>
        <p class="m-0 pt-5 text-bold w-100" style="font-size:small;">NB Orders: <span class="gray-color">{{ $factorisation->commands_number }}</span></p>
      </div>
    </div>

    <div style="clear: both;"></div>
  </div>

  <div class="table-section bill-tbl w-100 mt-10">
    <table class="table w-100 mt-10">
      <tr>
        <th class="w-100" style="font-size:medium;" colspan="7">Orders</th>
      </tr>
      <tr>
        <th class="w-50" style="color:#4b5563;">Order ID</th>
        <th class="w-50" style="color:#4b5563;">Product Name</th>
        <th class="w-50" style="color:#4b5563;">Quantity</th>
        <th class="w-50" style="color:#4b5563;">CRBT</th>
        <th class="w-50" style="color:#4b5563;">Shipping Fees</th>
        <th class="w-50" style="color:#4b5563;">COD Fees</th>
        <th class="w-50" style="color:#4b5563;">Payment</th>
      </tr>
      @foreach ($salesSeller as $sale)
      <tr align="center">
        <td>{{ substr($sale->sheets_id, strrpos($sale->sheets_id, '***') + 4) }}</td>
        <td>{{ $sale->product_name }}</td>
        <td>{{ implode(", ", $sale->items->pluck("quantity")->toArray()) }}</td>
        <td>{{ salePrice($sale) }}$</td>
        <td>{{ $sale->upsell == "oui" ? 10 : 8 }}$</td>
        <td>{{ number_format(salePrice($sale) * 0.04,2) }}$</td>
        <td>{{ number_format(salePrice($sale) - (($sale->upsell == "oui" ? 10 : 8) + (salePrice($sale) * 0.04)),)}}$</td>
      </tr>
      @endforeach
      <tr>
        <td colspan="7">
          <div class="total-part">
            <table class="table w-20 mt-10" style="margin-left:auto">
              <tr>
                <th class="w-50">Orders Fees</th>
                <td>{{$totalPrice}}$</td>
              </tr>
              <tr>
                <th class="w-50">Total Fees</th>
                <td>{{number_format($shippingFees + $totalCOD)}}$</td>
              </tr>
              @php
                  $otherFees = 0;                
              @endphp
              @foreach ($factorisation->fees as $fee)
                @php
                    $otherFees += $fee->feeprice;                
                @endphp
                <tr>
                    <th class="w-50">{{$fee->feename}}</th>
                    <td>{{$fee->feeprice}}$</td>
                </tr>
              @endforeach
              <tr>
                <th class="w-50">Net Payment</th>
                <td>{{number_format($totalPrice - ($shippingFees + $totalCOD + $otherFees))}}$</td>
              </tr>
            </table>
            <div style="clear: both;"></div>
          </div>
        </td>
      </tr>
    </table>
  </div>

</html>