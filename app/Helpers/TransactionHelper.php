<?php

namespace App\Helpers;

use App\Models\Transaction;

class TransactionHelper
{
  public static function generateBookingTrxId()
  {
    $prefix = 'TRX';
    do {
      $randString = $prefix . mt_rand(100000, 999999);
    } while (Transaction::where('booking_trx_id', $randString)->exists());
    return $randString;
  }
}
