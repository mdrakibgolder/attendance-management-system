<?php

namespace App\Mail;

use App\Models\LeaveRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class LeaveStatusUpdatedMail extends Mailable
{
    use Queueable;

    public function __construct(public LeaveRequest $leaveRequest)
    {
        //
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Leave Request ' . ucfirst($this->leaveRequest->status),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.leave.status-updated',
            with: [
                'leaveRequest' => $this->leaveRequest->load(['leaveType', 'manager']),
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
