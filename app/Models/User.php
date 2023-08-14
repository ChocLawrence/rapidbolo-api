<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Role;
use App\Models\User;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'role_id',
        'plan_id',
        'firstname',
        'middlename',
        'lastname',
        'username',
        'address',
        'dob',
        'status_id',
        'longitude',
        'latitude',
        'email',
        'password',
        'gender',
        'phone'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function isAdmin()
    {
        if($this->role_id === 1)
        { 
            return true; 
        } 
        else 
        { 
            return false; 
        }
    }

    public function hasRole($role)
    {
        $res = Role::where('name', $role)->first();
        $doesHave = User::where('role_id', $res->id)
                    ->where('id', $this->id)->first();         
        if($doesHave){
            return true;
        }else{
            return false; 
        }
    }

    public function status()
    {
        return $this->hasOne('App\Models\Status');
    }

    public function role()
    {
        return $this->hasOne('App\Models\Role');
    }

    public function chats()
    {
        return $this->hasMany('App\Models\Chat');
    }

    public function user_activities()
    {
        return $this->hasMany('App\Models\UserActivity');
    }

    public function notifications()
    {
        return $this->hasMany('App\Models\Notification');
    }

    public function transactions()
    {
        return $this->hasMany('App\Models\Transaction');
    }

    public function services()
    {
        return $this->hasMany('App\Models\Service');
    }

    public function demands()
    {
        return $this->hasMany('App\Models\Demand');
    }

    public function sliders()
    {
        return $this->hasMany('App\Models\Slider');
    }

    public function subscription()
    {
        return $this->hasOne('App\Models\Subscription');
    }

    public function verification()
    {
        return $this->hasOne('App\Models\Verification');
    }


    public function sendPasswordResetNotification($token)
    {
        $web_url = env("WEB_URL", "https://rapidbolo.com");
        $url = $web_url.'/password-reset?token=' . $token .'&state=change';

        $this->notify(new ResetPasswordNotification($url));
    }
}
