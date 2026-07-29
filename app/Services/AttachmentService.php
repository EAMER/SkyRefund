<?php

namespace App\Services;

use App\Enums\AttachmentType;
use App\Models\Refund;
use App\Models\RefundAttachment;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class AttachmentService
{
    /**
     * Store all refund attachments.
     */
    public function store(
        Refund $refund,
        Request $request
    ): void {

        $files = $request->file('attachments', []);

        $types = $request->input(
            'attachment_types',
            []
        );


        if (empty($files)) {
            return;
        }


        if (count($files) !== count($types)) {

            throw new RuntimeException(
                'Each attachment must have a matching attachment type.'
            );
        }



        foreach ($files as $index => $file) {


            if (! $file instanceof UploadedFile) {
                continue;
            }


            $type = AttachmentType::tryFrom(
                $types[$index]
            );


            if (! $type) {

                $type = AttachmentType::OTHER;
            }



            $this->storeSingleAttachment(
                $refund,
                $file,
                $type
            );
        }
    }




    /**
     * Store one attachment.
     */
    protected function storeSingleAttachment(
        Refund $refund,
        UploadedFile $file,
        AttachmentType $type
    ): RefundAttachment {


        $this->validateFile($file);



        $storedName =
            Str::uuid()
            . '.'
            . $file->extension();



        $directory =
            "refunds/{$refund->id}";



        $path = $file->storeAs(
            $directory,
            $storedName,
            'local'
        );



        if (! $path) {

            throw new RuntimeException(
                'Unable to store attachment.'
            );
        }



        return RefundAttachment::create([

            'refund_id' =>
                $refund->id,


            'type' =>
                $type,


            'original_name' =>
                $file->getClientOriginalName(),


            'stored_name' =>
                $storedName,


            'path' =>
                $path,


            'mime_type' =>
                $file->getMimeType(),


            'size' =>
                $file->getSize(),

        ]);
    }





    /**
     * Validate uploaded files.
     */
    protected function validateFile(
        UploadedFile $file
    ): void {


        $allowedMimeTypes = [

            'image/jpeg',

            'image/png',

            'application/pdf',

        ];



        if (
            ! in_array(
                $file->getMimeType(),
                $allowedMimeTypes,
                true
            )
        ) {

            throw new RuntimeException(
                'Unsupported attachment type.'
            );
        }



        if ($file->getSize() > 10 * 1024 * 1024) {

            throw new RuntimeException(
                'Attachment exceeds 10MB limit.'
            );
        }
    }
}