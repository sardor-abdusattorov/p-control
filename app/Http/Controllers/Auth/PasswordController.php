<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class PasswordController extends Controller
{
    /**
     * Update the user's password.
     */
    public function update(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', 'min:6'],
        ]);
        if (Hash::check($request->password, $request->user()->password)) {
            return back()->withErrors([
                'password' => 'Новый пароль не должен совпадать с текущим.',
            ]);
        }
        $request->user()->update([
            'password' => Hash::make($request->password),
        ]);
        auth()->logout();
        return redirect()->route('login')->with('status', 'Пароль успешно изменён. Пожалуйста, войдите снова.');
    }

}
