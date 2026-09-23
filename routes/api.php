<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\FraudCheckController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\PurchaseController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\SaleController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\StockController;
use App\Http\Controllers\Api\SupplierController;
use App\Http\Controllers\Api\IncomeExpenseController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Web\EpbxCallController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login',    [AuthController::class, 'login']);
Route::post('/epbx/webhook', [EpbxCallController::class, 'handleWebhook']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me',      [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/profile',           [ProfileController::class, 'show']);
    Route::post('/profile',          [ProfileController::class, 'update']);
    Route::post('/profile/password', [ProfileController::class, 'changePassword']);

    Route::get('/dashboard/stats', [DashboardController::class, 'stats']);

    // Categories (multipart support for image)
    Route::get('/categories',           [CategoryController::class, 'index']);
    Route::post('/categories',          [CategoryController::class, 'store']);
    Route::post('/categories/{category}', [CategoryController::class, 'update']);
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy']);

    // Products (multipart support for image)
    Route::get('/products',                          [ProductController::class, 'index']);
    Route::post('/products',                         [ProductController::class, 'store']);
    Route::post('/products/{product}',               [ProductController::class, 'update']);
    Route::post('/products/{product}/barcode',       [ProductController::class, 'generateBarcode']);
    Route::delete('/products/{product}',             [ProductController::class, 'destroy']);

    // Fraud checker (external API proxy)
    Route::get('/fraud-check', [FraudCheckController::class, 'check']);

    // Suppliers
    Route::get('/suppliers',                      [SupplierController::class, 'index']);
    Route::post('/suppliers',                     [SupplierController::class, 'store']);
    Route::put('/suppliers/{supplier}',           [SupplierController::class, 'update']);
    Route::delete('/suppliers/{supplier}',        [SupplierController::class, 'destroy']);
    Route::get('/suppliers/{supplier}/report',    [SupplierController::class, 'report']);

    // Purchases
    Route::get('/purchases',                      [PurchaseController::class, 'index']);
    Route::post('/purchases',                     [PurchaseController::class, 'store']);
    Route::get('/purchases/{purchase}',           [PurchaseController::class, 'show']);
    Route::put('/purchases/{purchase}',           [PurchaseController::class, 'update']);
    Route::patch('/purchases/{purchase}/payment', [PurchaseController::class, 'updatePayment']);

    // Income / Expense
    Route::get('/finance/report',                    [IncomeExpenseController::class, 'report']);
    Route::get('/finance/categories',                [IncomeExpenseController::class, 'categories']);
    Route::post('/finance/categories',               [IncomeExpenseController::class, 'storeCategory']);
    Route::put('/finance/categories/{category}',     [IncomeExpenseController::class, 'updateCategory']);
    Route::delete('/finance/categories/{category}',  [IncomeExpenseController::class, 'destroyCategory']);
    Route::get('/finance',                           [IncomeExpenseController::class, 'index']);
    Route::post('/finance',                          [IncomeExpenseController::class, 'store']);
    Route::put('/finance/{incomeExpense}',           [IncomeExpenseController::class, 'update']);
    Route::delete('/finance/{incomeExpense}',        [IncomeExpenseController::class, 'destroy']);

    // Stock
    Route::get('/stock',           [StockController::class, 'index']);
    Route::get('/stock/summary',   [StockController::class, 'summary']);
    Route::get('/stock/movements', [StockController::class, 'movements']);

    // Customers
    Route::get('/customers',                        [CustomerController::class, 'index']);
    Route::post('/customers',                       [CustomerController::class, 'store']);
    Route::post('/customers/{customer}',            [CustomerController::class, 'update']);
    Route::delete('/customers/{customer}',          [CustomerController::class, 'destroy']);
    Route::get('/customers/{customer}/report',      [CustomerController::class, 'report']);

    // Sales
    Route::get('/sales',                        [SaleController::class, 'index']);
    Route::post('/sales',                       [SaleController::class, 'store']);
    Route::get('/sales/{sale}',                 [SaleController::class, 'show']);
    Route::post('/sales/{sale}',                [SaleController::class, 'update']);
    Route::patch('/sales/{sale}/order-status',  [SaleController::class, 'updateStatus']);

    // User management (vendor-owner manages their cashier/staff)
    Route::get('/users',          [UserController::class, 'index']);
    Route::post('/users',         [UserController::class, 'store']);
    Route::put('/users/{user}',   [UserController::class, 'update']);
    Route::delete('/users/{user}',[UserController::class, 'destroy']);

    // Super-admin panel
    Route::prefix('admin')->middleware('role:super-admin')->group(function () {
        Route::get('/stats',                       [AdminController::class, 'stats']);
        Route::get('/vendors',                     [AdminController::class, 'vendorIndex']);
        Route::post('/vendors',                    [AdminController::class, 'vendorStore']);
        Route::put('/vendors/{vendor}',            [AdminController::class, 'vendorUpdate']);
        Route::delete('/vendors/{vendor}',         [AdminController::class, 'vendorDestroy']);
        Route::get('/vendors/{vendor}/report',     [AdminController::class, 'vendorReport']);
        Route::get('/users',                       [AdminController::class, 'userIndex']);
    });

    // Reports
    Route::prefix('reports')->group(function () {
        Route::get('/overview',       [ReportController::class, 'overview']);
        Route::get('/sales-summary',  [ReportController::class, 'salesSummary']);
        Route::get('/top-products',   [ReportController::class, 'topProducts']);
        Route::get('/payment-status', [ReportController::class, 'paymentStatus']);
    });
});
