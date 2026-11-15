<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;

class DocumentIssuedMail extends Mailable
{
    use Queueable;

    /**
     * @param  array<int, array{path: string, name: string}>  $files
     */
    public function __construct(private string $title, private string $body, private array $files, private string $disk) {}

    public function build(): static
    {
        $mail = $this->subject($this->title)->html('<p>'.nl2br(e($this->body)).'</p>');
        foreach ($this->files as $file) {
            $mail->attachFromStorageDisk($this->disk, $file['path'], $file['name'], ['mime' => 'application/pdf']);
        }

        return $mail;
    }
}
