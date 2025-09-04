<?php

namespace App\Repositories;

use App\Models\Pricing;

class PricingRepository implements PricingRepositoryInterface
{
  public function getAll()
  {
    return Pricing::all();
  }

  public function findById(int $id)
  {
    return Pricing::find($id);
  }
}
