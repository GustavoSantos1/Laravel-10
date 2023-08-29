<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use SebastianBergmann\CodeCoverage\Driver\Driver;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Facades\Auth;


class ProviderController extends Controller
{
    public function redirect($provider)
    {
        return Socialite::driver($provider)->redirect();
    }

    public function callback($provider)
    {
        try
        {
            $SocialUser = Socialite::driver($provider)->user();
            if(User::where('email', $SocialUser->getEmail())->exits())
            {
                return redirect('/login')->withErros(['email'=>'this email uses different method to login']);
            }
            $user = User::where([
                'provider'=> $provider,
                'provider_id'=> $SocialUser->id
            ])->first();
            
            if(!$user){
                $user = User::create([
                    'name'=>$SocialUser->getName(),
                    'email'=>$SocialUser->getEmai(),
                    'username'=>User::generateUserName($SocialUser->getNickname()),
                    'provider'=>$provider,
                    'provider_id'=> $SocialUser->getId(),
                    'provider_token'=> $SocialUser-> token,
                    'email_verified_at'=> now()
                ]);
            }

            Auth::login($user);
            return redirect('/dashboard');

        } 
        catch(\Exception $e)
        {
            return redirect('/login');
        }

        $SocialUser = Socialite::driver($provider)->user();
        $user = User::updateOrCreate([
            'provider_id' => $SocialUser->id,
            'provider'=> $provider
        ], [
            'name' => $SocialUser->name,
            'username' => User::generateUserName($SocialUser->nickname),
            'email' => $SocialUser->email,
            'provider_token' => $SocialUser->token
            
        ]);

        Auth::login($user);
        return redirect('/dashboard');

    }
}
