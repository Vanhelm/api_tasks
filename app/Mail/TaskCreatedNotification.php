<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TaskCreatedNotification extends Mailable
{
    use Queueable, SerializesModels;

    public JsonResource $task;

    public function __construct(JsonResource $task)
    {
        $this->task = $task;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Новая задача создана',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.task_created',
            with: [
                'task' => $this->task,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}

