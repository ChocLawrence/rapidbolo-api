<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\StatusController;
use App\Http\Controllers\SliderController;
use App\Http\Controllers\PaymentMethodController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\RequestController;
use App\Http\Controllers\RatingController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\SubscriberController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\TransactionTypeController;
use App\Http\Controllers\VerificationController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\MailController;
use App\Http\Controllers\EmailVerificationController;

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
Route::middleware(['cors'])->group(function () {


  Route::get('/', [UserController::class,'root'])->name('root');
  Route::post('login', [UserController::class,'login']);
  Route::post('signup', [UserController::class,'signup']);

  Route::post('forgot-password', [UserController::class, 'forgotPassword']);
  Route::post('reset-password', [UserController::class, 'reset']);

  //services without authentication
  Route::get('services',[ServiceController::class, 'getServices']);
  Route::get('services/{id}',[ServiceController::class, 'getService']);
  //Route::post('services/view/{id}',[ServiceController::class, 'updateServiceCount']);

  //sliders without authentication
  Route::get('sliders',[SliderController::class, 'getSliders']);
  //Route::get('sliders/{id}',[SliderController::class, 'getSlider']);

  //categories without authentication
  Route::get('categories',[CategoryController::class, 'getCategories']);
  Route::get('categories/{id}',[CategoryController::class, 'getCategory']);

  //tags without authentication
  Route::get('tags',[TagController::class, 'getTags']);
  Route::get('tags/{id}',[TagController::class, 'getTag']);

  //subscribers without authentication
  Route::post('subscribers',[SubscriberController::class,'addSubscriber']);

  //feedbacks without authentication
  Route::post('feedbacks',[FeedbackController::class,'addFeedback']);

  Route::middleware(['auth:sanctum'])->group(function () {   
    Route::post('email/verification-notification', [EmailVerificationController::class, 'sendVerificationEmail']);
    Route::get('verify-email/{id}/{hash}', [EmailVerificationController::class, 'verify'])->name('verification.verify');
  });

  Route::middleware(['auth:sanctum', 'verified'])->group(function () {   

    Route::post('logout', [UserController::class, 'logout']);

    //settings
    Route::post('users/update',[UserController::class,'updateUser']);
    Route::get('users/{id}',[UserController::class, 'getUser']);

    //general for all logged in users.

    //requests
    // Route::get('requests',[RequestController::class, 'getRequests']);
    // Route::get('requests/{id}',[RequestController::class, 'getRequest']);
    // Route::put('requests/{id}',[RequestController::class,'updateRequest']);
    // Route::delete('requests/{id}',[RequestController::class,'deleteRequest']);

    //chats
    // Route::get('chats',[ChatController::class, 'getChats']);
    // Route::get('chats/{id}',[ChatController::class, 'getChat']);
    // Route::post('chats',[ChatController::class,'addChat']);
    // Route::delete('chats/{id}',[ChatController::class,'deleteChat']);

    //notifications
    Route::get('notifications',[NotificationController::class, 'getNotifications']);
    Route::get('notifications/{id}',[NotificationController::class, 'getNotification']);
    Route::post('notifications',[NotificationController::class,'addNotification']);
    Route::put('notifications/mark-as-read/{id}',[NotificationController::class,'markAsRead']);
    Route::put('notifications/mark-as-unread/{id}',[NotificationController::class,'markAsUnRead']);
    Route::delete('notifications/{id}',[NotificationController::class,'deleteNotification']);


    Route::get('users',[UserController::class, 'getUsers']);


    //verification
    Route::post('verifications',[VerificationController::class,'addVerification']);

    //transactions
    Route::post('transactions',[TransactionController::class,'addTransaction']);

    //end general 

    //admin
    Route::middleware('role:admin')->group(function () {   
      Route::post('send-mail', [MailController::class, 'sendMail']);

      //users
      Route::delete('users/{id}',[UserController::class,'deleteUser']);

      //tags
      Route::post('tags',[TagController::class,'addTag']);
      Route::put('tags/{id}',[TagController::class,'updateTag']);
      Route::delete('tags/{id}',[TagController::class,'deleteTag']);

      //categories
      Route::post('categories',[CategoryController::class,'addCategory']);
      Route::post('categories/{id}',[CategoryController::class,'updateCategory']);
      Route::delete('categories/{id}',[CategoryController::class,'deleteCategory']);

      //services
      Route::post('services',[ServiceController::class,'addService']);
      Route::post('services/{id}',[ServiceController::class,'updateService']);
      Route::delete('services/{id}',[ServiceController::class,'deleteService']);

      //statuses
    //   Route::get('statuses',[StatusController::class, 'getStatuses']);
    //   Route::get('statuses/{id}',[StatusController::class, 'getStatus']);
    //   Route::put('statuses/{id}',[StatusController::class,'updateStatus']);
    //   Route::delete('statuses/{id}',[StatusController::class,'deleteStatus']);

      //plans
    //   Route::get('plans',[PlanController::class, 'getPlans']);
    //   Route::get('plans/{id}',[PlanController::class, 'getPlan']);
    //   Route::put('plans/{id}',[PlanController::class,'updatePlan']);
    //   Route::delete('plans/{id}',[PlanController::class,'deletePlan']);

      //subscriptions
    //   Route::get('subscriptions',[SubscriptionController::class, 'getSubscriptions']);
    //   Route::get('subscriptions/{id}',[SubscriptionController::class, 'getSubscription']);
    //   Route::put('subscriptions/{id}',[SubscriptionController::class,'updateSubscription']);
    //   Route::delete('subscriptions/{id}',[SubscriptionController::class,'deleteSubscription']);

      //transactiontypes
    //   Route::get('transactiontypes',[TransactionTypeController::class, 'getTransactionTypes']);
    //   Route::post('transactiontypes',[TransactionTypeController::class,'addTransactionType']);
    //   Route::put('transactiontypes/{id}',[TransactionTypeController::class,'updateTransactionType']);
    //   Route::delete('transactiontypes/{id}',[TransactionTypeController::class,'deleteTransactionType']);
 

      //transactions
    //   Route::get('transactions',[TransactionController::class, 'getTransactions']);
    //   Route::get('transactions/{id}',[TransactionController::class, 'getTransaction']);
    //   Route::put('transactions/{id}',[TransactionController::class,'updateTransaction']);
    //   Route::delete('transactions/{id}',[TransactionController::class,'deleteTransaction']);

      //verifications
    //   Route::get('verifications',[VerificationController::class, 'getVerifications']);
    //   Route::get('verifications/{id}',[VerificationController::class, 'getVerification']);
    //   Route::put('verifications/{id}',[VerificationController::class,'updateVerification']);
    //   Route::delete('verifications/{id}',[RequeVerificationControllerstController::class,'deleteVerficiation']);


      //subscribers
      Route::get('subscribers',[SubscriberController::class, 'getSubscribers']);
      Route::get('subscribers/{id}',[SubscriberController::class, 'getSubscriber']);
      Route::put('subscribers/{id}',[SubscriberController::class,'updateSubscriber']);
      Route::delete('subscribers/{id}',[SubscriberController::class,'deleteSubscriber']);

      //feedbacks
      Route::get('feedbacks',[FeedbackController::class, 'getFeedbacks']);
      Route::get('feedbacks/{id}',[FeedbackController::class, 'getFeedback']);
      Route::put('feedbacks/{id}',[FeedbackController::class,'updateFeedback']);
      Route::delete('feedbacks/{id}',[FeedbackController::class,'deleteFeedback']);


      //sliders
      Route::post('sliders',[SliderController::class,'addSlider']);
      Route::post('sliders/{id}',[SliderController::class,'updateSlider']);
      Route::delete('sliders/{id}',[SliderController::class,'deleteSlider']);

      //roles
      Route::get('roles',[RoleController::class, 'getRoles']);
      Route::get('roles/{id}',[RoleController::class, 'getRole']);
      Route::post('roles',[RoleController::class,'addRole']);
      Route::put('roles/{id}',[RoleController::class,'updateRole']);
      Route::delete('roles/{id}',[RoleController::class,'deleteRole']);

     
      //payment methods
    //   Route::get('paymentmethods',[PaymentMethodController::class, 'getPaymentMethods']);
    //   Route::post('paymentmethods',[PaymentMethodController::class,'addPaymentMethod']);
    //   Route::put('paymentmethods/{id}',[PaymentMethodController::class,'updatePaymentMethod']);
    //   Route::delete('paymentmethods/{id}',[PaymentMethodController::class,'deletePaymentMethod']);

      //ratings
    //   Route::get('ratings',[RatingController::class, 'getRatings']);
    //   Route::get('ratings/{id}',[RatingController::class, 'getRating']);
    //   Route::post('ratings',[RatingController::class,'addRating']);
    //   Route::put('ratings/{id}',[RatingController::class,'updateRating']);
    //   Route::delete('ratings/{id}',[RatingController::class,'deleteRating']);

   
    });

  
  });

});
 

