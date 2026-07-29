<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Refund;
use App\Models\RefundAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RefundAttachmentController extends Controller
{
    public function show(
        Request $request,
        Refund $refund,
        RefundAttachment $attachment
    ): StreamedResponse {

        $user = $request->user();

        /*
        |--------------------------------------------------------------------------
        | Tenant Security
        |--------------------------------------------------------------------------
        */

        if (
            ! $user->isSuperAdmin()
            &&
            $refund->airline_id !== $user->airline_id
        ) {

            abort(404, 'Refund not found.');
        }


        if ($attachment->refund_id !== $refund->id) {
            abort(404, 'Attachment does not belong to this refund.');
        }


        if (! Storage::disk('local')->exists($attachment->path)) {
            abort(404, 'File not found.');
        }


        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
        $disk = Storage::disk('local');

        return $disk->response(
            $attachment->path,
            $attachment->original_name,
            [
                'Content-Type' => $attachment->mime_type,
                'Content-Disposition' => 'inline; filename="' . $attachment->original_name . '"',
            ]
        );
    }
}