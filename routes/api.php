<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\LoginController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\LoanController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::prefix('v1')->group(function () {
    Route::post('login', [LoginController::class, 'login']);
    Route::post('register', [LoginController::class, 'register']);

 
    Route::middleware('auth:api')->group(function () {
        Route::post('loan', [LoanController::class, 'loanApplication']);
        Route::post('loan_approval/{loan_id}', [LoanController::class, 'approvedLoan']);
        Route::get('get_loans', [LoanController::class, 'getLoansDetails']);
        Route::post('loan_payment', [LoanController::class, 'loanPaymentByCustomer']);
    });
});
