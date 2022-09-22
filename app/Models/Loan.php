<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\LoanPayments;

class Loan extends Model
{
    use HasFactory;
    
    protected $table = 'loan';

    protected $fillable = ['customer_id', 'amount','period','principal_amount','balance','interest_rate','approved_by','status'];

    
    public function loanPayments(){
     return $this->hasMany("App\LoanPayments");
    }
}
