<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    // Functie voor KLA-10666 (Registratie)
    public function register(Request $request)
    {
        // 1. Controleer de inkomende data
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed', // Verwacht ook een password_confirmation veld
        ]);

        // 2. Maak de gebruiker aan in de database
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'is_admin' => false, // Nieuwe gebruikers zijn standaard altijd klant
        ]);

        // 3. Geef een API token (sleutel) en de gebruikersdata terug
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'user' => $user
        ]);
    }

    // Functie voor Inloggen (KLA-10642)
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        // Check of de gebruiker bestaat en het wachtwoord klopt
        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['De inloggegevens zijn onjuist.'],
            ]);
        }

        // Maak een token aan voor de Vue frontend
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'user' => $user
        ]);
    }

    // Functie voor Uitloggen (KLA-10644)
    public function logout(Request $request)
    {
        // Vernietig alle actieve tokens van deze gebruiker
        $request->user()->tokens()->delete();

        return response()->json(['message' => 'Succesvol uitgelogd']);
    }
}