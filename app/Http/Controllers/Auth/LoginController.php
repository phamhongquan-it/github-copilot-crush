<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'name' => 'required|string',
            // Validate phone number must be a string and follow the mask (xxx) xxx xxxx
            'phoneNumber' => 'required|string|regex:/\([0-9]{3}\) [0-9]{3} [0-9]{4}/',
        ]);

        if (auth()->attempt($request->only('email', 'password'))) {
            return redirect()->route('home');
        }

        return back()->with('error', 'Invalid credentials');
    }

    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleProviderCallback($provider)
    {
        $socialUser = Socialite::driver($provider)->stateless()->user();
        $user = User::where('email', $socialUser->getEmail())->first();

        if (!$user) {
            $user = User::create([
                'email' => $socialUser->getEmail(),
                'name' => $socialUser->getName(),
                'password' => Hash::make(uniqid()), // Generate a random password
            ]);
        }

        Auth::login($user, true);

        return redirect()->route('home');
    }

    public function logout()
    {
        Auth::logout();

        return redirect()->route('login');
    }

    public function activeUsers()
    {
        return User::where('active', true)->get();
    }

    public function quickSort(array $arr)
    {
        if (count($arr) <= 1) {
            return $arr;
        }
    
        $pivot = $arr[0];
        $left = $right = [];
    
        for ($i = 1; $i < count($arr); $i++) {
            if ($arr[$i] < $pivot) {
                $left[] = $arr[$i];
            } else {
                $right[] = $arr[$i];
            }
        }
    
        return array_merge($this->quickSort($left), [$pivot], $this->quickSort($right));
    }
}
