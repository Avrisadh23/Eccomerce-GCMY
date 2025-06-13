<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;

class AuthController extends Controller
{
    protected $apiBaseUrl;

    public function __construct()
    {
        $this->apiBaseUrl = env('USER_SERVICE_EXTERNAL_URL');
    }

    public function showLogin()
    {
        return view('auth.login');
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        try {
            $response = Http::post($this->apiBaseUrl . '/users/login', [
                'email' => $request->email,
                'password' => $request->password
            ]);
               
            $result = $response->json();

            if ($response->successful()) {
                $result = $response->json();
                if (isset($result['status']) && $result['status'] === 'success' && isset($result['data'])) {
                    $user = $result['data'];
                    Session::put('user', $user);
                    return redirect()->route('home')
                        ->with('success', 'Welcome back, ' . $user['first_name'] . '!');
                }
            }

            return back()->withErrors([
                'email' => 'The provided credentials do not match our records.',
            ]);
        } catch (\Exception $e) {
            return back()->withErrors([
                'email' => 'Unable to connect to authentication service.',
            ]);
        }
    }

    public function register(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'password' => 'required|string|min:8|confirmed'
        ]);

        try {
            $response = Http::post($this->apiBaseUrl . '/users/register', [
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'password' => $request->password
            ]);

            if ($response->successful()) {
                return redirect()->route('login')
                    ->with('success', 'Registration successful! Please login.');
            }

            return back()->withErrors([
                'email' => $response->json()['error'] ?? 'Registration failed.',
            ]);
        } catch (\Exception $e) {
            return back()->withErrors([
                'email' => 'Unable to connect to registration service. ' . $e->getMessage(),
            ]);
        }
    }

    public function logout()
    {
        Session::forget('user');
        return redirect()->route('login')
            ->with('success', 'You have been logged out successfully.');
    }

    public function profile()
    {
        if (!Session::has('user')) {
            return redirect()->route('login');
        }

        $user = Session::get('user');
        
        try {
            $response = Http::get($this->apiBaseUrl . '/users/profile/' . $user['id']);
            
            if ($response->successful()) {
                $profile = $response->json();
                return view('auth.profile', ['user' => $profile]);
            }

            return redirect()->route('home')
                ->with('error', 'Unable to fetch profile.');
        } catch (\Exception $e) {
            return redirect()->route('home')
                ->with('error', 'Unable to connect to user service.');
        }
    }

    public function updateProfile(Request $request)
    {
        if (!Session::has('user')) {
            return redirect()->route('login');
        }

        $user = Session::get('user');

        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'password' => 'nullable|string|min:8|confirmed'
        ]);

        try {
            $data = [
                'first_name' => $request->first_name,
                'last_name' => $request->last_name
            ];

            if ($request->filled('password')) {
                $data['password'] = $request->password;
            }

            $response = Http::put($this->apiBaseUrl . '/users/profile/' . $user['id'], $data);
            
            if ($response->successful()) {
                $updatedUser = $response->json();
                Session::put('user', $updatedUser);
                return back()->with('success', 'Profile updated successfully.');
            }

            return back()->withErrors([
                'error' => 'Unable to update profile.',
            ]);
        } catch (\Exception $e) {
            return back()->withErrors([
                'error' => 'Unable to connect to user service.',
            ]);
        }
    }
}