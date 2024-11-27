 

<?php
 
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Client;
use App\Models\LoanPayment;
use App\Models\LoanPaymentInstallment;
use App\Models\UserLoan;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class LoanController extends Controller
{}