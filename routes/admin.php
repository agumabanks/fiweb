<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PurposeController;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AgentController;
use App\Http\Controllers\Admin\BonusController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\EMoneyController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\LanguageController;
use App\Http\Controllers\Admin\MerchantController;
use App\Http\Controllers\Admin\TransferController;
use App\Http\Controllers\Admin\WithdrawController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\HelpTopicController;
use App\Http\Controllers\Admin\SMSModuleController;
use App\Http\Controllers\Admin\Auth\LoginController;
use App\Http\Controllers\Admin\WithdrawalController;
use App\Http\Controllers\Admin\SocialMediaController;
use App\Http\Controllers\Admin\SystemAddonController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\ContactMessageController;
use App\Http\Controllers\Admin\BusinessSettingsController;
use App\Http\Controllers\Admin\LandingPageSettingsController;
use App\Http\Controllers\ExpenseController; 
use App\Http\Controllers\MembershipController;

use App\Http\Controllers\ExcessFundController;


use App\Http\Controllers\TransactionController;

use App\Http\Controllers\CardController ;

use App\Http\Controllers\AdminReportController;

use App\Http\Controllers\SavingsController;


use App\Http\Controllers\BranchesController;
use App\Http\Controllers\LoanController;

use App\Http\Controllers\AgentReportController;


use App\Http\Controllers\LoanOfferController;
use App\Http\Controllers\LoanApplicationController;
use App\Http\Controllers\ClientController;

use App\Http\Controllers\Admin\AppController;


Route::group(['namespace' => 'Admin', 'as' => 'admin.'], function () {
    Route::get('lang/{locale}', [LanguageController::class, 'lang'])->name('lang');

    Route::group(['namespace' => 'Auth', 'prefix' => 'auth', 'as' => 'auth.'], function () {
        Route::get('/code/captcha/{tmp}', 'LoginController@captcha')->name('default-captcha');
        Route::get('login', [LoginController::class, 'login'])->name('login');
        Route::post('login', [LoginController::class, 'submit']);
        Route::get('logout', [LoginController::class, 'logout'])->name('logout');

    });
    // deleteLoan
    
    Route::get('/settingsController', [SettingsController::class, 'index'])->name('settings.index');

// Display a listing of the memberships.
    
    // Membership resource routes
    Route::get('/memberships', [MembershipController::class, 'index'])->name('memberships.index');
    Route::get('/memberships/create', [MembershipController::class, 'create'])->name('memberships.create');
    Route::post('/memberships', [MembershipController::class, 'store'])->name('memberships.store');
    Route::get('/memberships/{membership}', [MembershipController::class, 'show'])->name('memberships.show');
    Route::get('/memberships/{membership}/edit', [MembershipController::class, 'edit'])->name('memberships.edit');
    Route::put('/memberships/{membership}', [MembershipController::class, 'update'])->name('memberships.update');
    Route::patch('/memberships/{membership}', [MembershipController::class, 'update']);
    Route::delete('/memberships/{membership}', [MembershipController::class, 'destroy'])->name('memberships.destroy');
    
    // Share transaction routes
    Route::get('/memberships/{membership}/shares/create', [MembershipController::class, 'createShareTransaction'])->name('shares.create');
    Route::post('/memberships/{membership}/shares', [MembershipController::class, 'storeShareTransaction'])->name('shares.store');
    Route::get('/memberships/{membership}/shares/transfer', [MembershipController::class, 'transferSharesForm'])->name('shares.transfer.form');
    Route::post('/memberships/{membership}/shares/transfer', [MembershipController::class, 'transferShares'])->name('shares.transfer.post');
    Route::get('/shares/create', [MembershipController::class, 'createGlobalShareTransaction'])->name('shares.create.global');
    
    // Report routes
    Route::get('/memberships/reports', [MembershipController::class, 'reportsIndex'])->name('memberships.reports.index');
    Route::post('/memberships/reports/generate', [MembershipController::class, 'generateReports'])->name('memberships.reports.generate');
    Route::post('/memberships/reports/export', [MembershipController::class, 'exportReport'])->name('memberships.reports.export');
    
    // Receipt routes
    Route::get('/memberships/{membership}/shares/{transaction}/receipt', [MembershipController::class, 'printTransactionReceiptPdf'])->name('shares.receipt.pdf');
    Route::get('/memberships/{membership}/shares/{transaction}/receipt/thermal', [MembershipController::class, 'printTransactionReceiptThermal'])->name('shares.receipt.thermal');

    // Route::get('/memberships', [MembershipController::class, 'index'])->name('memberships.index');
    
    // // Show the form for creating a new membership.
    // Route::get('/memberships/create', [MembershipController::class, 'create'])->name('memberships.create');
    
    // // Store a newly created membership in storage.
    // Route::post('/memberships', [MembershipController::class, 'store'])->name('memberships.store');
    
    // // Display the specified membership.
    // Route::get('/memberships/{membership}', [MembershipController::class, 'show'])->name('memberships.show');
    
    // // Show the form for editing the specified membership.
    // Route::get('/memberships/{membership}/edit', [MembershipController::class, 'edit'])->name('memberships.edit');
    
    // // Update the specified membership in storage.
    // Route::put('/memberships/{membership}', [MembershipController::class, 'update'])->name('memberships.update');
    // Route::patch('/memberships/{membership}', [MembershipController::class, 'update']); // Optional, for PATCH requests
    
    // // Remove the specified membership from storage.
    // Route::delete('/memberships/{membership}', [MembershipController::class, 'destroy'])->name('memberships.destroy');

   
// 1. List All Savings Accounts
    Route::get('/savings', [SavingsController::class, 'index'])->name('savings.index');
    
    // 2. Show Form to Create a New Savings Account
    Route::get('/savings/create', [SavingsController::class, 'create'])->name('savings.create');
    
    // 3. Store a New Savings Account
    Route::post('/savings', [SavingsController::class, 'store'])->name('savings.store');
    
    // 4. Display a Specific Savings Account
    Route::get('/savings/{savings}', [SavingsController::class, 'show'])->name('savings.show');
    
    // 5. Show Form to Edit a Savings Account
    Route::get('/savings/{savings}/edit', [SavingsController::class, 'edit'])->name('savings.edit');
    
    // 6. Update a Savings Account
    Route::put('/savings/{savings}', [SavingsController::class, 'update'])->name('savings.update');
    
    // 7. Delete a Savings Account
    Route::delete('/savings/{savings}', [SavingsController::class, 'destroy'])->name('savings.destroy');
    
    // 8. Show Form to Deposit Funds
    Route::get('/savings/{savings}/deposit', [SavingsController::class, 'depositForm'])->name('savings.depositForm');
    
    // 9. Handle Deposit Action
    Route::post('/savings/{savings}/deposit', [SavingsController::class, 'deposit'])->name('savings.deposit');
    
    // 10. Show Form to Withdraw Funds
    Route::get('/savings/{savings}/withdraw', [SavingsController::class, 'withdrawForm'])->name('savings.withdrawForm');
    
    // 11. Handle Withdrawal Action
    Route::post('/savings/{savings}/withdraw', [SavingsController::class, 'withdraw'])->name('savings.withdraw');
    
    Route::get('/savings/{savings}/transaction/{transaction}/receipt', [SavingsController::class, 'printTransactionReceiptThermal'])->name('savings.transaction.receipt');

// reports.index'
    Route::get('/savings/reports', [SavingsController::class, 'printTransactionReceipt'])->name('savings.reports.index');

       // Delinquency Report Routes
       Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('delinquency', [App\Http\Controllers\Admin\Reports\DelinquencyReportController::class, 'index'])->name('delinquency.index');
        Route::post('delinquency/fetch', [App\Http\Controllers\Admin\Reports\DelinquencyReportController::class, 'fetchData'])->name('delinquency.fetch');
        Route::get('delinquency/export/pdf', [App\Http\Controllers\Admin\Reports\DelinquencyReportController::class, 'exportPDF'])->name('delinquency.export.pdf');
        Route::get('delinquency/export/excel', [App\Http\Controllers\Admin\Reports\DelinquencyReportController::class, 'exportExcel'])->name('delinquency.export.excel');
    });

    // print
    // Route to view transaction details
    Route::get('/transaction/{transactionId}', [TransactionController::class, 'show'])->name('transaction.show');
    
    // sendSmsNotification
    Route::post('/sms/{transactionId}', [TransactionController::class, 'SmsNotification'])->name('transactiosn.sms');
    Route::post('admin/sms/{payment}', [TransactionController::class, 'SmsNotification'])->name('transaction.sms');

    Route::get('/admin/loan-arrears', [LoanOfferController::class, 'loanArrearsIndex'])->name('loan-arrears.index');
    Route::get('/admin/loan-arrears/data', [LoanOfferController::class, 'loanArrearsData'])->name('loan-arrears.data');
    

    Route::get('/print-receipt/{transactionId}', [TransactionController::class, 'printTransactionReceipt'])->name('transaction.printReceipt');
   
    Route::get('/print-statment/{clientId}', [TransactionController::class, 'showStatment'])->name('print-statment');
    Route::get('/print-loan-statment/{Id}', [TransactionController::class, 'showLoanStatment'])->name('print-showLoanStatment');
 
    // Route::get('/agent-report/{agentId}', [AgentReportController::class, 'index'])->name('agent.report');


    // Route::post('/admin/loans/update-payment/{loanId}', [LoanOfferController::class, 'updateLoanPayment'])->name('loans.updatePayment');

    // For listing advances
    Route::get('/admin/loan-advances', [LoanOfferController::class, 'loanAdvancesIndex'])->name('loan-advances.index');
    Route::get('/admin/loan-advances/data', [LoanOfferController::class, 'listLoanAdvances'])->name('loan-advances.data');


 // Example for expense routes
        Route::post('/expenses/{id}/reverse', [ExpenseController::class, 'reverse'])->name('expenses.reverse');
        Route::delete('/expenses/{id}', [ExpenseController::class, 'destroy'])->name('expenses.destroy');
            
            // Example for cashflow routes
        Route::delete('/cashflow/{id}', [ExpenseController::class, 'destroyCashflow'])->name('cashflow.destroy');
        
        // Excess Funds Routes
        Route::get('admin/excess-funds',                        [ExpenseController::class, 'indexExcessfund'])->name('excess-funds.index');
        Route::get('admin/excess-funds/create',                 [ExpenseController::class, 'createExcessfund'])->name('excess-funds.create');
        Route::post('admin/excess-funds',                       [ExpenseController::class, 'storeExcessfund'])->name('excess-funds.store');
        Route::get('admin/excess-funds/{excessFund}/edit',      [ExpenseController::class, 'editExcessfund'])->name('excess-funds.edit');
        Route::put('admin/excess-funds/{excessFund}',           [ExpenseController::class, 'updateExcessfund'])->name('excess-funds.update');
        Route::delete('admin/excess-funds/{excessFund}',        [ExpenseController::class, 'destroyExcessfund'])->name('excess-funds.destroy');

   
    // branches
    Route::get('/branches/create', [BranchesController::class, 'create'])->name('branches.create');
    Route::post('/branches', [BranchesController::class, 'store'])->name('branches.store');
    Route::get('/branches', [BranchesController::class, 'index'])->name('allbranches');



      Route::get('/clear-cache', [AdminController::class, 'clearCache'])->name('cache.clear');
      Route::get('/apps', [AdminController::class, 'apps'])->name('apps.get');
      Route::get('/admin/apps', [AppController::class, 'index'])->name('apps.index');
      Route::post('/admin/apps/store', [AppController::class, 'store'])->name('apps.store');
      Route::get('/admin/apps/add', [AppController::class, 'addapp'])->name('apps.add');
      Route::get('/admin/apps/download/{id}', [AppController::class, 'download'])->name('apps.download');


 Route::get('agent-report-today/{agentId}', [AgentReportController::class, 'index'])->name('agent.report.performance');
 Route::get('/agent-report/{agentId}', [AgentReportController::class, 'downloadPdf'])->name('agent.report-pdf');


            //   Route::get('admin/transaction/{user_id}', [AdminController::class, 'transaction'])->name('admin.transaction');


        Route::group(['middleware' => ['admin']], function () {
            
            
            
            
        Route::get('/clients/export/excel', [ClientController::class, 'exportClientsToExcel'])->name('clients.export.excel');

  
        Route::get('agent-report-today', [ClientController::class, 'agentDash'])->name('agent.report');

        Route::get('agent-report', [ClientController::class, 'agentDash'])->name('agent.report');
        Route::get('/agent-report-transaction', [ClientController::class, 'agentTransactions'])->name('agent.trans');
        Route::get('/agent/{agentId}/clients', [ClientController::class, 'agentClientDetails'])->name('agent.client.details');
        
        Route::get('/admin/reports/export-daily-analytics-pdf', [AdminReportController::class, 'exportAnalyticsPDF'])->name('reports.exportDailyAnalyticsPDF');


       Route::get('cards/{id}/print', [CardController::class, 'generatePdf'])->name('cards.print');

       Route::post('/admin/cashflow/store', [ExpenseController::class, 'storeCashflow'])->name('cashflow.store');
       
       // Routes for Cash Flow
        Route::get('cashflow/create', [ExpenseController::class, 'createCashflow'])->name('cashflow.create');
        Route::post('cashflow/store', [ExpenseController::class, 'storeCashflow'])->name('cashflow.store');
        
        // Routes for Expenses
        Route::get('expense/create', [ExpenseController::class, 'create'])->name('expense.create');
        Route::post('expense/store', [ExpenseController::class, 'store'])->name('expense.store');


        // expenses
        Route::post('/expe/del/{id}', [ExpenseController::class, 'distroy'])->name('expense.delete');
          // clients 
        Route::get('/clients', [ClientController::class, 'clients'])->name('allclients');
        Route::get('/activeclients', [ClientController::class, 'activeClients'])->name('clients.active');

        // clients.ajax
        // Route::get('/admin/allclients', [ClientController::class, 'clients'])->name('clients');

        Route::get('/bannedclients', [ClientController::class, 'bannedClients'])->name('clients.banned');
        Route::get('/with-balanceclients', [ClientController::class, 'clientsWithBalance'])->name('clients.with-balance');
        Route::get('/verifiedclients', [ClientController::class, 'verifiedClients'])->name('clients.verified');
        Route::get('/clients/{id}', [ClientController::class, 'show'])->name('clients.profile');
        // delete
        // Route::get('/clients/{id}', [ClientController::class, 'show'])->name('clients.profile');
        // Route::post('/clients/delete/{id}', [ClientController::class, 'delete'])->name('clients.delete');
        Route::post('/clients/del/{id}', [ClientController::class, 'distroy'])->name('clients.delete');


        Route::post('/access/histo', [ClientController::class, 'distroy'])->name('access.history'); 
        Route::post('/notification/history', [ClientController::class, 'distroy'])->name('notification.history');
        
        Route::post('/clients/{client}/topup', [LoanOfferController::class, 'topup'])->name('clients.topup');
        Route::post('/admin/loans/{loan}/renew', [LoanOfferController::class, 'renewLoan'])->name('loans.renewLoan');

        // Route::post('/admin/clients/{client}/topup', [ClientController::class, 'topup'])->name('clients.topup');
        Route::get('/clients/{client}/transaction-history', [ClientController::class, 'getTransactionHistory'])->name('clients.transactionHistory');



        
        // delete loan 
        Route::delete('/clients/loan/del/{id}', [LoanOfferController::class, 'deleteLoan'])->name('loan.delete');
        
        // deleteLoan
        Route::post('/clients/loan/deleteLoan/{id}', [LoanOfferController::class, 'deleteLoanNow'])->name('loan.deleteLoan');

        
        // Route to display the edit form
        Route::get('/clients/{id}/edit', [ClientController::class, 'edit'])->name('clients.edit');
        
        // Route to handle the update submission
        Route::put('/clients/{id}', [ClientController::class, 'update'])->name('clients.update');
        
        // addClientGuarantorWeb
         Route::post('/clients/addguar/{id}', [ClientController::class, 'addClientGuarantorWeb'])->name('clients.addClientGuarantorWeb');


        // client cards 
        Route::get('/client/cards', [ClientController::class, 'clientsCards'])->name('client.cards');
        
        // add client
        Route::get('/client/create', [ClientController::class, 'createClient'])->name('client.create');
        Route::post('/client/store', [ClientController::class, 'store'])->name('client.store');
        
        
        // add client loan online
        Route::post('admin/loans/storeClientLoan', [LoanOfferController::class, 'storeClientLoan'])->name('loans.storeClientLoan');
        Route::get('admin/loans/updateClientLoan/{id}', [LoanOfferController::class, 'addClientLoan'])->name('loans.updateClientLoan');


        // client search with shortcut
        Route::get('admin/clients/search', [ClientController::class, 'searchClient'])->name('clients.search');

    // pay loan by admin
        Route::get('/loan/{loanid}/pay', [LoanOfferController::class, 'adminPayingLoan'])->name('loans.admin.pay1');
        
        Route::post('/loan/slot/pay/{slotId}', [LoanOfferController::class, 'updateLoanPayment'])->name('loans.admin.pay');

        Route::get('/admin/loans/pay-loan/{id}', [LoanOfferController::class, 'adminPayingLoan'])->name('loans.admin.pay');
        Route::post('/admin/loans/update-payment/{loanId}', [LoanOfferController::class, 'updateLoanPayment'])->name('loans.updatePayment');
        
        
        // Route to reverse the payment
        Route::patch('admin/payments/reverse/{id}', [LoanOfferController::class, 'reversePayment'])->name('payments.reverse');



        
        // reports
        Route::get('/report', [AdminReportController::class, 'index'])->name('report.index');

        Route::post('/actual-cash/store', [ExpenseController::class, 'storeActualSafeCash'])->name('actual-cash.store');
        Route::get('/add-actual-cash/store', [ExpenseController::class, 'createActualCash'])->name('actual-cash.add');

        // actual-cash.edit
        // Route::get('/add-actual-cash/edit', [ExpenseController::class, 'editActualSafeCash'])->name('actual-cash.edit');

        Route::get('admin/actual-cash/{id}/edit', [ExpenseController::class, 'editActualSafeCash'])->name('actual-cash.edit');
        Route::post('admin/actual-cash/{id}', [ExpenseController::class, 'updateActualSafeCash'])->name('actual-cash.update');

        // actual-cash.destroy
        Route::delete('/add-actual-cash/distrory/{id}', [ExpenseController::class, 'destroyActualSafeCash'])->name('actual-cash.destroy');

        // Route::post('admin/actual-cash/store', [ExpenseController::class, 'storeActualSafeCash'])->name('admin.actual-cash.store');
        // Route::post('admin/actual-cash/{id}', [ExpenseController::class, 'updateActualSafeCash'])->name('admin.actual-cash.update');
        // Route::delete('admin/actual-cash/{id}', [ExpenseController::class, 'destroyActualSafeCash'])->name('admin.actual-cash.destroy');

        // Route::delete('admin/actual-cash/{id}', [ExpenseController::class, 'destroyActualSafeCash'])->name('admin.actual-cash.destroy');

        

        
        // Route::get('/admin/reports', [AdminReportController::class, 'index'])->name('daily-reports.index');
        
        
        Route::get('/admin/daily-report', [AdminReportController::class, 'generateDailyReport'])->name('daily-report');
        Route::get('/reports/create', [AdminReportController::class, 'create'])->name('daily-reports.create');
        Route::post('/reports/store', [AdminReportController::class, 'store'])->name('daily-reports.store');
        Route::get('/reports/export', [AdminReportController::class, 'export'])->name('daily-reports.export');
        
        Route::get('/loan/{id}', [LoanOfferController::class, 'showLoan'])->name('loans.show');
        
        // approveLoan
        Route::post('/loan/{id}/approve', [LoanOfferController::class, 'approveLoan'])->name('loans.approve');
        // editLoan
        //  Route::get('/loan/{id}/edit2', [LoanOfferController::class, 'editLoan'])->name('loans.loanedit2');
         Route::get('/loan/{id}/edit', [LoanOfferController::class, 'editLoan'])->name('loans.loanedit');

         Route::post('/loan/{id}/editsave', [LoanOfferController::class, 'saveLoanEdit'])->name('loans.saveloanedit');


        



        
        
        Route::get('/add-clients', [ClientController::class, 'createClient'])->name('clients.add');
        Route::post('/store-clients', [ClientController::class, 'store'])->name('clients.store');

        Route::post('/store-clients-photo', [ClientController::class, 'store'])->name('clients.upload'); 

 
        
        
        // Loans
        Route::apiResource('loan-applications', LoanApplicationController::class);
        Route::apiResource('loan-offers', LoanOfferController::class);
        
        // loan Plans
        Route::get('/loanplans', [LoanOfferController::class, 'allplans'])->name('loan-plans');
        // Add
         Route::get('/adddloanplans', [LoanOfferController::class, 'addplan'])->name('add-loan-plans');
         // Add
         Route::post('/addloanplans', [LoanOfferController::class, 'createplan'])->name('create-loan-plan');

        //  admin.clients.fines.store
        // Route::post('/addclientfine', [LoanOfferController::class, 'storeClientFine'])->name('clients.fines.store');
        // Route::post('/addclientfine/{clientId}', [LoanOfferController::class, 'storeClientFine'])->name('clients.fines.store');

        // Route::post('/client/{clientId}/fine', [LoanOfferController::class, 'storeClientFine'])->name('clients.fines.store');


         
        //  editplan
        Route::get('/editplan', [LoanOfferController::class, 'editplan'])->name('edit-loan-plans');
        
        Route::get('/loan-plans/{id}/edit', [LoanOfferController::class, 'editplan'])->name('loan-plans.edit');
        Route::put('/loan-plans/{id}', [LoanOfferController::class, 'updateNow'])->name('loan-plans.update');
        Route::delete('/loan-plans/{id}', [LoanOfferController::class, 'destroyNow'])->name('loan-plans.destroy');
        
        // all loans
        Route::get('/all-loans', [LoanOfferController::class, 'all_loans'])->name('all-loans');
        // paid Loans
        Route::get('/paidLoans', [LoanOfferController::class, 'paidLoans'])->name('paidLoans');
        
        // pending loans
        Route::get('/pendingLoans', [LoanOfferController::class, 'pendingLoans'])->name('loan-pendingLoans');
        // rejected loans
        Route::get('/rejectedLoans', [LoanOfferController::class, 'rejectedLoans'])->name('loan-loanrejectedLoans');
        // Due loans
        Route::get('/dueloans', [LoanOfferController::class, 'dueLoans'])->name('loans-due');
        // running loans 
        Route::get('/runningLoans', [LoanOfferController::class, 'runningLoans'])->name('loan-runningLoans');
        
        // Store Fine Route
        // Route::post('/admin/clients/{client}/fines', [LoanOfferController::class, 'storeClientFine'])->name('clients.fines.store');
        // Route::post('/admin/clients/{client}/fines', [LoanOfferController::class, 'storeClientFine'])->name('clients.fines.store');
        Route::post('/clients/{client}/fines', [LoanOfferController::class, 'storeClientFine'])->name('clients.fines.store');

        // Store Collateral Route
        Route::post('/admin/clients/{clientId}/collaterals', [LoanOfferController::class, 'storeClientCollateral'])->name('clients.collaterals.store');

        // Fetch Collaterals List
        Route::get('/admin/clients/{clientId}/collaterals/list', [LoanOfferController::class, 'collateralsList'])->name('clients.collaterals.list');


        // Fetch Fines List via AJAX
        Route::get('/admin/clients/{client}/fines/list', [LoanOfferController::class, 'finesList'])->name('clients.fines.list');




        Route::get('/', [DashboardController::class, 'dashboard'])->name('dashboard');
        Route::get('settings', [DashboardController::class, 'settings'])->name('settings');
        Route::post('settings', [DashboardController::class, 'settingsUpdate']);
        Route::post('settings-password', [DashboardController::class, 'settingsPasswordUpdate'])->name('settings-password');

        Route::group(['prefix' => 'pages', 'as' => 'pages.'], function () {
            Route::get('terms-and-conditions', [PageController::class, 'termsAndConditions'])->name('terms-and-conditions');
            Route::post('terms-and-conditions', [PageController::class, 'termsAndConditionsUpdate']);

            Route::get('privacy-policy', [PageController::class, 'privacyPolicy'])->name('privacy-policy');
            Route::post('privacy-policy', [PageController::class, 'privacyPolicyUpdate']);

            Route::get('about-us', [PageController::class, 'aboutUs'])->name('about-us');
            Route::post('about-us', [PageController::class, 'aboutUsUpdate']);

            Route::get('social-media/fetch', [SocialMediaController::class, 'fetch'])->name('social-media.fetch');
            Route::get('social-media/status-update', [SocialMediaController::class, 'socialMediaStatusUpdate'])->name('social-media.status-update');
            Route::resource('social-media', SocialMediaController::class);
        });

        Route::group(['prefix' => 'contact', 'as' => 'contact.'], function () {
            Route::get('list', [ContactMessageController::class, 'list'])->name('list');
            Route::post('delete', [ContactMessageController::class, 'destroy'])->name('delete');
            Route::get('view/{id}', [ContactMessageController::class, 'view'])->name('view');
            Route::post('update/{id}', [ContactMessageController::class, 'update'])->name('update');
            Route::post('send-mail/{id}', [ContactMessageController::class, 'sendMail'])->name('send-mail');
        });


        Route::group(['prefix' => 'landing-settings', 'as' => 'landing-settings.'], function () {
              Route::get('get-landing-information', [LandingPageSettingsController::class, 'getLandingPageInformation'])->name('get-landing-information');
              Route::put('set-landing-information', [LandingPageSettingsController::class, 'updateLandingPageInformation'])->name('set-landing-information');
              Route::delete('delete-landing-information/{page}/{id}', [LandingPageSettingsController::class, 'landingPageInformationDelete'])->name('delete-landing-information');
              Route::get('status-landing-information/{page}/{id}', [LandingPageSettingsController::class, 'landingPageStatusUpdate'])->name('landing-status-change');
              Route::put('set-landing-title-status', [LandingPageSettingsController::class, 'landingPageTitleAndStatus'])->name('set-landing-title-status');
        });

        Route::group(['prefix' => 'business-settings', 'as' => 'business-settings.'], function () {
            Route::get('business-setup', [BusinessSettingsController::class, 'businessIndex'])->name('business-setup');
            Route::post('update-setup', [BusinessSettingsController::class, 'businessSetup'])->name('update-setup');

            Route::get('payment-method', [BusinessSettingsController::class, 'paymentIndex'])->name('payment-method');
            Route::post('payment-method-update', [BusinessSettingsController::class, 'paymentConfigUpdate'])->name('payment-method-update');

            Route::get('sms-module', [SMSModuleController::class, 'smsIndex'])->name('sms-module');
            Route::post('sms-module-update', [SMSModuleController::class, 'smsConfigUpdate'])->name('sms-module-update');

            Route::get('mail-config', [BusinessSettingsController::class, 'mailConfigIndex'])->name('mail_config');
            Route::get('send-mail-index', [BusinessSettingsController::class, 'testMailIndex'])->name('send_mail_index');
            Route::post('mail-config-update', [BusinessSettingsController::class, 'mailConfigUpdate'])->name('mail_config_update');
            Route::post('mail-config-status', [BusinessSettingsController::class, 'mailConfigStatus'])->name('mail_config_status');
            Route::get('mail-send', [BusinessSettingsController::class, 'sendMail'])->name('send_mail');

            Route::get('charge-setup', [BusinessSettingsController::class, 'chargeSetupIndex'])->name('charge-setup');
            Route::put('charge-setup', [BusinessSettingsController::class, 'chargeSetupUpdate']);

            Route::get('app-settings', [BusinessSettingsController::class, 'appSettings'])->name('app_settings');
            Route::get('app-setting-update', [BusinessSettingsController::class, 'appSettingUpdate'])->name('app_setting_update');

            Route::get('recaptcha', [BusinessSettingsController::class, 'recaptchaIndex'])->name('recaptcha_index');
            Route::post('recaptcha-update', [BusinessSettingsController::class, 'recaptchaUpdate'])->name('recaptcha_update');

            Route::get('fcm-index', [BusinessSettingsController::class, 'fcmIndex'])->name('fcm-index');
            Route::post('update-fcm', [BusinessSettingsController::class, 'updateFcm'])->name('update-fcm');
            Route::post('update-fcm-messages', [BusinessSettingsController::class, 'updateFcmMessages'])->name('update-fcm-messages');

            Route::group(['prefix' => 'language', 'as' => 'language.', 'middleware' => []], function () {
                Route::get('', [LanguageController::class, 'index'])->name('index');
                Route::post('add-new', [LanguageController::class, 'store'])->name('add-new');
                Route::get('update-status', [LanguageController::class, 'updateStatus'])->name('update-status');
                Route::get('update-default-status', [LanguageController::class, 'updateDefaultStatus'])->name('update-default-status');
                Route::post('update', [LanguageController::class, 'update'])->name('update');
                Route::get('translate/{lang}', [LanguageController::class, 'translate'])->name('translate');
                Route::post('translate-submit/{lang}', [LanguageController::class, 'translateSubmit'])->name('translate-submit');
                Route::post('remove-key/{lang}', [LanguageController::class, 'translateKeyRemove'])->name('remove-key');
                Route::get('delete/{lang}', [LanguageController::class, 'delete'])->name('delete');
            });

            Route::get('otp-setup', [BusinessSettingsController::class, 'otpSetup'])->name('otp_setup_index');
            Route::post('otp-setup-update', [BusinessSettingsController::class, 'otpSetupUpdate'])->name('otp_setup_update');

            Route::get('system-feature', [BusinessSettingsController::class, 'systemFeature'])->name('system_feature');
            Route::post('system-feature-update', [BusinessSettingsController::class, 'systemFeatureUpdate'])->name('system_feature_update');

            Route::get('customer-transaction-limits', [BusinessSettingsController::class, 'customerTransactionLimitsIndex'])->name('customer_transaction_limits');
            Route::get('agent-transaction-limits', [BusinessSettingsController::class, 'agentTransactionLimitsIndex'])->name('agent_transaction_limits');
            Route::post('transaction-limits/{transaction_type}', [BusinessSettingsController::class, 'transactionLimitsUpdate'])->name('transaction_limits_update');
        });

        Route::group(['prefix' => 'addon', 'as' => 'addon.'], function () {
            Route::get('/', [SystemAddonController::class, 'index'])->name('index');
            Route::post('publish', [SystemAddonController::class, 'publish'])->name('publish');
            Route::post('activation', [SystemAddonController::class, 'activation'])->name('activation');
            Route::post('upload', [SystemAddonController::class, 'upload'])->name('upload');
            Route::post('delete', [SystemAddonController::class, 'deleteAddon'])->name('delete');
        });

        Route::group(['prefix' => 'merchant-config', 'as' => 'merchant-config.'], function () {
            Route::get('merchant-payment-otp', [BusinessSettingsController::class, 'merchantPaymentOtpIndex'])->name('merchant-payment-otp');
            Route::post('merchant-payment-otp-verification-update', [BusinessSettingsController::class, 'merchantPaymentOtpUpdate'])->name('merchant-payment-otp-verification-update');
            Route::get('settings', [BusinessSettingsController::class, 'merchantSettingsIndex'])->name('settings');
            Route::post('settings-update', [BusinessSettingsController::class, 'merchantSettingUpdate'])->name('settings-update');
        });

        Route::get('linked-website', [BusinessSettingsController::class, 'linkedWebsite'])->name('linked-website');
        Route::post('linked-website', [BusinessSettingsController::class, 'linkedWebsiteAdd']);
        Route::get('linked-website/update/{id}', [BusinessSettingsController::class, 'linkedWebsiteEdit'])->name('linked-website-edit');
        Route::put('linked-website', [BusinessSettingsController::class, 'linkedWebsiteUpdate']);
        Route::get('linked-website/status/{id}', [BusinessSettingsController::class, 'linkedWebsiteStatus'])->name('linked-website-status');
        Route::get('linked-website-delete', [BusinessSettingsController::class, 'linkedWebsiteDelete'])->name('linked-website-delete');

        Route::group(['prefix' => 'notification', 'as' => 'notification.'], function () {
            Route::get('add-new', [NotificationController::class, 'index'])->name('add-new');
            Route::post('store', [NotificationController::class, 'store'])->name('store');
            Route::get('edit/{id}', [NotificationController::class, 'edit'])->name('edit');
            Route::post('update/{id}', [NotificationController::class, 'update'])->name('update');
            Route::get('status/{id}/{status}', [NotificationController::class, 'status'])->name('status');
            Route::delete('delete/{id}', [NotificationController::class, 'delete'])->name('delete');
        });

        Route::group(['prefix' => 'banner', 'as' => 'banner.'], function () {
            Route::get('add-new', [BannerController::class, 'index'])->name('index');
            Route::post('store', [BannerController::class, 'store'])->name('store');
            Route::get('edit/{id}', [BannerController::class, 'edit'])->name('edit');
            Route::post('update/{id}', [BannerController::class, 'update'])->name('update');
            Route::get('status/{id}', [BannerController::class, 'status'])->name('status');
            Route::get('delete/{id}', [BannerController::class, 'delete'])->name('delete');
        });

        Route::group(['prefix' => 'bonus', 'as' => 'bonus.'], function () {
            Route::get('add-new', [BonusController::class, 'index'])->name('index');
            Route::post('store', [BonusController::class, 'store'])->name('store');
            Route::get('edit/{id}', [BonusController::class, 'edit'])->name('edit');
            Route::put('update/{id}', [BonusController::class, 'update'])->name('update');
            Route::get('status/{id}', [BonusController::class, 'status'])->name('status');
            Route::post('delete', [BonusController::class, 'delete'])->name('delete');
        });

        Route::group(['prefix' => 'helpTopic', 'as' => 'helpTopic.'], function () {
            Route::get('list', [HelpTopicController::class, 'list'])->name('list');
            Route::post('add-new', [HelpTopicController::class, 'store'])->name('add-new');
            Route::get('status/{id}', [HelpTopicController::class, 'status']);
            Route::get('edit/{id}', [HelpTopicController::class, 'edit']);
            Route::post('update/{id}', [HelpTopicController::class, 'update']);
            Route::post('delete', [HelpTopicController::class, 'destroy'])->name('delete');
        });

        Route::group(['prefix' => 'customer', 'as' => 'customer.', 'middleware' => []], function () {
            Route::get('add', [CustomerController::class, 'index'])->name('add');
            Route::post('store', [CustomerController::class, 'store'])->name('store');
            Route::get('list', [CustomerController::class, 'customerList'])->name('list');
            Route::get('view/{user_id}', [CustomerController::class, 'view'])->name('view');
            Route::get('edit/{id}', [CustomerController::class, 'edit'])->name('edit');
            Route::post('update/{id}', [CustomerController::class, 'update'])->name('update');
            Route::get('transaction/{user_id}', [CustomerController::class, 'transaction'])->name('transaction');
            Route::get('log/{user_id}', [CustomerController::class, 'log'])->name('log');
            Route::post('search', [CustomerController::class, 'search'])->name('search');
            Route::get('status/{id}', [CustomerController::class, 'status'])->name('status');
            Route::get('kyc-requests', [CustomerController::class, 'getKycRequest'])->name('kyc_requests');
            Route::get('kyc-status-update/{id}/{status}', [CustomerController::class, 'updateKycStatus'])->name('kyc_status_update');
        });
        Route::get('admin/transaction/{user_id}', [AdminController::class, 'transaction'])->name('admin.transaction');
        Route::get('admin/view/{user_id}', [AdminController::class, 'view'])->name('admin.view');

        Route::group(['prefix' => 'agent', 'as' => 'agent.'], function () {
            Route::get('add', [AgentController::class, 'index'])->name('add');
            Route::post('store', [AgentController::class, 'store'])->name('store');
            Route::get('list', [AgentController::class, 'list'])->name('list');
            Route::get('view/{user_id}', [CustomerController::class, 'view'])->name('view');
            Route::get('transaction/{user_id}', [CustomerController::class, 'transaction'])->name('transaction');
            Route::get('log/{user_id}', [CustomerController::class, 'log'])->name('log');
            Route::get('edit/{id}', [AgentController::class, 'edit'])->name('edit');
            Route::post('update/{id}', [AgentController::class, 'update'])->name('update');
            Route::post('search', [AgentController::class, 'search'])->name('search');
            Route::get('status/{id}', [AgentController::class, 'status'])->name('status');
            Route::get('kyc-requests', [AgentController::class, 'getKycRequest'])->name('kyc_requests');
            Route::get('kyc-status-update/{id}/{status}', [AgentController::class, 'updateKycStatus'])->name('kyc_status_update');
        });

        Route::group(['prefix' => 'merchant', 'as' => 'merchant.'], function () {
            Route::get('add', [MerchantController::class, 'index'])->name('add');
            Route::post('store', [MerchantController::class, 'store'])->name('store');
            Route::get('list', [MerchantController::class, 'list'])->name('list');
            Route::get('view/{user_id}', [MerchantController::class, 'view'])->name('view');
            Route::get('transaction/{user_id}', [MerchantController::class, 'transaction'])->name('transaction');
            Route::get('edit/{id}', [MerchantController::class, 'edit'])->name('edit');
            Route::post('update/{id}', [MerchantController::class, 'update'])->name('update');
            Route::post('search', [MerchantController::class, 'search'])->name('search');
            Route::get('status/{id}', [MerchantController::class, 'status'])->name('status');

        });

        Route::group(['prefix' => 'user', 'as' => 'user.'], function () {
            Route::get('log', [UserController::class, 'log'])->name('log');
        });

        Route::group(['prefix' => 'transaction', 'as' => 'transaction.'], function () {
            Route::get('index', [TransactionController::class, 'index'])->name('index');
            Route::post('store', [TransactionController::class, 'store'])->name('store');

            Route::get('request-money', [TransactionController::class, 'requestMoney'])->name('request_money');
            Route::get('request-money-status/{slug}', [TransactionController::class, 'requestMoneyStatusChange'])->name('request_money_status_change');
            Route::get('get-user', [TransferController::class, 'getUser'])->name('get_user');
        });

        Route::group(['prefix' => 'expense', 'as' => 'expense.'], function () {
            Route::get('index', [ExpenseController::class, 'index'])->name('index');
             Route::get('expenses', [ExpenseController::class, 'index'])->name('expenses');
             Route::get('expenses-create', [ExpenseController::class, 'create'])->name('create');
             Route::post('expenses-store', [ExpenseController::class, 'store'])->name('store');
            //  Route::post('/expenses/del/{id}', [ExpenseController::class, 'distroy'])->name('delete');
            //  Route::resource('expenses', ExpenseController::class);
            
           

        });
        Route::resource('expenses', ExpenseController::class);
        

        Route::group(['prefix' => 'withdraw', 'as' => 'withdraw.'], function () {
            Route::get('requests', [WithdrawController::class, 'index'])->name('requests');
            Route::get('status-update', [WithdrawController::class, 'status_update'])->name('status_update');
            Route::get('download', [WithdrawController::class, 'download'])->name('download');
        });

        Route::group(['prefix' => 'transfer', 'as' => 'transfer.'], function () {
            Route::get('index', [TransferController::class, 'index'])->name('index');
            Route::post('store', [TransferController::class, 'store'])->name('store');
            Route::get('get-user', [TransferController::class, 'getUser'])->name('get_user');
        });

        Route::group(['prefix' => 'emoney', 'as' => 'emoney.'], function () {
            Route::get('index', [EMoneyController::class, 'index'])->name('index');
            Route::post('store', [EMoneyController::class, 'store'])->name('store');

        });

        Route::group(['prefix' => 'purpose', 'as' => 'purpose.'], function () {
            Route::get('index', [PurposeController::class, 'index'])->name('index');
            Route::post('store', [PurposeController::class, 'store'])->name('store');
            Route::get('edit/{id}', [PurposeController::class, 'edit'])->name('edit');
            Route::post('update', [PurposeController::class, 'update'])->name('update');
            Route::get('delete/{id}', [PurposeController::class, 'delete'])->name('delete');

        });

        Route::group(['prefix' => 'withdrawal-methods', 'as' => 'withdrawal_methods.'], function () {
            Route::get('add-method', [WithdrawalController::class, 'addMethod'])->name('add');
            Route::post('store', [WithdrawalController::class, 'storeMethod'])->name('store');
            Route::post('delete', [WithdrawalController::class, 'deleteMethod'])->name('delete');

        });
    });

});


