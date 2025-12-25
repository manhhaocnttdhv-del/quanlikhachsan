<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class PasswordResetController extends Controller
{
    public function showForgotPasswordForm()
    {
        return view('user.auth.forgot-password');
    }

    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $user = User::where('email', $request->email)->first();
        
        // Tạo token reset (đơn giản - có thể dùng password_reset_tokens table)
        $token = Str::random(60);
        
        // Lưu token vào database (sử dụng password_reset_tokens table)
        \DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $request->email],
            [
                'token' => Hash::make($token),
                'created_at' => now(),
            ]
        );

        // Gửi email (tạm thời chỉ lưu token, có thể tích hợp email service sau)
        // Mail::to($user->email)->send(new PasswordResetMail($token));

        return back()->with('success', 'Link đặt lại mật khẩu đã được gửi đến email của bạn!');
    }

    public function showResetForm($token)
    {
        return view('user.auth.reset-password', compact('token'));
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'token' => 'required',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $reset = \DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (!$reset || !Hash::check($request->token, $reset->token)) {
            return back()->withErrors(['token' => 'Token không hợp lệ hoặc đã hết hạn.']);
        }

        // Kiểm tra token không quá 1 giờ
        if (now()->diffInHours($reset->created_at) > 1) {
            return back()->withErrors(['token' => 'Token đã hết hạn. Vui lòng yêu cầu lại.']);
        }

        // Đặt lại mật khẩu
        $user = User::where('email', $request->email)->first();
        $user->password = Hash::make($request->password);
        $user->save();

        // Xóa token
        \DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return redirect()->route('user.login')->with('success', 'Mật khẩu đã được đặt lại thành công!');
    }
}
