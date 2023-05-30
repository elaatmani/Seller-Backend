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

    table{
      text-align: center;
      border-collapse: collapse;
    }

    td{
      font-size: small;
      padding: 6px;
    }
    .total{
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

          <h4>Livreur: <strong> {{ucfirst($factorisation->delivery->firstname)}} {{ucfirst($factorisation->delivery->lastname)}} </strong></h4>
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
    @foreach ($sales as $sale)
    <tr>
      <td> {{ $sale->id }} </td>
      <td> {{ $sale->cmd }} </td>
      <td> {{ $sale->delivery_date }} </td>
      <td> {{ $sale->phone }} </td>
      <td> {{ $sale->city }} </td>
      <td> x{{ count($sale->items->pluck('product')->pluck('name')) }} </td>
      {{-- <td> {{ $sale->delivery }} </td> --}}
      <td> {{ $sale->price }} </td>
      <td> @foreach ($factorisation->delivery->deliveryPlaces as $deliveryPlace)
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
      <td>{{ $factorisation->price }}</td>
    </tr>
    <tr>
      <th>Frais :</th>
      <td> @php
        $totalFees = 0;
        @endphp

        @foreach ($sales as $sale)
        @foreach ($factorisation->delivery->deliveryPlaces as $deliveryPlace)
        @if ($deliveryPlace->city->name === $sale->city)
        @php
        $totalFees += $deliveryPlace->fee;
        @endphp
        @endif
        @endforeach
        @endforeach

        {{ $totalFees }} DH
      </td>
      <tr>
        <th>Total Net :</th>
        <td>{{ $factorisation->price - $totalFees}} DH</td>
      </tr>
    </tr>
  </table>
</body>

</html>
