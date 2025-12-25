<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ShiftAutoCreateService;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    protected $shiftAutoCreateService;

    public function __construct(ShiftAutoCreateService $shiftAutoCreateService)
    {
        $this->shiftAutoCreateService = $shiftAutoCreateService;
    }

    public function showLoginForm()
    {
        return view('admin.auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::guard('admin')->attempt($credentials, $request->filled('remember'))) {
            $request->session()->regenerate();
            
            /** @var Admin $admin */
            $admin = Auth::guard('admin')->user();
            
            // Tự động tạo ca làm việc nếu là nhân viên
            if ($admin && $admin->isEmployee()) {
                $shift = $this->shiftAutoCreateService->createShiftOnLogin($admin);
                
                // Nếu không tạo được ca (nhân viên thứ 2), thông báo
                if (!$shift) {
                    return redirect()->intended('/admin/dashboard')
                        ->with('warning', 'Hiện tại đã có nhân viên khác đang làm việc. Bạn có thể xem báo cáo và lịch sử ca làm việc, nhưng không thể thực hiện các thao tác checkout cho đến khi nhân viên hiện tại kết thúc ca.');
                }
            }
            
            return redirect()->intended('/admin/dashboard');
        }

        return back()->withErrors([
            'email' => 'Thông tin đăng nhập không chính xác.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        /** @var Admin|null $admin */
        $admin = Auth::guard('admin')->user();
        
        // Nếu là nhân viên, đóng ca làm việc hiện tại
        if ($admin && $admin->isEmployee()) {
            $this->shiftAutoCreateService->closeShiftOnLogout($admin);
        }
        
        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect('/admin/login');
    }
}
