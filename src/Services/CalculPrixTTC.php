<?php
namespace App\Services;

class CalculPrixTTC
{
    private $TVA;
    public function __construct($TVA)
    {
        $this->TVA=$TVA;
    }
    public function calculerPrixTTC($prix)
    {
        return $prix + $prix *$this->TVA;
    }
}