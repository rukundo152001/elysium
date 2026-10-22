<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\AdminReportController;
use App\Http\Controllers\StaffDashboardController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AdminPatientController;
use App\Http\Controllers\Admin\AdminServiceController;
use App\Http\Controllers\Admin\AdminLocationController;
use App\Http\Controllers\PatientRegistrationController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminInsuranceController;
use App\Http\Controllers\InsuranceSummaryController;

use App\Http\Controllers\AuthController;

    Route::get('/', [AuthController::class, 'index'])->name('homepage');
    
    Route::post('/', [AuthController::class, 'login'])->name('login');

Route::middleware('auth')->group(function () {
    
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');






    ///////////////////////////////////////////PATIENT INVOICE/////////

Route::prefix('invoice')->name('patients.')->group(function () 
{
    Route::get('/', [InvoiceController::class, 'ptInvoice'])->name('invoice');



Route::get('/insurance-invoice', [InvoiceController::class, 'insuranceInvoice'
])->name('insurance-invoice');

});

////////////////////////////////////////




// Patient Detail Route
    






Route::prefix('staff')->name('staff.')->group(function () 
{


    Route::get('/', [StaffDashboardController::class, 'index'])->name('index');

    ////PATIENTS ROUTES////
    Route::prefix('patients')->name('patients.')->group(function () 
{
    Route::get('/create',[PatientRegistrationController::class,'index'])->name('create');
    
    Route::post('/store',[PatientRegistrationController::class,'store'])->name('store');

    Route::get('/details/{id}', [PatientRegistrationController::class, 'show'])->name('details');
    
    
});
    

   
});
    



/**
 * Admin Address Management Routes
  
 */


    
Route::prefix('admin')->name('admin.')->group(function () 
{
    Route::get('/', [AdminDashboardController::class, 'index'])->name('index');

    ///USER MANAGEMENT ROUTES///
    Route::prefix('users')->name('users.')->group(function () {

        Route::get('/', [AdminUserController::class, 'index'])->name('index');
        Route::get('/create', [AdminUserController::class, 'create'])->name('create');
        Route::post('/', [AdminUserController::class, 'store'])->name('store');

        // FIXED: changed {service} → {user}
        Route::get('/{user}/edit', [AdminUserController::class, 'edit'])->name('edit');

        Route::get('/{user}', [AdminUserController::class, 'show'])->name('show');

        // FIXED: use PUT instead of POST
        Route::put('/{user}', [AdminUserController::class, 'update'])->name('update');

        // FIXED: delete a user
        Route::delete('/{user}', [AdminUserController::class, 'destroy'])->name('destroy');
    });


    

   
    ///LOCATIONS MANAGEMENT ROUTES///

    Route::prefix('locations')->name('locations.')->group(function () 
    {

    Route::get('/', [AdminLocationController::class, 'index'])->name('index');

    // create & store (level param)
    Route::get('/create/{level?}', [AdminLocationController::class, 'create'])->name('create');
    Route::post('/store', [AdminLocationController::class, 'store'])->name('store');

    // edit & update
    Route::get('/{level}/{id}/edit', [AdminLocationController::class, 'edit'])->name('edit');
    Route::put('/{level}/{id}', [AdminLocationController::class, 'update'])->name('update');

    // delete
    Route::delete('/{level}/{id}', [AdminLocationController::class, 'destroy'])->name('destroy');

    // NEW: View hierarchy page
    Route::get('/{level}/{id}/view', [AdminLocationController::class, 'view'])->name('view');
    

    });
    


    //SERVICES MANAGEMENT ROUTES


    Route::prefix('services')->name('services.')->group(function () 
    {
    Route::get('', [AdminServiceController::class, 'index'])->name('index');
    Route::get('/create', [AdminServiceController::class, 'create'])->name('create');
    Route::post('', [AdminServiceController::class, 'store'])->name('store');
    Route::get('/{service}/edit', [AdminServiceController::class, 'edit'])->name('edit');
    Route::put('/{service}', [AdminServiceController::class, 'update'])->name('update');
    });



    ///INSURANCES MANAGEMENT ROUTES///
    Route::prefix('insurances')->name('insurances.')->group(function () 
    {
    Route::get('/insurance', [AdminInsuranceController::class, 'index'])->name('index');
    Route::get('/insurance/create', [AdminInsuranceController::class, 'create'])->name('create');
    Route::post('/insurance/store', [AdminInsuranceController::class, 'store'])->name('store');
    Route::get('/insurance/{id}/edit', [AdminInsuranceController::class, 'edit'])->name('edit');
    Route::put('/insurance/{id}/update', [AdminInsuranceController::class, 'update'])->name('update');
    Route::delete('/insurance/{id}/delete', [AdminInsuranceController::class, 'destroy'])->name('destroy');

    });


   
        ///PATIENTS MANAGEMENT ROUTES///

        Route::prefix('patients')->name('patients.')->group(function () 
        {
        
            Route::get('/', [AdminPatientController::class, 'index'])->name('index');

            Route::get('/{patientId}/edit', [AdminPatientController::class, 'edit'])->name('edit');

            Route::put('/{id}/update', [AdminPatientController::class, 'update'])->name('update');

            Route::get('/details/{id}', [AdminPatientController::class, 'show'])->name('details');
        
        });


        /*REPORTS MANAGEMENT ROUTES///

        Route::prefix('reports')->name('reports.')->group(function () 
        { 
            Route::get('/', [AdminReportController::class, 'index'])->name('index');

            Route::get('/preview', [AdminReportController::class, 'preview'])->name('preview');

            Route::get('/custom-date-range',[AdminReportController::class,'customDateRange'])->name('custom-date-range');

            Route::get('/custom-preview',[AdminReportController::class,'customPreview'])->name('custom-preview');

            Route::get('/print',[AdminReportController::class,'printReport'])->name('print-report');


        }); */

        //////////////REPORTS DEMO////////////////


        Route::prefix('reports')->name('reports.')->group(function ()  {
    Route::get('/', [InsuranceSummaryController::class, 'index'])->name('index');

    Route::post('/generate', [InsuranceSummaryController::class, 'generate'])->name('insurance-summary.generate');
    Route::get('/{batch}/review', [InsuranceSummaryController::class, 'review'])->name('review');
    Route::post('/{batch}/save-draft', [InsuranceSummaryController::class, 'saveDraft'])->name('save-draft');
    Route::post('/{batch}/items/{item}/verify', [InsuranceSummaryController::class, 'verifyItem'])->name('verify-item');
    Route::delete('/{batch}/items/{item}', [InsuranceSummaryController::class, 'deleteItem'])->name('delete-item');
    Route::post('/{batch}/finalize-download', [InsuranceSummaryController::class, 'finalizeAndDownload'])->name('finalize-download');
    Route::get('/{batch}/download-finalized', [InsuranceSummaryController::class, 'downloadFinalized'])->name('download-finalized');
    Route::post('/{batch}/global-bill', [InsuranceSummaryController::class, 'uploadGlobalBill'])->name('upload-global-bill');
    Route::get('/{batch}/global-bill', [InsuranceSummaryController::class, 'downloadGlobalBill'])->name('download-global-bill');
});



});



Route::post('logout', [AuthController::class, 'destroy'])
       ->name('logout');

});












require __DIR__.'/auth.php';
