<?php

use Illuminate\Http\Demand;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\StatusController;
use App\Http\Controllers\StatusHistoryController;
use App\Http\Controllers\SliderController;
use App\Http\Controllers\PaymentMethodController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DemandController;
use App\Http\Controllers\RatingController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\SubscriberController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\TransactionTypeController;
use App\Http\Controllers\VerificationController;
use App\Http\Controllers\VerificationTypeController;
use App\Http\Controllers\FeedbackController; 
use App\Http\Controllers\UserActivityController;
use App\Http\Controllers\ColorController;
use App\Http\Controllers\LabelController;
use App\Http\Controllers\CountryController;
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

  //without authentication
  Route::get('/', [UserController::class,'root'])->name('root');
  Route::post('login', [UserController::class,'login']);
  Route::post('signup', [UserController::class,'signup']);

  Route::post('forgot-password', [UserController::class, 'forgotPassword']);
  Route::post('reset-password', [UserController::class, 'reset']);

  //services 
  Route::get('services',[ServiceController::class, 'getServices']);
  Route::get('services/{id}',[ServiceController::class, 'getService']);
  //Route::post('services/view/{id}',[ServiceController::class, 'updateServiceCount']);

  //sliders 
  Route::get('sliders',[SliderController::class, 'getSliders']);
  //Route::get('sliders/{id}',[SliderController::class, 'getSlider']);

  //categories 
  Route::get('categories',[CategoryController::class, 'getCategories']);
  Route::get('categories/{id}',[CategoryController::class, 'getCategory']);

  //tags 
  Route::get('tags',[TagController::class, 'getTags']);
  Route::get('tags/{id}',[TagController::class, 'getTag']);

  //subscribers 
  Route::post('subscribers',[SubscriberController::class,'addSubscriber']);

  //feedbacks 
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

    //statuses
    Route::get('statuses',[StatusController::class, 'getStatuses']);


    //statuses
    Route::get('stathis',[StatusHistoryController::class, 'getStatusHistories']); 
    Route::get('stathis/{id}',[StatusHistoryController::class, 'getStatusHistory']);  
    Route::post('stathis',[StatusHistoryController::class,'addStatusHistory']);

    //demands
    Route::get('demands',[DemandController::class, 'getDemands']);
    Route::get('demands/{id}',[DemandController::class, 'getDemand']);
    Route::post('demands',[DemandController::class,'addDemand']);
    Route::post('demands/accept/{id}',[DemandController::class,'acceptDemand']);
    Route::post('demands/trans/{id}',[DemandController::class,'transferDemand']);
    Route::post('demands/setst/{id}',[DemandController::class,'setStages']);
    Route::post('demands/dc/{id}',[DemandController::class,'demanderConfirmAmount']);
    Route::put('demands/pcj/{id}',[DemandController::class,'providerConfirmJob']);
    Route::put('demands/makepmt/{id}',[DemandController::class,'demanderMakePayment']);
    Route::put('demands/dcd/{id}',[DemandController::class,'demanderConfirmDelivery']);
    Route::put('demands/pcd/{id}',[DemandController::class,'providerConfirmDelivery']);
    Route::post('demands/{id}',[DemandController::class,'updateDemand']);
    Route::post('demands/cancel/{id}',[DemandController::class,'cancelDemand']);
    Route::delete('demands/{id}',[DemandController::class,'deleteDemand']);

    //chats
    Route::get('chats',[ChatController::class, 'getChats']);
    Route::get('chats/{id}',[ChatController::class, 'getChat']);
    Route::post('chats',[ChatController::class,'addChat']);
    Route::put('chats/read/{id}',[ChatController::class,'markAsRead']);
    Route::put('chats/uread/{id}',[ChatController::class,'markAsUnRead']);
    Route::delete('chats/{id}',[ChatController::class,'deleteChat']);

    //notifications
    Route::get('notifications',[NotificationController::class, 'getNotifications']);
    Route::get('notifications/{id}',[NotificationController::class, 'getNotification']);
    Route::post('notifications',[NotificationController::class,'addNotification']);
    Route::put('notifications/{id}',[NotificationController::class,'updateNotificationStatus']);
    Route::put('notifications/read/{id}',[NotificationController::class,'markAsRead']);
    Route::put('notifications/uread/{id}',[NotificationController::class,'markAsUnRead']);
    Route::delete('notifications/{id}',[NotificationController::class,'deleteNotification']);

    //verification
    Route::post('verifications',[VerificationController::class,'addVerification']);

    //subscriptions
    Route::get('subscriptions/{id}',[SubscriptionController::class, 'getSubscription']);
    Route::post('subscriptions',[SubscriptionController::class,'addSubscription']);

    //transactions
    Route::post('transactions',[TransactionController::class,'addTransaction']);
    Route::get('transactions/{id}',[TransactionController::class, 'getTransaction']);

    //ratings
    Route::get('ratings',[RatingController::class, 'getRatings']);
    Route::get('ratings/{id}',[RatingController::class, 'getRating']);
    Route::post('ratings',[RatingController::class,'addRating']);

    //plans
    Route::get('plans',[PlanController::class, 'getPlans']);

    //colors
    Route::get('colors',[ColorController::class, 'getColors']);
    Route::get('colors/{id}',[ColorController::class, 'getColor']);

    //labels
    Route::get('labels',[LabelController::class, 'getLabels']);
    Route::get('labels/{id}',[LabelController::class, 'getLabel']);

    //user activity
    Route::post('activity',[UserActivityController::class, 'addUserActivity']);

    //end general 


    //admin
    Route::middleware('role:admin')->group(function () {   
      Route::post('send-mail', [MailController::class, 'sendMail']);

      //users
      Route::get('users',[UserController::class, 'getUsers']);
      Route::post('users/ban/{id}',[UserController::class,'banUser']);
      Route::post('users/activate/{id}',[UserController::class,'activateUser']);
      Route::post('users/markdel/{id}',[UserController::class,'markUserDeletion']);
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
      Route::post('services/susp/{id}',[ServiceController::class,'suspendService']);
      Route::delete('services/{id}',[ServiceController::class,'deleteService']);

      //statuses
      Route::get('statuses/{id}',[StatusController::class, 'getStatus']);
      Route::post('statuses',[StatusController::class,'addStatus']);
      Route::put('statuses/{id}',[StatusController::class,'updateStatus']);
      Route::delete('statuses/{id}',[StatusController::class,'deleteStatus']);

      //plans
      Route::get('plans/{id}',[PlanController::class, 'getPlan']);
      Route::post('plans',[PlanController::class,'addPlan']);
      Route::put('plans/{id}',[PlanController::class,'updatePlan']);
      Route::delete('plans/{id}',[PlanController::class,'deletePlan']);

      //subscriptions
      Route::get('subscriptions',[SubscriptionController::class, 'getSubscriptions']);
      Route::put('subscriptions/{id}',[SubscriptionController::class,'updateSubscription']);
      Route::delete('subscriptions/{id}',[SubscriptionController::class,'deleteSubscription']);

      //transactiontypes
      Route::get('transactiontypes',[TransactionTypeController::class, 'getTransactionTypes']);
      Route::post('transactiontypes',[TransactionTypeController::class,'addTransactionType']);
      Route::put('transactiontypes/{id}',[TransactionTypeController::class,'updateTransactionType']);
      Route::delete('transactiontypes/{id}',[TransactionTypeController::class,'deleteTransactionType']);
 

      //transactions
      Route::get('transactions',[TransactionController::class, 'getTransactions']);
      Route::get('transactions/{id}',[TransactionController::class, 'getTransaction']);
      Route::delete('transactions/{id}',[TransactionController::class,'deleteTransaction']);

      //verifications
      Route::get('verificationtypes',[VerificationTypeController::class, 'getVerificationTypes']);
      Route::get('verificationtypes/{id}',[VerificationTypeController::class, 'getVerificationType']);
      Route::post('verificationtypes',[VerificationTypeController::class, 'addVerificationType']);
      Route::put('verificationtypes/{id}',[VerificationTypeController::class,'updateVerificationType']);
      Route::delete('verificationtypes/{id}',[VerificationTypeController::class,'deleteVerificationType']);

      //verifications
      Route::get('verifications',[VerificationController::class, 'getVerifications']);
      Route::get('verifications/{id}',[VerificationController::class, 'getVerification']);
      Route::post('verifications/{id}',[VerificationController::class,'updateVerification']);
      Route::put('verifications/val/{id}',[VerificationController::class,'validateUserVerification']);
      Route::delete('verifications/{id}',[VerificationController::class,'deleteVerification']);

      //user activities
      Route::get('activity',[UserActivityController::class, 'getUserActivities']);
      Route::get('activity/{id}',[UserActivityController::class, 'getUserActivity']);


      //subscribers
      Route::get('subscribers',[SubscriberController::class, 'getSubscribers']);
      Route::get('subscribers/{id}',[SubscriberController::class, 'getSubscriber']);
      Route::put('subscribers/{id}',[SubscriberController::class,'updateSubscriber']);
      Route::delete('subscribers/{id}',[SubscriberController::class,'deleteSubscriber']);

      //feedbacks
      Route::get('feedbacks',[FeedbackController::class, 'getFeedbacks']);
      Route::get('feedbacks/{id}',[FeedbackController::class, 'getFeedback']);
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
      Route::get('paymentmethods',[PaymentMethodController::class, 'getPaymentMethods']);
      Route::post('paymentmethods',[PaymentMethodController::class,'addPaymentMethod']);
      Route::put('paymentmethods/{id}',[PaymentMethodController::class,'updatePaymentMethod']);
      Route::delete('paymentmethods/{id}',[PaymentMethodController::class,'deletePaymentMethod']);

      //countries
      Route::get('countries',[CountryController::class, 'getCountries']);
      Route::get('countries/{id}',[CountryController::class, 'getCountry']);
      Route::post('countries',[CountryController::class,'addCountry']);
      Route::put('countries/{id}',[CountryController::class,'updateCountry']);
      Route::delete('countries/{id}',[CountryController::class,'deleteCountry']);

      //ratings
      Route::delete('ratings/{id}',[RatingController::class,'deleteRating']);

       //colors
       Route::post('colors',[ColorController::class,'addColor']);
       Route::put('colors/{id}',[ColorController::class,'updateColor']);
       Route::delete('colors/{id}',[ColorController::class,'deleteColor']);
 
       //labels
       Route::post('labels',[LabelController::class,'addLabel']);
       Route::put('labels/{id}',[LabelController::class,'updateLabel']);
       Route::delete('labels/{id}',[LabelController::class,'deleteLabel']);

    });

  
  });

});
 

