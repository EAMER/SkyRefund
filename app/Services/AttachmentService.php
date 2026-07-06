<?php

namespace App\Services;

use App\Enums\AttachmentType;
use App\Models\Refund;
use App\Models\RefundAttachment;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use InvalidArgumentException;

class AttachmentService
{
    /**
     * Store all uploaded attachments for a refund.
     */
    public function store(Refund $refund, Request $request): void
    {
        if (! $request->hasFile('attachments')) {
            return;
        }

        $files = $request->file('attachments', []);
        $types = $request->input('attachment_types', []);

        /*
        |--------------------------------------------------------------------------
        | Ensure every uploaded file has a matching attachment type
        |--------------------------------------------------------------------------
        */

        if (count($files) !== count($types)) {
            throw new InvalidArgumentException(
                'Each uploaded attachment must have a matching attachment type.'
            );
        }

        foreach ($files as $index => $file) {

            $type = AttachmentType::tryFrom($types[$index])
                ?? AttachmentType::OTHER;

            $this->storeSingleAttachment(
                refund: $refund,
                file: $file,
                type: $type
            );
        }
    }

    /**
     * Store a single attachment.
     */
    protected function storeSingleAttachment(
        Refund $refund,
        UploadedFile $file,
        AttachmentType $type
    ): RefundAttachment {

        /*
        |--------------------------------------------------------------------------
        | Generate a secure filename
        |--------------------------------------------------------------------------
        */

        $storedName = Str::uuid() . '.' . $file->extension();

        /*
        |--------------------------------------------------------------------------
        | Store the file
        |--------------------------------------------------------------------------
        */

        $path = $file->storeAs(
            "private/refunds/{$refund->id}",
            $storedName
        );

        /*
        |--------------------------------------------------------------------------
        | Save attachment record
        |--------------------------------------------------------------------------
        */

        return RefundAttachment::create([

            'refund_id'      => $refund->id,

            'type'           => $type->value,

            'original_name'  => $file->getClientOriginalName(),

            'stored_name'    => $storedName,

            'path'           => $path,

            'mime_type'      => $file->getMimeType(),

            'size'           => $file->getSize(),

        ]);
    }
}