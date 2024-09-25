<?php
namespace App\Actions\Fortify;

use Laravel\Fortify\Contracts\RegisterResponse as RegisterResponseContract;

class RegisterResponse implements RegisterResponseContract
{
    public function toResponse($request)
    {
        // Redirect semua pengguna ke halaman admin setelah registrasi
        return redirect()->intended('/admin');
    }
}