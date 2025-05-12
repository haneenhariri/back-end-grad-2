<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class PasswordController extends Controller
{
    public function sendResetCode(Request $request)
    {
        $request->validate(['email' => 'required|email|exists:users,email']);

        $token = Str::random(6);

        $d = DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $request->email],
            ['token' => $token, 'created_at' => now()]
        );
        Mail::raw("Your verification code is: $token", function ($message) use ($request) {
            $message->to($request->email)->subject('Password Reset Verification Code');
        });

        return self::success(null, 'Verification code sent to your email.');
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'token' => 'required|string',
            'password' => 'required|min:8|confirmed',
        ]);

        $reset = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->where('token', $request->token)
            ->first();

        if (!$reset || Carbon::parse($reset->created_at)->addMinutes(15)->isPast()) {
            throw new \Exception('Invalid or expired token.', 400);
        }

        $user = \App\Models\User::where('email', $request->email)->first();
        $user->update(['password' => Hash::make($request->password)]);

        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return self::success(null, 'Password reset successful.');
    }


    // تحديث كلمة المرور عبر API
    public function update(Request $request)
    {
        // التحقق من المدخلات
        $request->validate([
            'current_password' => ['required'],
            'new_password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        // التأكد من أن كلمة المرور الحالية صحيحة
        if (!Hash::check($request->current_password, Auth::user()->password)) {
            throw  new \Exception('The current password is incorrect.', 400);
        }

        // تحديث كلمة المرور
        Auth::user()->update([
            'password' => Hash::make($request->new_password),
        ]);

        return self::success(null, 'Your password has been changed successfully.');
    }
}
