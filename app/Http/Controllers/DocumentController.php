<?php

namespace App\Http\Controllers;

use App\Models\Silos;
use App\Models\Sios;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Legal document downloads. TECHNICAL_AUDIT.md C5: these files carry
 * personal certification data (UU PDP scope). Phase 0 (Milestone M0.3)
 * closed the "reachable by anyone with the URL" exposure with
 * authentication only, as an interim measure — this completes C5 with
 * real Policy-scoped access (IMPLEMENTATION_PLAN.md Milestone M3.4):
 * per the approved authorization matrix, LOGISTIK is authenticated but
 * still has no access to SIO/SILO, so authentication alone is no longer
 * sufficient.
 */
class DocumentController extends Controller
{
    public function sio(Sios $sios): StreamedResponse
    {
        $this->authorize('view', $sios);

        abort_unless($sios->file_sio && Storage::disk('local')->exists($sios->file_sio), 404);

        return Storage::disk('local')->response($sios->file_sio);
    }

    public function silo(Silos $silos): StreamedResponse
    {
        $this->authorize('view', $silos);

        abort_unless($silos->file_path && Storage::disk('local')->exists($silos->file_path), 404);

        return Storage::disk('local')->response($silos->file_path);
    }
}
