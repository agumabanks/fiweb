<?php

use App\Http\Controllers\Api\V1\Agent\AgentController;



use App\Http\Controllers\Api\V1\Customer\Auth\PasswordResetController;
use App\Http\Controllers\Api\V1\Customer\WithdrawController;
use App\Http\Controllers\Api\V1\Agent\AgentWithdrawController;
use App\Http\Controllers\Api\V1\Agent\Auth\PasswordResetController as AgentPasswordResetController;
use App\Http\Controllers\Api\V1\Agent\TransactionController as AgentTransactionController;
use App\Http\Controllers\Api\V1\BannerController;
use App\Http\Controllers\Api\V1\ConfigController;
use App\Http\Controllers\Api\V1\Customer\Auth\CustomerAuthController;
use App\Http\Controllers\Api\V1\Customer\TransactionController;
use App\Http\Controllers\Api\V1\GeneralController;
use App\Http\Controllers\Api\V1\LoginController;
use App\Http\Controllers\Api\V1\NotificationController;
use App\Http\Controllers\Api\V1\OTPController;
use App\Http\Controllers\Api\V1\RegisterController;
use App\Http\Controllers\Payment\Api\PaymentOrderController;
use App\Http\Controllers\Api\V1\Agent\Auth\AgentAuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ClientManagementController;

use App\Http\Controllers\Api\LoanOfferApiController;


// pesa pal
use App\Http\Controllers\PesapalController;
use App\Http\Controllers\MembershipController;

use App\Http\Controllers\LoanOfferController;
use App\Http\Controllers\LoanApplicationController;

use App\Http\Controllers\ClientController;

use App\Http\Controllers\CardController ; 

use App\Http\Controllers\AgentLoanTransactionController;

use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\FinanceController; 
use App\Http\Controllers\Api\FinReportsController;

use App\Http\Controllers\Api\DailyReportController; 
use App\Http\Controllers\Api\PerformanceController;



Route::apiResource('loan-applications', LoanApplicationController::class);
Route::apiResource('loan-offers', LoanOfferController::class);

 


Route::group(['namespace' => 'Api\V1'], function () {
    
    Route::get('unpaidclients', [ClientController::class, 'getClientsWhoHaventPaidToday']); // Get all clients
    
    // getClientsWhoPaidToday
     Route::get('getClientsWhoPaidToday', [ClientController::class, 'getClientsWhoPaidToday']); // Get all clients
    
    // desktop
    Route::get('dclients', [ClientManagementController::class, 'getClients']); // Get all clients
    Route::post('/dclients', [ClientManagementController::class, 'store']); // Create a new client
    Route::get('/dclients/{id}', [ClientManagementController::class, 'show']); // Get client by ID
    Route::put('/dclients/{id}', [ClientManagementController::class, 'update']); // Update client by ID
    Route::delete('/dclients/{id}', [ClientManagementController::class, 'destroy']); // Delete client by ID
    Route::get('/dclients/search', [ClientManagementController::class, 'search']); // Search clients by name or email
    Route::get('/dclients/{id}/loans', [ClientManagementController::class, 'clientLoans']); // Get client loans
    
    
    
    
    Route::get('dclients/{id}/loans', [ClientManagementController::class, 'clientLoans']);  // Get client loans
    Route::get('clients/loans/{loan_id}', [LoanController::class, 'showLoan']);  // Get loan by ID
    Route::post('clients/{id}/loans', [LoanController::class, 'addLoan']);  // Add a new loan for a client
    Route::put('clients/loans/{loan_id}', [LoanController::class, 'updateLoan']);  // Update loan by loan ID
    Route::delete('clients/loans/{loan_id}', [LoanController::class, 'deleteLoan']);  // Delete loan by loan ID


    Route::get('agents/{agentId}/clients-with-running-loans', [LoanOfferController::class, 'getAgentsClientsWithRunningLoans']);  // Get clients with running loans
    Route::get('agents/{agentId}/clients-with-paid-loans', [LoanOfferController::class, 'getAgentsClientsWithPaidLoans']);  // Get clients with paid loans
    Route::get('agents/{agentId}/clients-with-pending-loans', [LoanOfferController::class, 'getAgentsClientsWithPendingLoans']);  // Get clients with pending loans
    
    // Route::post('dclients/{id}/upload-photo', [ClientManagement



        // Desktop API routes for Loan Offer and Agent-related clients
        Route::get('dagents/{agentId}/clients-with-running-loans', [LoanOfferController::class, 'getAgentsClientsWithRunningLoans']);  // Get clients with running loans
        Route::get('dagents/{agentId}/clients-with-paid-loans', [LoanOfferController::class, 'getAgentsClientsWithPaidLoans']);  // Get clients with paid loans
        Route::get('dagents/{agentId}/clients-with-pending-loans', [LoanOfferController::class, 'getAgentsClientsWithPendingLoans']);  // Get clients with pending loans
        
        // Desktop API routes from LoanOfferApiController
        Route::get('dloans/{id}/reverse', [LoanOfferApiController::class, 'reversePayment']); // Reverse payment
        
        Route::delete('dloans/{id}', [LoanOfferApiController::class, 'deleteLoan']); // Delete loan
        Route::post('dclients/{id}/loan', [LoanOfferApiController::class, 'addClientLoan']); // Add client loan
        Route::post('dloans/pay', [LoanOfferApiController::class, 'payLoan']); // Pay loan
        Route::get('dinstallments/today', [LoanOfferApiController::class, 'todaysLoanInstallments']); // Get today's loan installments
        Route::post('dagents/total-amount', [LoanOfferApiController::class, 'totalAmountForAgentOnDate']); // Get total amount for agent today
        Route::post('dloans/create', [LoanOfferApiController::class, 'createLoan']); // Create a loan

    
    
    
    
    
    
// finance

       
// Desktop API Routes from LoanOfferApiController
// Route::prefix('d')->group(function () {}; 
    
 // Group routes under 'finance' prefix and apply authentication middleware if needed
Route::prefix('finance')->group(function () {
    // Expenses Routes
    Route::get('/expenses', [FinanceController::class, 'getExpenses']);
    Route::post('/expenses', [FinanceController::class, 'storeExpense']);
    Route::put('/expenses/{id}', [FinanceController::class, 'updateExpense']);
    Route::delete('/expenses/{id}', [FinanceController::class, 'deleteExpense']);

    // Cashflows Routes
    Route::get('/cashflows', [FinanceController::class, 'getCashflows']);
    Route::post('/cashflows', [FinanceController::class, 'storeCashflow']);
    Route::put('/cashflows/{id}', [FinanceController::class, 'updateCashflow']);
    Route::delete('/cashflows/{id}', [FinanceController::class, 'deleteCashflow']);
});   
    
    
    
Route::prefix('reports')->group(function () {
    // getDailyReport
     Route::get('/loan-statistics', [DailyReportController::class, 'getLoanStatistics']);
    Route::get('/cashflow', [DailyReportController::class, 'getCashflowReport']);
    Route::get('/agents', [DailyReportController::class, 'getAgentsReport']);
    Route::get('/clients', [DailyReportController::class, 'getClientsReport']);
    Route::get('/new-loans', [DailyReportController::class, 'getNewLoansReport']);
    Route::get('/financial-summary', [DailyReportController::class, 'getFinancialSummary']);

});
    
    
    
    // perfomance 
    
Route::prefix('performance')->group(function () {
    Route::get('/performance/all-metrics', [PerformanceController::class, 'getAllPerformanceMetrics']);

     // Performance Routes
        Route::get('/loans/total', [PerformanceController::class, 'totalLoansIssued']);
        Route::get('/loans/repayment-rates', [PerformanceController::class, 'loanRepaymentRates']);
        Route::get('/loans/defaults', [PerformanceController::class, 'loanDefaults']);
        
        Route::get('/agent/{agentId}/activity', [PerformanceController::class, 'agentActivity']);
        Route::get('/agent/{agentId}/successful-collections', [PerformanceController::class, 'successfulCollections']);
        Route::get('/agent/rankings', [PerformanceController::class, 'agentRanking']);
        
        Route::get('/client/{clientId}/credit-history', [PerformanceController::class, 'clientCreditHistory']);
        Route::get('/client/{clientId}/account-activity', [PerformanceController::class, 'clientAccountActivity']);
        
        Route::get('/institution/portfolio-at-risk', [PerformanceController::class, 'portfolioAtRisk']);
        Route::get('/institution/liquidity-metrics', [PerformanceController::class, 'liquidityMetrics']);


});
    
    
    
// Desktop API Routes from LoanOfferApiController
Route::prefix('d')->group(function () {


Route::get('/dashboard/loan-stats', [DashboardController::class, 'getLoanDashboardStats'])
        ->name('dashboard.loan-stats');
        
        
Route::get('dclients/{id}/loan-history', [LoanOfferApiController::class, 'clientLoanspayHistory']); 


    // 1. Reverse Payment
    Route::post('loans/{id}/reverse', [LoanOfferApiController::class, 'reversePayment'])
        ->name('dloans.reversePayment'); // Reverses a loan payment
        
    Route::get('/latest-payment-transactions', [LoanOfferApiController::class, 'latestPaymentTransactions']);


 
    // 3. Get Agent's Clients with Running Loans
    Route::get('agents/{agentId}/clients-running-loans', [LoanOfferApiController::class, 'getAgentsClientsWithRunningLoans'])
        ->name('dagents.clientsRunningLoans'); // Fetches clients with running loans for a specific agent

    // 4. Get Agent's Clients with Pending Loans
    Route::get('agents/{agentId}/clients-pending-loans', [LoanOfferApiController::class, 'getAgentsClientsWithPendingLoans'])
        ->name('dagents.clientsPendingLoans'); // Fetches clients with pending loans for a specific agent

    // 5. Get Agent's Clients with Paid Loans
    Route::get('agents/{agentId}/clients-paid-loans', [LoanOfferApiController::class, 'getAgentsClientsWithPaidLoans'])
        ->name('dagents.clientsPaidLoans'); // Fetches clients with paid loans for a specific agent

    // 6. Delete Loan
    Route::delete('loans/{id}', [LoanOfferApiController::class, 'deleteLoan'])
        ->name('dloans.deleteLoan'); // Deletes a specific loan

    // 7. Add Client Loan
    Route::post('clients/{id}/loan', [LoanOfferApiController::class, 'addClientLoan'])
        ->name('dclients.addLoan'); // Adds a new loan for a specific client

    // 8. Admin Paying Loan
    Route::post('loans/{id}/admin-pay', [LoanOfferApiController::class, 'adminPayingLoan'])
        ->name('dloans.adminPay'); // Admin processes a loan payment

    // 9. Pay Loan Variant Z
    Route::post('loans/pay-z', [LoanOfferApiController::class, 'payLoanZ'])
        ->name('dloans.payLoanZ'); // Processes a loan payment (Variant Z)

    // 10. Pay Loan Variant Z1
    Route::post('loans/pay-z1', [LoanOfferApiController::class, 'payLoanZ1'])
        ->name('dloans.payLoanZ1'); // Processes a loan payment (Variant Z1)

    // 11. Pay Loan Variant Zx
    Route::post('loans/pay-zx', [LoanOfferApiController::class, 'payLoanZx'])
        ->name('dloans.payLoanZx'); // Processes a loan payment (Variant Zx)

    // 12. Pay Loan
    Route::post('loans/pay', [LoanOfferApiController::class, 'payLoan'])
        ->name('dloans.payLoan'); // Processes a loan payment

    // 13. Update Loan Payment
    Route::put('loans/{loanId}/update-payment', [LoanOfferApiController::class, 'updateLoanPayment'])
        ->name('dloans.updateLoanPayment'); // Updates payment details for a loan

    // 14. Update Loan Payment Variant 10
    Route::put('loans/{loanId}/update-payment10', [LoanOfferApiController::class, 'updateLoanPayment10'])
        ->name('dloans.updateLoanPayment10'); // Another variant to update loan payment details

    // 15. Store Client Loan
    Route::post('clients/loan/store', [LoanOfferApiController::class, 'storeClientLoan'])
        ->name('dclients.storeLoan'); // Stores a new loan for a client

    // 16. Create Loan
    Route::post('loans/create', [LoanOfferApiController::class, 'createLoan'])
        ->name('dloans.createLoan'); // Creates a new loan offer

    // 17. Edit Loan
    Route::get('loans/{id}/edit', [LoanOfferApiController::class, 'editLoan'])
        ->name('dloans.editLoan'); // Retrieves loan details for editing

    // 18. Edit Loan Variant 2
    Route::post('loans/edit2', [LoanOfferApiController::class, 'editLoan2'])
        ->name('dloans.editLoan2'); // Another variant to retrieve loan details for editing

    // 19. Save Loan Edit Variant 2
    Route::put('loans/save-edit2/{loanId}', [LoanOfferApiController::class, 'saveLoanEdit2'])
        ->name('dloans.saveLoanEdit2'); // Saves edited loan details (Variant 2)

    // 20. Save Loan Edit
    Route::put('loans/save-edit/{loanId}', [LoanOfferApiController::class, 'saveLoanEdit'])
        ->name('dloans.saveLoanEdit'); // Saves edited loan details

    // 21. Show Loan Details
    Route::get('loans/{id}/show', [LoanOfferApiController::class, 'showLoan'])
        ->name('dloans.showLoan'); // Displays details of a specific loan

    // 22. Approve Loan
    Route::post('loans/{id}/approve', [LoanOfferApiController::class, 'approveLoan'])
        ->name('dloans.approveLoan'); // Approves a loan and creates payment installments

    // 23. Get Client QR Code
    Route::get('clients/{clientId}/qr', [LoanOfferApiController::class, 'getClientQr'])
        ->name('dclients.getClientQr'); // Generates a QR code for a client

    // 24. Pay Loan Variant 3
    Route::post('loans/pay3', [LoanOfferApiController::class, 'payLoan3'])
        ->name('dloans.payLoan3'); // Processes a loan payment with specific logic (Variant 3)

    // 25. Pay Loan Variant 33
    Route::post('loans/pay33', [LoanOfferApiController::class, 'payLoan33'])
        ->name('dloans.payLoan33'); // Processes a loan payment and records a payment transaction (Variant 33)

    // 26. Pay Loan New
    Route::post('loans/pay-new', [LoanOfferApiController::class, 'payLoanNew'])
        ->name('dloans.payLoanNew'); // Processes a new variant of loan payment

    // 27. Pay Loan Variant 111e
    Route::post('loans/pay111e', [LoanOfferApiController::class, 'payLoan111e'])
        ->name('dloans.payLoan111e'); // Processes a loan payment with another specific logic (Variant 111e)

    // 28. Today's Loan Installments
    Route::get('installments/today', [LoanOfferApiController::class, 'todaysLoanInstallments'])
        ->name('dinstallments.todaysLoanInstallments'); // Retrieves loan installments due today

    // 29. Today's Schedule for Agent
    Route::get('agents/{agentId}/schedule/today', [LoanOfferApiController::class, 'todaysSchedule'])
        ->name('dagents.todaysSchedule'); // Retrieves today's schedule for an agent

    // 30. Total Amount for Agent on Date
    Route::post('agents/{agentId}/total-amount', [LoanOfferApiController::class, 'totalAmountForAgentOnDate'])
        ->name('dagents.totalAmountForAgentOnDate'); // Calculates the total amount an agent needs to collect on a specific date

    // 31. Total Amount for Agent on Date Variant 1000
    Route::post('agents/{agentId}/total-amount1000', [LoanOfferApiController::class, 'totalAmountForAgentOnDate1000'])
        ->name('dagents.totalAmountForAgentOnDate1000'); // Another variant to calculate the total amount for an agent on a specific date

    // 32. All Plans
    Route::get('plans/all', [LoanOfferApiController::class, 'allplans'])
        ->name('dplans.all'); // Retrieves and displays all available loan plans

    // 33. Add Plan
    Route::get('plans/add', [LoanOfferApiController::class, 'addplan'])
        ->name('dplans.add'); // Prepares the view to add a new loan plan

    // 34. Create Plan
    Route::post('plans/create', [LoanOfferApiController::class, 'createplan'])
        ->name('dplans.create'); // Creates a new loan plan based on the request data

    // 35. Edit Plan
    Route::get('plans/{id}/edit', [LoanOfferApiController::class, 'editplan'])
        ->name('dplans.edit'); // Prepares the view to edit an existing loan plan

    // 36. Destroy Now (Delete Plan)
    Route::delete('plans/{id}/destroy', [LoanOfferApiController::class, 'destroyNow'])
        ->name('dplans.destroyNow'); // Deletes a loan plan based on its ID

    // 37. Update Now (Update Plan)
    Route::put('plans/{id}/update', [LoanOfferApiController::class, 'updateNow'])
        ->name('dplans.updateNow'); // Updates an existing loan plan with new data

    // 38. All Loans
    Route::get('loans/all', [LoanOfferApiController::class, 'all_loans'])
        ->name('dloans.all'); // Retrieves and displays all loans, with optional search functionality

    // 39. Paid Loans
    Route::get('loans/paid', [LoanOfferApiController::class, 'paidLoans'])
        ->name('dloans.paidLoans'); // Retrieves and displays all paid loans, with optional search functionality

    // 40. Pending Loans
    Route::get('loans/pending', [LoanOfferApiController::class, 'pendingLoans'])
        ->name('dloans.pendingLoans'); // Retrieves and displays all pending loans, with optional search functionality

    // 41. Pending Loans Variant 10
    Route::get('loans/pending10', [LoanOfferApiController::class, 'pendingLoans10'])
        ->name('dloans.pendingLoans10'); // Another variant to retrieve pending loans with client details

    // 42. Rejected Loans
    Route::get('loans/rejected', [LoanOfferApiController::class, 'rejectedLoans'])
        ->name('dloans.rejectedLoans'); // Retrieves and displays all rejected loans, with optional search functionality

    // 43. Running Loans
    Route::get('loans/running', [LoanOfferApiController::class, 'runningLoans'])
        ->name('dloans.runningLoans'); // Retrieves and displays all running loans, with optional search functionality

    // 44. Loan Plans Index
    Route::get('loan-plans', [LoanOfferApiController::class, 'loanplansindex'])
        ->name('dloanplans.index'); // Retrieves all loan plans and returns them as JSON

    // 45. Create Loan Variant 10
    Route::post('loans/create10', [LoanOfferApiController::class, 'createLoan10'])
        ->name('dloans.createLoan10'); // Creates a new loan offer with additional details

    // 46. Client Loans
    Route::get('clients/{id}/loans', [LoanOfferApiController::class, 'clientLoans'])
        ->name('dclients.loans'); // Retrieves all loans associated with a specific client

    // 47. User Loans List
    Route::get('users/{id}/loans', [LoanOfferApiController::class, 'userLoansList'])
        ->name('dusers.loansList'); // Retrieves all loans associated with a specific user/agent

    // 48. Withdrawal Methods
    Route::get('withdrawal-methods', [LoanOfferApiController::class, 'withdrawalMethods'])
        ->name('dwithdrawal-methods'); // Retrieves all withdrawal methods

    // 49. Show Loan Details
    Route::get('loans/{id}/show', [LoanOfferApiController::class, 'showLoan'])
        ->name('dloans.showLoan'); // Displays details of a specific loan

    // 50. Update Loan Offer
    Route::put('loans/{id}/update', [LoanOfferApiController::class, 'update'])
        ->name('dloans.update'); // Updates a specific loan offer

    // 51. Destroy Loan Offer
    Route::delete('loans/{id}/destroy', [LoanOfferApiController::class, 'destroy2'])
        ->name('dloans.destroy2'); // Deletes a specific loan offer

});
    
    
    
    
    
    
    
    
    
    // clients 
    Route::get('clients', [ClientController::class, 'index']);
    Route::post('agentclients', [ClientController::class, 'getAgentClient']);
    Route::post('add-clients', [ClientController::class, 'addClient']);
    Route::post('/addclients', [ClientController::class, 'addClient']);
    
    Route::get('/clients/search', [ClientController::class, 'search']);

    // client cards
    // Route::get('client-cards', [CardController::class, 'getAllClientsCardApi']));
    Route::get('/client-cards', [CardController::class, 'getAllClientsCardApi']);
    Route::get('/agents-cards', [CardController::class, 'getAllAgents']);
    Route::get('/agents/{agentId}/cards', [CardController::class, 'getAgentClientCards']);
    
    Route::get('agents/{agentId}/clients/total', [AgentLoanTransactionController::class, 'getAgentClientsSum']);

    

// get client with
    // running loans
    Route::get('/agents/clients/{agentId}', [CardController::class, 'getAgentsClientsWithRunningLoans']);
    Route::get('/agents/{agentId}/clients-with-running-loans', [LoanOfferController::class, 'getAgentsClientsWithRunningLoans']);

// getAgentsClientsWithPaidLoans
       Route::get('/agents/{agentId}/clients-with-paid-loans', [LoanOfferController::class, 'getAgentsClientsWithPaidLoans']);
       
       Route::get('agent/{agentId}/loan-transactions', [AgentLoanTransactionController::class, 'getLoanTransactions']);



// getAgentsClientsWithPendingLoans
       Route::get('/agents/{agentId}/clients-with-pending-loans', [LoanOfferController::class, 'getAgentsClientsWithPendingLoans']);

     
    // get client addClientGuarantor clientguarantorsList
    Route::post('getClient', [ClientController::class, 'getClientProfile']);
    Route::post('addClientGuarantor', [ClientController::class, 'addClientGuarantor']);
    Route::post('clientguarantorsList', [ClientController::class, 'clientguarantorsList']);
    Route::get('/today-installments', [LoanOfferController::class, 'todaysLoanInstallments']);
    
    // addd photo
    Route::post('clientphotos', [ClientController::class, 'addClientPhotos']);
    
    // getClientQr
    Route::post('getClientQr', [LoanOfferController::class, 'getClientQr']);
    
    // getCustomertest
    Route::get('getClientQrtest', [CustomerAuthController::class, 'getCustomertest']);
    

    
    
    // getUserByPhone  totalAmountForAgentOnDate
    Route::post('/getUserByPhone', [CustomerAuthController::class, 'getUserByPhone']);
    Route::post('/today-instal-sum', [LoanOfferController::class, 'totalAmountForAgentOnDate']);
    Route::post('todaysSchedule', [LoanOfferController::class, 'todaysSchedule']);
    
    Route::post('loans/pay', [LoanOfferController::class, 'payLoan']);



    Route::post('/membership', [MembershipController::class, 'createMembership']);
    Route::put('/membership/{id}/payment-status', [MembershipController::class, 'updateMembershipPaymentStatus']);
    Route::get('/membership/{userId}', [MembershipController::class, 'getUserMembership']);
    
    // loan managment
    Route::get('loan-plans', [LoanOfferController::class, 'loanplansindex']);
    Route::post('create-loans', [LoanOfferController::class, 'createLoan']);
    
    // clientLoans
    Route::post('clientLoans', [LoanOfferController::class, 'clientLoans']);
    
    
    
    // userLoansList
    Route::post('loan-lists', [LoanOfferController::class, 'userLoansList']);
    
    // client pay history clientLoanspayHistory
    Route::post('clientLoanspayHistory', [LoanOfferController::class, 'clientLoanspayHistory']);
    


    Route::group(['middleware' => ['deviceVerify']], function () {
        Route::group(['middleware' => ['inactiveAuthCheck', 'trackLastActiveAt', 'auth:api']], function () {
            Route::post('check-customer', [GeneralController::class, 'checkCustomer']);
            Route::post('check-agent', [GeneralController::class, 'checkAgent']);

        });

        Route::group(['prefix' => 'customer', 'namespace' => 'Auth'], function () {

            Route::group(['prefix' => 'auth', 'namespace' => 'Auth'], function () {
                Route::post('register', [RegisterController::class, 'customerRegistration']);
                Route::post('login', [LoginController::class, 'customerLogin']);

                Route::post('check-phone', [CustomerAuthController::class, 'checkPhone']);
                Route::post('verify-phone', [CustomerAuthController::class, 'verifyPhone']);
                Route::post('resend-otp', [CustomerAuthController::class, 'resendOTP']);

                Route::post('forgot-password', [PasswordResetController::class, 'resetPasswordRequest']);
                Route::post('verify-token', [PasswordResetController::class, 'verifyToken']);
                Route::put('reset-password', [PasswordResetController::class, 'resetPasswordSubmit']);
            });

            Route::group(['middleware' => ['inactiveAuthCheck', 'trackLastActiveAt', 'auth:api', 'customerAuth', 'checkDeviceId']], function () {
                Route::get('get-customer', [CustomerAuthController::class, 'getCustomer']);
                Route::get('get-purpose', [CustomerAuthController::class, 'getPurpose']);
                Route::get('get-banner', [BannerController::class, 'getCustomerBanner']);
                Route::get('linked-website', [CustomerAuthController::class, 'linkedWebsite']);
                Route::get('get-notification', [NotificationController::class, 'getCustomerNotification']);
                Route::get('get-requested-money', [CustomerAuthController::class, 'getRequestedMoney']);
                Route::get('get-own-requested-money', [CustomerAuthController::class, 'getOwnRequestedMoney']);
                Route::delete('remove-account', [CustomerAuthController::class, 'removeAccount']);
                Route::put('update-kyc-information', [CustomerAuthController::class, 'updateKycInformation']);

                Route::post('check-otp', [OTPController::class, 'checkOtp']);
                Route::post('verify-otp', [OTPController::class, 'verifyOtp']);

                Route::post('verify-pin', [CustomerAuthController::class, 'verifyPin']);
                Route::post('change-pin', [CustomerAuthController::class, 'changePin']);

                Route::put('update-profile', [CustomerAuthController::class, 'updateProfile']);
                Route::post('update-two-factor', [CustomerAuthController::class, 'updateTwoFactor']);
                Route::put('update-fcm-token', [CustomerAuthController::class, 'updateFcmToken']);
                Route::post('logout', [CustomerAuthController::class, 'logout']);

                Route::post('send-money', [TransactionController::class, 'sendMoney']);
                Route::post('cash-out', [TransactionController::class, 'cashOut']);
                Route::post('request-money', [TransactionController::class, 'requestMoney']);
                Route::post('request-money/{slug}', [TransactionController::class, 'requestMoneyStatus']);
                Route::post('add-money', [TransactionController::class, 'addMoney']);
                // pesapal
                Route::post('pesa', [PesapalController::class, 'pay']);
                
               
                
                Route::post('withdraw', [TransactionController::class, 'withdraw']);
                Route::get('transaction-history', [TransactionController::class, 'transactionHistory']);

                Route::get('withdrawal-methods', [TransactionController::class, 'withdrawalMethods']);

                Route::get('withdrawal-requests', [WithdrawController::class, 'list']);
                
                
                
            });

        });

        Route::group(['prefix' => 'agent', 'namespace' => 'Auth'], function () {

            Route::group(['prefix' => 'auth', 'namespace' => 'Auth'], function () {
                Route::post('register', [RegisterController::class, 'agentRegistration']);
                Route::post('login', [LoginController::class, 'agentLogin']);

                Route::post('check-phone', [AgentAuthController::class, 'checkPhone']);
                Route::post('verify-phone', [AgentAuthController::class, 'verifyPhone']);
                Route::post('resend-otp', [AgentAuthController::class, 'resendOtp']);

                Route::post('forgot-password', [AgentPasswordResetController::class, 'resetPasswordRequest']);
                Route::post('verify-token', [AgentPasswordResetController::class, 'verifyToken']);
                Route::put('reset-password', [AgentPasswordResetController::class, 'resetPasswordSubmit']);
            });
            Route::group(['middleware' => ['inactiveAuthCheck', 'trackLastActiveAt', 'auth:api', 'agentAuth', 'checkDeviceId']], function () {
                Route::get('get-agent', [AgentController::class, 'getAgent']);
                Route::get('get-notification', [NotificationController::class, 'getAgentNotification']);
                Route::get('get-banner', [BannerController::class, 'getAgentBanner']);
                Route::get('linked-website', [AgentController::class, 'linkedWebsite']);
                Route::get('get-requested-money', [AgentController::class, 'getRequestedMoney']);
                Route::put('update-kyc-information', [CustomerAuthController::class, 'updateKycInformation']);

                Route::post('check-otp', [OTPController::class, 'checkOtp']);
                Route::post('verify-otp', [OTPController::class, 'verifyOtp']);

                Route::post('verify-pin', [AgentController::class, 'verifyPin']);
                Route::post('change-pin', [AgentController::class, 'changePin']);

                Route::put('update-profile', [AgentController::class, 'updateProfile']);
                Route::post('update-two-factor', [AgentController::class, 'updateTwoFactor']);
                Route::put('update-fcm-token', [AgentController::class, 'updateFcmToken']);
                Route::post('logout', [AgentController::class, 'logout']);
                Route::delete('remove-account', [AgentController::class, 'removeAccount']);

                Route::post('send-money', [AgentTransactionController::class, 'cashIn']);
                Route::post('request-money', [AgentTransactionController::class, 'requestMoney']);
                Route::post('add-money', [AgentTransactionController::class, 'addMoney']);
                Route::post('withdraw', [AgentTransactionController::class, 'withdraw']);
                Route::get('transaction-history', [AgentTransactionController::class, 'transactionHistory']);

                Route::get('withdrawal-methods', [AgentTransactionController::class, 'withdrawalMethods']);

                Route::get('withdrawal-requests', [AgentWithdrawController::class, 'list']);
            });
        });

        Route::get('/config', [ConfigController::class, 'configuration']);
        Route::get('/faq', [GeneralController::class, 'faq']);
    });

    Route::post('/create-payment-order', [PaymentOrderController::class, 'createPaymentOrder']);
    Route::post('/payment-success', [PaymentOrderController::class, 'payment_success']);
    Route::post('/payment-verification', [PaymentOrderController::class, 'paymentVerification']);

});
