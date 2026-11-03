<?php

namespace App\Models;

use Database\Factories\InfillFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['code', 'description', 'unique_code'])]
#[UseFactory(InfillFactory::class)]
class Infill extends Model
{
    /** @use HasFactory<InfillFactory> */
    use HasFactory;
}
