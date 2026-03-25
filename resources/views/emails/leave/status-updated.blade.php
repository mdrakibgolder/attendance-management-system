@component('mail::message')
# Leave Request {{ ucfirst($leaveRequest->status) }}

Hello {{ $leaveRequest->user->name }},

Your leave request for **{{ $leaveRequest->leaveType->name }}** from **{{ $leaveRequest->start_date->format('d M Y') }}** to **{{ $leaveRequest->end_date->format('d M Y') }}** has been **{{ strtoupper($leaveRequest->status) }}**.

@if ($leaveRequest->manager_remark)
**Manager Remark:** {{ $leaveRequest->manager_remark }}
@endif

@if ($leaveRequest->manager)
Processed by: {{ $leaveRequest->manager->name }}
@endif

Thanks,<br>
{{ config('app.name') }}
@endcomponent
