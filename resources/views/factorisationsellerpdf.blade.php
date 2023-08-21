@php
$totalFees = 0;
$upselled = 0;
@endphp

@foreach ($salesSeller as $sale)
@if ($sale->upsell)
$upselled++
@endif
@foreach ($sale->delivery_user->deliveryPlaces as $deliveryPlace)
@if ($deliveryPlace->city->name === $sale->city)
@php
$totalFees += $deliveryPlace->fee;
@endphp
@endif
@endforeach
@endforeach

















<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Document</title>
  <style>
    * {
      font-family: Arial, Helvetica, sans-serif;
    }

    .infos {
      margin-top: -400px;
      margin-left: 450px;
      line-height: 0px;
    }

    ul {
      list-style: none;
    }

    li {
      margin-top: -10px;
    }

    table {
      text-align: center;
      border-collapse: collapse;
    }

    td {
      font-size: small;
      padding: 6px;
    }

    .total {
      margin-top: 10px;
      margin-left: 77.4%;
    }
  </style>
</head>

<body>
  <div class="top">
    <h1 class="logo">VLDO</h1>
    <div class="infos">
      <ul>

        <li>

          <h4>Seller: <strong> {{ucfirst($factorisation->seller->firstname)}} {{ucfirst($factorisation->seller->lastname)}} </strong></h4>
        </li>
        <li>

          <h4>Client: Vldo</h4>
        </li>
        <li>

          <h4>RIB:</h4>
        </li>
        <li>

          <h4>Nb. commandes: {{ $factorisation->commands_number }}</h4>
        </li>
        <li>

          <h4>Date: {{ $factorisation->created_at }}</h4>
        </li>
      </ul>
    </div>
  </div>
  <h3 style="text-align: center; font-size:30px; font-weight:bolder;"> {{$factorisation->factorisation_id}} </h3>
  <table border="1px">
    <tr>
      <th>#</th>
      <th>Code d'envoi</th>
      <th>Date livraison</th>
      <th>Téléphone</th>
      <th>Ville</th>
      <th>Produit</th>
      {{-- <th>Etat</th> --}}
      <th>CRBT</th>
      <th>Frais</th>
    </tr>
    @foreach ($salesSeller as $sale)
    <tr>
      <td>{{ substr($sale->sheets_id, strrpos($sale->sheets_id, '***') + 4) }}</td>
      <td> {{ $sale->cmd }} </td>
      <td> {{ $sale->delivery_date }} </td>
      <td> {{ $sale->phone }} </td>
      <td> {{ $sale->city }} </td>
      <td> x{{ count($sale->items->pluck('product')->pluck('name')) }} </td>
      {{-- <td> {{ $sale->delivery }} </td> --}}
      <td> {{ App\Services\RoadRunnerService::getPrice($sale) }} </td>
      <td> @foreach ($sale->delivery_user->deliveryPlaces as $deliveryPlace)
        @if ($deliveryPlace->city->name === $sale->city)
        {{ $deliveryPlace->fee }}
        @endif
        @endforeach
      </td>
    </tr>
    @endforeach
  </table>
  <table class="total" border="2px">
    <tr>
      <th>Total Brut :</th>
      <td>{{ $factorisation->price }} $</td>
    </tr>
    <tr>
      <th>Frais de <br> Livraison : </th>
      <td>{{ $totalFees }} $</td>
    </tr>
    <tr>
      <th>Frais :</th>
      <td>

        {{ number_format((($factorisation->price + $totalFees) * 0.04) + ($factorisation->commands_number * 8) + ($upselled * 2), 2)}} $
      </td>
    <tr>
      <th>Total Net :</th>
      <td>{{ $factorisation->price - number_format((($factorisation->price + $totalFees) * 0.04) + ($factorisation->commands_number * 8) + ($upselled * 2), 2)}} $</td>
    </tr>
    </tr>
  </table>
</body>

</html>