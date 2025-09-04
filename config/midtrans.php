<?php

return [
  'serverKey' => env('MIDTRANS_SERVER_KEY', ''),
  'clientKey' => env('MIDTRANS_CLIENT_KEY', ''),
  'isProduction' => env('MIDTRANS_IS_PRODUCTION'),
  'isSanitized' => env('MIDTRANS_SANITIZED'),
  'is3ds' => env('MIDTRANS_3DS'),
];
