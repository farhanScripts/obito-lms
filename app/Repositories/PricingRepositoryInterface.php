<?php

namespace App\Repositories;

use App\Models\Pricing;

interface PricingRepositoryInterface
{
  public function getAll();
  public function findById(int $id);
}
