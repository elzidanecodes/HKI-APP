<?php

namespace App\Http\Controllers;

use App\Models\Silos;
use App\Models\Sios;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentController extends Controller
{
    public function sio(Sios $sios): StreamedResponse
    {
        abort_unless($sios->file_sio && Storage::disk('local')->exists($sios->file_sio), 404);

        return Storage::disk('local')->response($sios->file_sio);
    }

    public function silo(Silos $silos): StreamedResponse
    {
        abort_unless($silos->file_path && Storage::disk('local')->exists($silos->file_path), 404);

        return Storage::disk('local')->response($silos->file_path);
    }
}
