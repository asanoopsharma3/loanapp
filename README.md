## Laravel 8 REST API with Passport Authentication

#### Step 1: Install Laravel

``laravel new project-name``  
or  
``composer create-project --prefer-dist laravel/laravel project-name``

#### Step 2: Database Configuration

Create a database and configure the env file.  

#### Step 3: Passport Installation

To get started, install Passport via the Composer package manager:

``composer require laravel/passport``

The Passport service provider registers its own database migration directory with the framework, so you should migrate your database after installing the package. The Passport migrations will create the tables your application needs to store clients and access tokens:

``php artisan migrate``

Next, you should run the `passport:install` command. This command will create the encryption keys needed to generate secure access tokens. In addition, the command will create "personal access" and "password grant" clients which will be used to generate access tokens:

``php artisan passport:install``

#### Step 4: Passport Configuration

After running the `passport:install` command, add the `Laravel\Passport\HasApiTokens` trait to your `App\Models\User` model. This trait will provide a few helper methods to your model which allow you to inspect the authenticated user's token and scopes:

```
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
}
```

Next, you should call the `Passport::routes` method within the `boot` method of your `AuthServiceProvider`. This method will register the routes necessary to issue access tokens and revoke access tokens, clients, and personal access tokens:

```
<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Laravel\Passport\Passport;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Passport::routes();
    }
}
```

Finally, in your `config/auth.php` configuration file, you should set the `driver` option of the `api` authentication guard to `passport`. This will instruct your application to use Passport's `TokenGuard` when authenticating incoming API requests:

```
'guards' => [
    'web' => [
        'driver' => 'session',
        'provider' => 'users',
    ],

    'api' => [
        'driver' => 'passport',
        'provider' => 'users',
    ],
],
```

#### Step 5: Add Loan Table and Model

next, we require to create migration & model for Loan table using Laravel 8 php artisan command, so first fire bellow command:  
``php artisan make:model Loan -m``  
After this command you will find one file in following path database/migrations and you have to put bellow code in your migration file for create Loan table.
```
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLoanTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('loan', function (Blueprint $table) {
            $table->id();
            $table->integer('customer_id');
            $table->float('amount')->nullable();
            $table->integer('period')->nullable();
            $table->float('principal_amount')->nullable();
            $table->integer('balance')->nullable();
            $table->integer('interest_rate')->default(10)->nullable();
            $table->tinyInteger('status')->default(0)->comment('0 for PANDING 1 for APPROVED');
            $table->integer('approved_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('loan');
    }
}

```
After create migration we need to run above migration by following command:  
``php artisan migrate``

**app/Models/Loan.php**
```
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

```

#### Step 6: Add LoanPayments Table and Model

next, we require to create migration & model for LoanPayments table using Laravel 8 php artisan command, so first fire bellow command:  
``php artisan make:model LoanPayments -m``  
After this command you will find one file in following path database/migrations and you have to put bellow code in your migration file for create LoanPayments table.
```
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLoanPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('loan_payments', function (Blueprint $table) {
            $table->id();
            $table->integer('loan_id');
            $table->float('loan_payment')->nullable();
            $table->float('amount')->nullable();
            $table->tinyInteger('status')->default(0)->comment('0 for PANDING 1 for PAID');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('loan_payments');
    }
}

```
After create migration we need to run above migration by following command:  
``php artisan migrate``

**app/Models/LoanPayments.php**
```
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

```

#### Step 7: Add user_type in users Table and Model

next, we require to create migration for users table using Laravel 8 php artisan command, so first fire bellow command:  
``php artisan make:migration add_user_type_to_users_table --table=users``  
After this command you will find one file in following path database/migrations and you have to put bellow code in your migration file for add user_type column users table.
```
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUserTypeToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            //
            $table->string('user_type')->after('remember_token')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            //
            $table->dropColumn('user_type');
        });
    }
}

```
After create migration we need to run above migration by following command:  
``php artisan migrate``

**app/Models/users.php**
```
<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'user_type'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
}

```

#### Step 7: Add payment_date in loan_payments Table and Model

next, we require to create migration for loans_payments table using Laravel 8 php artisan command, so first fire bellow command:  
``php artisan make:migration add_user_type_to_users_table --table=users``  
After this command you will find one file in following path database/migrations and you have to put bellow code in your migration file for add payment_date column loan_payments table.
```
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPaymentDateToLoanPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('loan_payments', function (Blueprint $table) {
            //
            $table->date('payment_date')->after('status')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('loan_payments', function (Blueprint $table) {
            //
            $table->dropColumn('payment_date');
        });
    }
}

```
After create migration we need to run above migration by following command:  
``php artisan migrate``

**app/Models/LoanPayments.php**
```
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


```
#### Step 8: Create Controller Files

in next step, now we have created a new controller as LoginController and LoanController:

``php artisan make:controller Api/LoginController``

``php artisan make:controller Api/LoanController``

#### Step 9: Create API Routes
In this step, we will create api routes. Laravel provide api.php file for write web services route. So, let's add new route on that file.

**routes/api.php**

```
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

```

#### Step 8: Create Helper Functions

**app/Helpers/Functions.php**

```
<?php
   
   /**
    * Success response method
    *
    * @param $result
    * @param $message
    * @return \Illuminate\Http\JsonResponse
    */
   function sendResponse($result, $message)
   {
       $response = [
           'success' => true,
           'data'    => $result,
           'message' => $message,
       ];
   
       return response()->json($response, 200);
   }
   
   /**
    * Return error response
    *
    * @param       $error
    * @param array $errorMessages
    * @param int   $code
    * @return \Illuminate\Http\JsonResponse
    */
   function sendError($error, $errorMessages = [], $code = 404)
   {
       $response = [
           'success' => false,
           'message' => $error,
       ];
   
       !empty($errorMessages) ? $response['data'] = $errorMessages : null;
   
       return response()->json($response, $code);
   }
```

**composer.json**
```
    "autoload": {
        "psr-4": {
            "App\\": "app/",
            "Database\\Factories\\": "database/factories/",
            "Database\\Seeders\\": "database/seeders/"
        },
        "files": [
            "app/Helpers/Functions.php"
        ]
    },
```

``composer dump-autoload``

**app\Http\Controllers\Api\LoginController.php**

```
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Exception;

class LoginController extends Controller
{
    /**
     * User login API method
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'    => 'required|email',
            'password' => 'required'
        ]);

        if ($validator->fails()) return sendError('Validation Error.', $validator->errors(), 422);

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $user             = Auth::user();
            $success['name']  = $user->name;
            $success['id']  = $user->id;
            $success['token'] = $user->createToken('accessToken')->accessToken;

            return sendResponse($success, 'You are successfully logged in.');
        } else {
            return sendError('Unauthorised', ['error' => 'Unauthorised'], 401);
        }
    }

    /**
     * User registration API method
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'     => 'required',
            'email'    => 'required|email|unique:users',
            'password' => 'required|min:8'
        ]);

        if ($validator->fails()) return sendError('Validation Error.', $validator->errors(), 422);

        try {
            
            $user = User::create([
                'name'     => $request->name,
                'email'    => $request->email,
                'user_type'=> isset($request->user_type)?'admin':'',
                'password' => bcrypt($request->password)
            ]);

            $success['name']  = $user->name;
            $message          = 'Yay! A user has been successfully created.';
            $success['token'] = $user->createToken('accessToken')->accessToken;
        } catch (Exception $e) {
            $success['token'] = [];
            $message          = 'Oops! Unable to create a new user.';
        }

        return sendResponse($success, $message);
    }
}

```

#### Step 9: Create Eloquent API Resources

This is a very important step of creating rest api in laravel 8. you can use eloquent api resources with api. it will help you to make same response layout of your model object. we used in PostController file. now we have to create it using following command:

``php artisan make:resource LoanResource``

Now there created a new file with a new folder on following path:

``app/Http/Resources/LoanResource.php``

```
<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class LoanResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $status = 'PENDING';
        if($request->status == 1)
        {
            $status = 'APPROVED';
        }
        return [
            'amount'=> $this->amount,
            'term' => $this->period,
            'status'=>$status,
            'created_at'  => $this->created_at->format('d-m-Y')
        ];
    }
}

```

**app\Http\Controllers\Api\LoanController.php**

```
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

```

Now we are ready to run full restful api and also passport api in laravel. so let's run our example so run bellow command for quick run:

``php artisan serve``

make sure in details api we will use following headers as listed bellow:

```
'headers' => [
    'Accept'        => 'application/json',
    'Authorization' => 'Bearer '.$accessToken,
]
```

Here is Routes URL with Verb:

Now simply you can run above listed url like:

- **User Register API:** Verb:POST, URL: http://127.0.0.1:8000/api/v1/register
- **User Login API:** Verb:POST, URL: http://127.0.0.1:8000/api/v1/login
- **Loan List API:** Verb:GET, http://127.0.0.1:8000/api/v1/get_loans
- **Create Loan Request API:** Verb:POST, http://127.0.0.1:8000/api/v1/loan
- **Loan Approval API:** Verb:POST, URL: http://127.0.0.1:8000/api/v1/loan_approval/{loan_id}
- **Loan Payment API:** Verb:POST, URL: http://127.0.0.1:8000/api/v1/loan_payment
