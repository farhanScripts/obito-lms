<?php

namespace App\Services;

use App\Models\Pricing;
use App\Repositories\PricingRepository;

class PricingService
{
  protected $pricingRepositoryInterface;

  public function __construct(PricingRepository $pricingrepositoryinterface)
  {
    $this->pricingRepositoryInterface = $pricingrepositoryinterface;
  }
  public function getAllPackages()
  {
    return $this->pricingRepositoryInterface->getAll();
  }
}
