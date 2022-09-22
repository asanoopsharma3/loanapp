<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Loan;

class LoanPayments extends Model
{
    use HasFactory;

    protected $fillable = ['loan_id', 'loan_payment','amount','payment_date'];
    
    public function loan(){
        return $this->hasOne("App\Loan", 'loan_id', 'id');
    }
}
