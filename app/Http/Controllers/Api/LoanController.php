<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Exception;
use Illuminate\Support\Facades\Auth;
use App\Models\Loan;
use App\Http\Resources\LoanResource;
use Carbon\Carbon;
use App\Models\LoanPayments;

class LoanController extends Controller
{
    //
    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function loanApplication(Request $request)
    {
        
        $validator = Validator::make($request->all(), [
            'amount'       => 'required',
            'term' => 'required'
        ]);

        if ($validator->fails()) return sendError('Validation Error.', $validator->errors(), 422);

        try {
            
            $loan   = Loan::create([
                'customer_id'=> Auth::user()->id,
                'amount' => $request->amount,
                'period'=>$request->term,
                'principal_amount'=>$request->amount,
                'balance'=>$request->amount,
            ]);
            
            if($loan->id)
            {
                $date = $loan->created_at;
                $term = $loan->period;
                $amount = $loan->amount;
                $weeklyDay = 7;
                for($i=0; $i < $term; $i++)
                {
                    $loanPayment = $amount / $term ;
                    $loanPayment = LoanPayments::create([
                            'loan_id'=> $loan->id,
                            'loan_payment' => $loanPayment,
                            'payment_date'=>$date->addDays($weeklyDay),
                        ]);
                }
                
            }
            
            $success = new LoanResource($loan);
            $message = 'Yay! A Loan application has been successfully submitted.';
        } catch (Exception $e) {
            $success = [];
            $message = 'Oops! Unable to create a new application.';
        }

        return sendResponse($success, $message);
    }
    
    public function approvedLoan(Request $request)
    {
        if (is_null($request->loan_id)) return sendError('record not found.');
        
        try{
            $update = Loan::where('id',$request->loan_id)->update(['status'=>1,'approved_by'=> Auth::user()->id,'updated_at'=>Carbon::now()]);
            return sendResponse('','Yay! A Loan application has been successfully approved.');
        }catch (Exception $e) {
            $success = [];
            $message = 'Oops! Unable to create a new post.';
        }
    }
    
    public function getLoansDetails()
    {
        if(Auth::user()->user_type == 'admin')
        {
            $loans = loan::all();
        }
        else{
            $loans = loan::where('customer_id',Auth::user()->id)->get(); 
        }
        
        $loanIds = [];
        foreach ($loans as $key=>$loanDetails)
        {
            $loanIds[] = $loanDetails->id;
        }
        
        $loansPayments = LoanPayments::join('loan', 'loan.id', '=', 'loan_payments.loan_id')
                        ->leftJoin('users as approval_user', 'approval_user.id', '=', 'loan.approved_by')
              		->whereIn('loan_payments.loan_id',$loanIds)
                        ->select('loan.principal_amount','loan.balance',
                                'loan.status as approved_status','loan.period as term','loan.created_at as loan_date',
                                'approval_user.name as approved_by',
                                'loan_payments.loan_payment as payment_repay',
                                'loan_payments.status as payment_status',
                                'payment_date')->get();

        
        return sendResponse($loansPayments, 'Loan Payment Details.');
    }
    
    public function loanPaymentByCustomer(Request $request)
    {
       $validator = Validator::make($request->all(), [
            'amount'       => 'required',
            'loan_id' => 'required',
            'loan_payment_id' => 'required',
            'remark'=>'required'
        ]);

        if ($validator->fails()) return sendError('Validation Error.', $validator->errors(), 422);
        
        try{
            $loanExits = LoanPayments::where('id',$request->loan_payment_id)->get();
            if(!empty($loanExits))
            {
                $loanAmount = $loanExits[0]->loan_payment;
                if($request->amount < $loanAmount)
                {
                   return sendError('Please payment required Amount greater or equal to '.$loanAmount);
                }
                else{
                    
                    $updateLoanStatus = LoanPayments::where('id',$request->loan_payment_id)->update(['status'=>1,'amount'=>$request->amount,'updated_at'=>Carbon::now()]);
                    
                    if($request->amount > $loanAmount)
                    {
                        $extraPay = $request->amount - $loanAmount;
                    }
                    $pendingLoans = LoanPayments::where('loan_id',$request->loan_id)->where('status',0)->where('id','!=',$request->loan_payment_id)->get();
                    $extraPayPer = $extraPay / count($pendingLoans);
                    if(!empty($pendingLoans))
                    {
                        foreach ($pendingLoans as $loan)
                        {
                            $totalPendingEMI = $loan->loan_payment - $extraPayPer;
                            $update = LoanPayments::where('id',$loan->id)->update(['loan_payment'=>$totalPendingEMI,'updated_at'=>Carbon::now()]);
                            
                        }
                    }
                    $loansDetails = Loan::where('id',$request->loan_id)->where('status',1)->get();
                    $balance = $loansDetails[0]->balance;
                    $left_balance = $balance - $request->amount;
                    $updateLoanbalance = Loan::where('id',$request->loan_id)->update(['balance'=>$left_balance,'updated_at'=>Carbon::now()]);
                }
            }
            
           return sendResponse('','Yay! A Loan payment request has been successfully updated.');
        }catch (Exception $e) {
            $success = [];
            $message = 'Oops! Unable to loan payment.';
        }
    }
    
    
    
    
    
}
