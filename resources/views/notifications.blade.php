@extends('layouts.mobile')

@section('title', 'Thông báo - Micex')

@section('header')
<header class="w-full px-4 py-4 flex items-center justify-between bg-gray-900 border-b border-gray-800">
    <a href="{{ route('dashboard') }}" class="text-gray-400">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
    </a>
    <h1 class="text-white text-base font-semibold">Thông báo</h1>
    <div class="w-6"></div>
</header>
@endsection

@section('content')
<div class="px-4 py-4">
    @forelse($notifications as $notification)
    <div class="flex items-start gap-3 py-4 border-b border-gray-800 {{ !$notification->is_read ? 'bg-gray-800/30' : '' }}">
        <div class="relative flex-shrink-0">
            @if($notification->type === 'deposit_approved' || $notification->type === 'withdraw_approved')
                <svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            @elseif($notification->type === 'deposit_rejected' || $notification->type === 'withdraw_rejected')
                <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            @elseif($notification->type === 'promotion')
                <svg class="w-6 h-6 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"></path>
                </svg>
            @else
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                </svg>
            @endif
            @if(!$notification->is_read)
                <span class="absolute -top-1 -right-1 w-3 h-3 bg-red-500 rounded-full"></span>
            @endif
        </div>
        <div class="flex-1">
            <p class="text-white font-semibold mb-1">{{ $notification->title }}</p>
            <p class="text-gray-400 text-sm mb-2">{{ $notification->message }}</p>
            <p class="text-gray-500 text-xs">{{ $notification->created_at->diffForHumans() }}</p>
        </div>
    </div>
    @empty
    <div class="text-center py-12">
        <svg class="w-16 h-16 text-gray-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
        </svg>
        <p class="text-gray-400 text-sm">Chưa có thông báo nào</p>
    </div>
    @endforelse

    <!-- Pagination -->
    @if($notifications->hasPages())
    <div class="mt-4 flex justify-center">
        {{ $notifications->links() }}
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Mark notifications as read when viewed
        const notifications = document.querySelectorAll('[data-notification-id]');
        notifications.forEach(notification => {
            const notificationId = notification.getAttribute('data-notification-id');
            if (notificationId) {
                // Mark as read via AJAX (optional, can be done on page load)
                fetch(`/notifications/${notificationId}/read`, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    },
                }).catch(err => console.error('Error marking notification as read:', err));
            }
        });
    });
</script>
@endpush
