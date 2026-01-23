@extends('layouts.mobile')

@section('title', 'Trang chủ - Micex')

@push('styles')
    <style>
        /* Custom Carousel Styles - không dùng Bootstrap CSS để tránh conflict */
        #sliderCarousel {
            position: relative;
        }

        .carousel-inner {
            position: relative;
            width: 100%;
            overflow: hidden;
            border-radius: 10px;
            aspect-ratio: 16 / 9;
            /* giữ tỷ lệ cho cả mobile và desktop */
        }

        .carousel-item {
            display: none;
            opacity: 0;
            transform: translateX(30px);
            transition: opacity 0.6s ease-in-out, transform 0.6s ease-in-out;
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
        }

        .carousel-item.active {
            display: block;
            opacity: 1;
            transform: translateX(0);
            position: relative;
            width: 100%;
            height: 100%;
        }

        .carousel-item.fade-out {
            opacity: 0;
            transform: translateX(-30px);
        }

        .carousel-indicators {
            position: absolute;
            bottom: 10px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 8px;
            z-index: 10;
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .carousel-indicators button {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background-color: rgba(255, 255, 255, 0.5);
            border: none;
            cursor: pointer;
            padding: 0;
            transition: all 0.3s ease;
        }

        .carousel-indicators button:hover {
            background-color: rgba(255, 255, 255, 0.7);
            transform: scale(1.2);
        }

        .carousel-indicators button.active {
            background-color: rgba(255, 255, 255, 0.9);
            width: 24px;
            border-radius: 4px;
        }

        /* Slider Content Animations */
        .carousel-item.active .slider-text {
            animation: slideInLeft 0.6s ease-out;
        }

        .carousel-item.active .slider-image {
            animation: slideInRight 0.6s ease-out 0.2s both;
        }

        .carousel-item.active .slider-badge {
            animation: fadeInDown 0.5s ease-out 0.1s both;
        }

        .carousel-item.active .slider-title {
            animation: fadeInDown 0.5s ease-out 0.2s both;
        }

        .carousel-item.active .slider-button {
            animation: fadeInUp 0.5s ease-out 0.3s both;
        }

        .carousel-item.active .slider-description {
            animation: fadeInUp 0.5s ease-out 0.4s both;
        }

        /* Slider Content - Auto height */
        .slider-content {
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        .slider-text {
            overflow: visible;
        }

        .slider-description {
            word-break: break-word;
            overflow-wrap: break-word;
            white-space: normal;
        }

        @keyframes slideInLeft {
            from {
                opacity: 0;
                transform: translateX(-30px);
            }

            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(30px) scale(0.9);
            }

            to {
                opacity: 1;
                transform: translateX(0) scale(1);
            }
        }

        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Referral Modal Animation */
        #referralModal.show {
            display: flex !important;
        }

        #referralModal.show>div:last-child {
            transform: translateY(0);
            opacity: 1;
        }

        /* Gift Spotlight Effect - Circular glow, not square */
        .gift-spotlight {
            position: absolute;
            top: 15%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 240px;
            height: 240px;
            background: linear-gradient(178.88deg, #3958F5 -160.95%, rgba(57, 88, 245, 0.01) 86.99%);
            border: 1px solid;
            border-image-source: linear-gradient(180deg, #3958F5 0%, rgba(102, 102, 102, 0) 100%);
            border-image-slice: 1;
            border-radius: 50%;
            backdrop-filter: blur(4px);
            -webkit-backdrop-filter: blur(4px);
            pointer-events: none;
            z-index: 1;
            animation: giftPulse 3s ease-in-out infinite;
        }

        @keyframes giftPulse {

            0%,
            100% {
                opacity: 0.6;
                transform: translate(-50%, -50%) scale(1);
            }

            50% {
                opacity: 0.8;
                transform: translate(-50%, -50%) scale(1.05);
            }
        }

        /* Gift Container - Ensure no square glow */
        .gift-container {
            position: relative;
            z-index: 2;
        }

        .gift-image {
            /* filter: drop-shadow(0 0 20px rgba(255, 157, 0, 0.5)); */
            /* animation: giftShine 2s ease-in-out infinite; */
        }

        /* @keyframes giftShine {
            0%, 100% {
                filter: drop-shadow(0 0 20px rgba(255, 157, 0, 0.5));
            }
            50% {
                filter: drop-shadow(0 0 30px rgba(255, 157, 0, 0.8));
            }
        } */
    </style>
@endpush

@section('header')
    <header class="w-full px-4 py-4 flex items-center justify-between bg-[#141517] border-b border-gray-800">
        <div class="text-white text-xl font-bold">MICEX</div>
        <div class="flex items-center gap-4">
            <!-- Profile Icon -->
            <a href="#" class="text-white flex items-center justify-center">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
            </a>
            <!-- Gift Box with Badge -->
            <a href="#" class="text-white relative flex items-center justify-center">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7">
                    </path>
                </svg>
                <span
                    class="absolute top-0 right-0 bg-red-500 text-white text-[8px] font-bold rounded-full min-w-[14px] h-[14px] flex items-center justify-center px-0.5 leading-none transform translate-x-1/2 -translate-y-1/2">New</span>
            </a>
            <!-- Notification Bell with Dropdown -->
            <div class="relative">
                <button id="notificationBtn"
                    class="text-white relative flex items-center justify-center hover:opacity-80 transition-opacity">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9">
                        </path>
                    </svg>
                    @if (isset($unreadCount) && $unreadCount > 0)
                        <span
                            class="absolute -top-1 -right-1 bg-red-500 text-white text-[10px] font-bold rounded-full min-w-[18px] h-[18px] flex items-center justify-center px-1">
                            {{ $unreadCount > 99 ? '99+' : $unreadCount }}
                        </span>
                    @endif
                </button>

                <!-- Dropdown Menu -->
                <div id="notificationDropdown"
                    class="hidden absolute right-0 mt-2 w-[calc(100vw-2rem)] md:w-80 max-w-sm bg-[#0f1118] rounded-xl shadow-2xl border border-gray-700/50 z-50 max-h-[500px] overflow-hidden flex flex-col">
                    <!-- Dropdown Header -->
                    <div class="px-4 py-3 border-b border-gray-700/50 flex items-center justify-between bg-[#0f1118]">
                        <div class="flex items-center gap-2">
                            <h3 class="text-white font-semibold text-base">Thông báo</h3>
                            @if (isset($unreadCount) && $unreadCount > 0)
                                <span class="bg-red-500 text-white text-xs font-bold rounded-full px-2 py-0.5">
                                    {{ $unreadCount }}
                                </span>
                            @endif
                        </div>
                        <a href="{{ route('notifications') }}"
                            class="text-blue-400 text-sm hover:text-blue-300 font-medium transition-colors">Xem tất cả</a>
                    </div>

                    <!-- Notifications List -->
                    <div class="overflow-y-auto hide-scrollbar flex-1">
                        @forelse($recentNotifications ?? [] as $notification)
                            <a href="{{ route('notifications') }}"
                                class="block px-4 py-3 hover:bg-gray-800/50 border-b border-gray-700/30 transition-colors {{ !$notification->is_read ? 'bg-blue-500/10 border-l-2 border-l-blue-500' : '' }}"
                                data-notification-id="{{ $notification->id }}">
                                <div class="flex items-start gap-3">
                                    <div class="relative flex-shrink-0 mt-0.5">
                                        @if ($notification->type === 'deposit_approved' || $notification->type === 'withdraw_approved')
                                            <div
                                                class="w-8 h-8 rounded-full bg-green-500/20 flex items-center justify-center">
                                                <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M5 13l4 4L19 7"></path>
                                                </svg>
                                            </div>
                                        @elseif($notification->type === 'deposit_rejected' || $notification->type === 'withdraw_rejected')
                                            <div
                                                class="w-8 h-8 rounded-full bg-red-500/20 flex items-center justify-center">
                                                <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M6 18L18 6M6 6l12 12"></path>
                                                </svg>
                                            </div>
                                        @elseif($notification->type === 'promotion')
                                            <div
                                                class="w-8 h-8 rounded-full bg-yellow-500/20 flex items-center justify-center">
                                                <svg class="w-4 h-4 text-yellow-500" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7">
                                                    </path>
                                                </svg>
                                            </div>
                                        @elseif($notification->type === 'commission_available')
                                            <div
                                                class="w-8 h-8 rounded-full bg-green-500/20 flex items-center justify-center">
                                                <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                                                    </path>
                                                </svg>
                                            </div>
                                        @else
                                            <div
                                                class="w-8 h-8 rounded-full bg-blue-500/20 flex items-center justify-center">
                                                <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9">
                                                    </path>
                                                </svg>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-start justify-between gap-2">
                                            <p class="text-white font-semibold text-sm mb-1 flex-1">
                                                {{ $notification->title }}</p>
                                            @if (!$notification->is_read)
                                                <span class="w-2 h-2 bg-blue-500 rounded-full flex-shrink-0 mt-1.5"></span>
                                            @endif
                                        </div>
                                        <p class="text-gray-400 text-xs line-clamp-2 leading-relaxed mb-1">
                                            {{ $notification->message }}</p>
                                        <p class="text-gray-500 text-xs">{{ $notification->created_at->diffForHumans() }}
                                        </p>
                                    </div>
                                </div>
                            </a>
                        @empty
                            <div class="px-4 py-12 text-center">
                                <svg class="w-12 h-12 text-gray-600 mx-auto mb-3" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9">
                                    </path>
                                </svg>
                                <p class="text-gray-400 text-sm">Chưa có thông báo nào</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </header>
@endsection

@section('content')
    <!-- Slider Section -->
    @if ($sliders->count() > 0)
        <div class="mx-4 mt-4 relative">
            <div id="sliderCarousel" class="carousel slide"
                style="border-radius: 10px; border: 0.5px solid #FF9D00; background: linear-gradient(180deg, #324CCF -11.41%, #171923 101.14%);">
                <div class="carousel-inner">
                    @foreach ($sliders as $index => $slider)
                        <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                            @if ($slider->image)
                                <div class="h-full w-full overflow-hidden" style="border-radius: 10px;">
                                    <img src="{{ asset('storage/' . $slider->image) }}" alt="Slider"
                                        class="w-full h-full object-cover"
                                        style="border-radius: 10px; width: 100%; height: 100%; aspect-ratio: 16 / 9;">
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>

                @if ($sliders->count() > 1)
                    <!-- Dots Indicator -->
                    <div class="carousel-indicators">
                        @foreach ($sliders as $index => $slider)
                            <button type="button" onclick="goToSlide({{ $index }})"
                                class="{{ $index === 0 ? 'active' : '' }}"></button>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    @else
        <!-- Default Banner if no sliders -->
        <div class="bg-gradient-to-br from-blue-600 to-blue-800 mx-4 mt-4 rounded-xs p-6 relative overflow-hidden">
            <div class="relative z-10">
                <h2 class="text-white text-2xl font-bold mb-1">MICEX</h2>
                <p class="text-white text-lg mb-4">Lễ hội khuyến mãi 20%</p>
                <button
                    class="bg-white/90 text-blue-700 font-semibold px-4 py-2 rounded-full text-sm mb-3 hover:bg-white transition-colors">
                    Vác cuốc lên đi cày nào ?
                </button>
                <p class="text-white/90 text-sm">Giới thiệu bạn bè ngay hôm nay để nhận phần thưởng liền tay !</p>
            </div>
        </div>
    @endif

    <div class="px-4 py-4 space-y-4">
        <!-- Daily Reward Card -->
        <div class="bg-[#181A20] rounded-xl p-4 flex items-center justify-between relative overflow-hidden">
            <!-- Left Side: Bitcoin Icon and Content -->
            <div class="flex-1 flex items-start gap-3">

                <div class="w-12 h-12 flex items-start justify-center">
                    <svg width="36" height="36" viewBox="0 0 36 36" fill="none"
                        xmlns="http://www.w3.org/2000/svg">
                        <g clip-path="url(#clip0_1439_896)">
                            <path
                                d="M18 36C27.9411 36 36 27.9411 36 18C36 8.05887 27.9411 0 18 0C8.05887 0 0 8.05887 0 18C0 27.9411 8.05887 36 18 36Z"
                                fill="#F7931A" />
                            <path
                                d="M25.911 15.7725C26.2642 13.4145 24.4676 12.1466 22.0129 11.3006L22.8094 8.10562L20.8654 7.62188L20.0891 10.7325C19.5784 10.6042 19.0541 10.485 18.531 10.3657L19.3129 7.23488L17.3689 6.75L16.5724 9.94387C16.1494 9.84712 15.7331 9.75263 15.3304 9.65138L15.3326 9.64125L12.6506 8.97187L12.1331 11.0486C12.1331 11.0486 13.5765 11.3794 13.5461 11.3996C14.3336 11.5965 14.4754 12.1174 14.4518 12.5314L13.545 16.1707C13.599 16.1842 13.6688 16.2045 13.7475 16.2349L13.5416 16.1842L12.2704 21.2827C12.1736 21.5212 11.9295 21.8801 11.3783 21.744C11.3985 21.7721 9.96525 21.3919 9.96525 21.3919L9 23.6171L11.5312 24.2483C12.0015 24.3664 12.4627 24.4901 12.9161 24.606L12.1118 27.837L14.0546 28.3207L14.8511 25.1257C15.3821 25.2686 15.8974 25.4014 16.4014 25.5274L15.6071 28.7089L17.5511 29.1926L18.3555 25.9684C21.672 26.5961 24.165 26.343 25.2146 23.3438C26.0606 20.9295 25.173 19.5356 23.4281 18.6278C24.6994 18.3353 25.6556 17.4994 25.911 15.7725ZM21.4673 22.0028C20.8676 24.4181 16.8008 23.112 15.4823 22.7846L16.551 18.504C17.8695 18.8336 22.0961 19.485 21.4673 22.0028ZM22.0691 15.7376C21.5213 17.9348 18.1372 16.8176 17.0404 16.5443L18.0079 12.663C19.1047 12.9364 22.6406 13.446 22.0691 15.7376Z"
                                fill="white" />
                        </g>
                        <defs>
                            <clipPath id="clip0_1439_896">
                                <rect width="36" height="36" fill="white" />
                            </clipPath>
                        </defs>
                    </svg>
                </div>
                
                <!-- Text Content -->
                <div class="flex items-start gap-3 flex-col">

                    <p class="text-white text-sm mb-1 text-nowrap">Phần thưởng mỗi ngày</p>
                    
                    <p id="dailyRewardAmount" class="text-[#8297FF] text-2xl font-bold mb-2 text-nowrap">1,123.92 USDT</p>
                    <button id="openDailyRewardBtn"
                        class="bg-blue-500 hover:bg-blue-600 text-white text-sm font-semibold px-6 py-2 rounded-3xl transition-colors">
                        Mở ngay
                    </button>
                </div>
            </div>

            <!-- Right Side: Gift Box Illustration -->
            <div class="w-32 h-32 flex-shrink-0 relative flex items-end">
                <img src="{{ asset('images/icons/newgift.png') }}" alt="Gift Box" class="w-full h-full object-contain">
            </div>
        </div>

        <!-- Quick Action Icons -->
        <div class="flex items-center justify-between gap-4">
            <!-- Games -->
            <a href="{{ route('games.index') }}" class="flex flex-col items-center flex-1">
                <div class="w-16 h-16 bg-[#181A20] rounded-full flex items-center justify-center mb-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="17" viewBox="0 0 22 17"
                        fill="none">
                        <path
                            d="M15.1777 0.0292969C18.5627 -0.309197 21.5 2.34915 21.5 5.75098V11.75C21.4998 16.0941 15.8227 17.7409 13.498 14.0713C12.4142 12.36 9.93808 12.3026 8.77637 13.9619L8.49219 14.3682C5.87508 18.1066 0.000362173 16.2548 0 11.6914V5.75098C0 2.34915 2.93732 -0.309198 6.32227 0.0292969L10.2275 0.419922C10.5749 0.454659 10.9251 0.454659 11.2725 0.419922L15.1777 0.0292969Z"
                            fill="#04FF9A" />
                        <circle cx="16.75" cy="5.72559" r="1" fill="#181A20" />
                        <circle cx="14.75" cy="8.72559" r="1" fill="#181A20" />
                        <path d="M6.41669 9.22559L6.41669 5.22559" stroke="#181A20" stroke-width="1.5"
                            stroke-linecap="round" stroke-linejoin="round" />
                        <path d="M4.49998 7.22559L8.33331 7.22559" stroke="#181A20" stroke-width="1.5"
                            stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </div>
                <span class="text-white text-xs">Trò chơi</span>
            </a>

            <!-- Deposit -->
            <a href="{{ route('deposit') }}" class="flex flex-col items-center flex-1">
                <div class="w-16 h-16 bg-[#181A20] rounded-full flex items-center justify-center mb-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 28 28"
                        fill="none">
                        <path
                            d="M5.83335 17.5C3.25502 17.5 1.16669 19.5883 1.16669 22.1667C1.16669 23.0417 1.41169 23.87 1.84335 24.57C2.64835 25.9233 4.13002 26.8333 5.83335 26.8333C7.53669 26.8333 9.01835 25.9233 9.82335 24.57C10.255 23.87 10.5 23.0417 10.5 22.1667C10.5 19.5883 8.41169 17.5 5.83335 17.5ZM7.57169 23.0183H6.70835V23.9283C6.70835 24.4067 6.31169 24.8033 5.83335 24.8033C5.35502 24.8033 4.95835 24.4067 4.95835 23.9283V23.0183H4.09502C3.61669 23.0183 3.22002 22.6217 3.22002 22.1433C3.22002 21.665 3.61669 21.2683 4.09502 21.2683H4.95835V20.44C4.95835 19.9617 5.35502 19.565 5.83335 19.565C6.31169 19.565 6.70835 19.9617 6.70835 20.44V21.2683H7.57169C8.05002 21.2683 8.44669 21.665 8.44669 22.1433C8.44669 22.6217 8.06169 23.0183 7.57169 23.0183Z"
                            fill="#3958F5" />
                        <path
                            d="M25.0833 14.5834H22.1666C20.8833 14.5834 19.8333 15.6334 19.8333 16.9167C19.8333 18.2 20.8833 19.25 22.1666 19.25H25.0833C25.41 19.25 25.6666 18.9934 25.6666 18.6667V15.1667C25.6666 14.84 25.41 14.5834 25.0833 14.5834Z"
                            fill="#3958F5" />
                        <path
                            d="M19.285 6.30002C19.635 6.63835 19.3433 7.16335 18.8533 7.16335L9.19331 7.15169C8.63331 7.15169 8.35331 6.47502 8.74998 6.07835L10.7916 4.02502C12.5183 2.31002 15.3066 2.31002 17.0333 4.02502L19.2383 6.25335C19.25 6.26502 19.2733 6.28835 19.285 6.30002Z"
                            fill="#3958F5" />
                        <path
                            d="M25.515 21.77C24.8033 24.1733 22.75 25.6666 19.95 25.6666H12.3667C11.9117 25.6666 11.62 25.165 11.8067 24.745C12.1567 23.9283 12.3783 23.0066 12.3783 22.1666C12.3783 18.6316 9.49667 15.75 5.96167 15.75C5.075 15.75 4.21167 15.9366 3.41834 16.2866C2.98667 16.4733 2.46167 16.1816 2.46167 15.715V14C2.46167 10.8266 4.375 8.60996 7.35 8.23663C7.64167 8.18996 7.95667 8.16663 8.28334 8.16663H19.95C20.2533 8.16663 20.545 8.17829 20.825 8.22496C23.1817 8.49329 24.885 9.92829 25.515 12.0633C25.6317 12.4483 25.3517 12.8333 24.955 12.8333H22.2833C19.7517 12.8333 17.745 15.1433 18.2933 17.7683C18.6783 19.6816 20.4517 21 22.4 21H24.955C25.3633 21 25.6317 21.3966 25.515 21.77Z"
                            fill="#3958F5" />
                    </svg>
                </div>
                <span class="text-white text-xs">Nạp</span>
            </a>

            <!-- Withdraw -->
            <a href="{{ route('withdraw') }}" class="flex flex-col items-center flex-1">
                <div class="w-16 h-16 bg-[#181A20] rounded-full flex items-center justify-center mb-2">
                    <svg width="28" height="28" viewBox="0 0 28 28" fill="none"
                        xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M5.83329 17.5C3.25496 17.5 1.16663 19.5883 1.16663 22.1667C1.16663 23.0417 1.41163 23.87 1.84329 24.57C2.64829 25.9233 4.12996 26.8333 5.83329 26.8333C7.53663 26.8333 9.01829 25.9233 9.82329 24.57C10.255 23.87 10.5 23.0417 10.5 22.1667C10.5 19.5883 8.41163 17.5 5.83329 17.5ZM8.13163 21.7817L5.64663 24.08C5.48329 24.2317 5.26163 24.3133 5.05163 24.3133C4.82996 24.3133 4.60829 24.2317 4.43329 24.0567L3.27829 22.9017C2.93996 22.5633 2.93996 22.0033 3.27829 21.665C3.61663 21.3267 4.17663 21.3267 4.51496 21.665L5.07496 22.225L6.94163 20.4983C7.29163 20.1717 7.85163 20.195 8.17829 20.545C8.50496 20.895 8.48163 21.455 8.13163 21.7817Z"
                            fill="#FF8383" />
                        <path
                            d="M25.0834 14.5834H22.1667C20.8834 14.5834 19.8334 15.6334 19.8334 16.9167C19.8334 18.2 20.8834 19.25 22.1667 19.25H25.0834C25.41 19.25 25.6667 18.9934 25.6667 18.6667V15.1667C25.6667 14.84 25.41 14.5834 25.0834 14.5834Z"
                            fill="#FF8383" />
                        <path
                            d="M19.2849 6.30002C19.6349 6.63835 19.3432 7.16335 18.8532 7.16335L9.19325 7.15169C8.63325 7.15169 8.35325 6.47502 8.74992 6.07835L10.7916 4.02502C12.5182 2.31002 15.3066 2.31002 17.0332 4.02502L19.2382 6.25335C19.2499 6.26502 19.2732 6.28835 19.2849 6.30002Z"
                            fill="#FF8383" />
                        <path
                            d="M25.515 21.7701C24.8033 24.1734 22.75 25.6667 19.95 25.6667H12.3667C11.9117 25.6667 11.62 25.1651 11.8067 24.7451C12.1567 23.9284 12.3783 23.0067 12.3783 22.1667C12.3783 18.6317 9.49667 15.7501 5.96167 15.7501C5.075 15.7501 4.21167 15.9367 3.41834 16.2867C2.98667 16.4734 2.46167 16.1817 2.46167 15.7151V14.0001C2.46167 10.8267 4.375 8.61008 7.35 8.23675C7.64167 8.19008 7.95667 8.16675 8.28334 8.16675H19.95C20.2533 8.16675 20.545 8.17841 20.825 8.22508C23.1817 8.49341 24.885 9.92841 25.515 12.0634C25.6317 12.4484 25.3517 12.8334 24.955 12.8334H22.2833C19.7517 12.8334 17.745 15.1434 18.2933 17.7684C18.6783 19.6817 20.4517 21.0001 22.4 21.0001H24.955C25.3633 21.0001 25.6317 21.3967 25.515 21.7701Z"
                            fill="#FF8383" />
                    </svg>
                </div>
                <span class="text-white text-xs">Rút</span>
            </a>

            <!-- Invite -->
            <button id="inviteBtn" class="flex flex-col items-center flex-1">
                <div class="w-16 h-16 bg-[#181A20] rounded-full flex items-center justify-center mb-2">
                    <svg width="28" height="28" viewBox="0 0 28 28" fill="none"
                        xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M20.4517 9.06504C20.37 9.05337 20.2884 9.05337 20.2067 9.06504C18.3984 9.00671 16.9634 7.52504 16.9634 5.70504C16.9634 3.85004 18.4684 2.33337 20.335 2.33337C22.19 2.33337 23.7067 3.83837 23.7067 5.70504C23.695 7.52504 22.26 9.00671 20.4517 9.06504Z"
                            fill="#F3AC12" />
                        <path
                            d="M24.255 17.15C22.9484 18.025 21.1167 18.3516 19.425 18.13C19.8684 17.1733 20.1017 16.1116 20.1134 14.9916C20.1134 13.825 19.8567 12.7166 19.3667 11.7483C21.0934 11.515 22.925 11.8416 24.2434 12.7166C26.0867 13.93 26.0867 15.925 24.255 17.15Z"
                            fill="#F3AC12" />
                        <path
                            d="M7.51335 9.06504C7.59502 9.05337 7.67669 9.05337 7.75835 9.06504C9.56669 9.00671 11.0017 7.52504 11.0017 5.70504C11.0017 3.83837 9.49669 2.33337 7.63002 2.33337C5.77502 2.33337 4.27002 3.83837 4.27002 5.70504C4.27002 7.52504 5.70502 9.00671 7.51335 9.06504Z"
                            fill="#F3AC12" />
                        <path
                            d="M7.64168 14.9916C7.64168 16.1233 7.88668 17.1966 8.33001 18.165C6.68501 18.34 4.97001 17.99 3.71001 17.1616C1.86668 15.9366 1.86668 13.9416 3.71001 12.7166C4.95835 11.8766 6.72001 11.5383 8.37668 11.725C7.89835 12.705 7.64168 13.8133 7.64168 14.9916Z"
                            fill="#F3AC12" />
                        <path
                            d="M14.14 18.515C14.0466 18.5033 13.9416 18.5033 13.8366 18.515C11.69 18.445 9.97498 16.6833 9.97498 14.5133C9.98664 12.2967 11.7716 10.5 14 10.5C16.2166 10.5 18.0133 12.2967 18.0133 14.5133C18.0016 16.6833 16.2983 18.445 14.14 18.515Z"
                            fill="#F3AC12" />
                        <path
                            d="M10.3483 20.93C8.58668 22.1083 8.58668 24.045 10.3483 25.2116C12.355 26.5533 15.645 26.5533 17.6517 25.2116C19.4134 24.0333 19.4134 22.0966 17.6517 20.93C15.6567 19.5883 12.3667 19.5883 10.3483 20.93Z"
                            fill="#F3AC12" />
                    </svg>
                </div>
                <span class="text-white text-xs">Mời</span>
            </button>
        </div>

        <!-- Referral/Introduction Section -->
        <div class="bg-[#181A20] rounded-xl p-4 flex items-center gap-4">
            <!-- Left: Megaphone Illustration -->
            <div class="w-16 h-16 flex-shrink-0">
                <img src="{{ asset('images/icons/notifi.png') }}" alt="Megaphone"
                    class="w-full h-full object-contain">
            </div>

            <!-- Right: Content -->
            <div class="flex-1">
                <h3 class="text-white font-semibold text-base mb-1">Giới thiệu</h3>
                <p class="text-gray-400 text-sm">Giới thiệu bạn bè để hưởng hoa hồng cao nhất hiện nay!</p>
            </div>
        </div>

        <!-- Transaction Statistics -->
        <div class="grid grid-cols-2 gap-4">
            <!-- Currently Trading Card -->
            <div class="bg-[#181A20] rounded-xl p-4">
                <p class="text-white text-sm mb-2">Đang giao dịch</p>
                <p id="tradingPeopleCount" class="text-blue-400 text-xl font-bold">123 Người</p>
            </div>

            <!-- Transactions Card -->
            <div class="bg-[#181A20] rounded-xl p-4">
                <p class="text-white text-sm mb-2">Giao dịch</p>
                <div class="flex items-center justify-between">
                    <p id="transactionAmount" class="text-blue-400 text-xl font-bold">89,320.92$</p>
                    <!-- Simple Line Graph -->
                    <svg class="w-12 h-8 text-blue-400" viewBox="0 0 48 24" fill="none" stroke="currentColor"
                        stroke-width="2">
                        <polyline points="2,20 8,14 14,16 20,10 26,12 32,6 38,8 44,4" stroke-linecap="round"
                            stroke-linejoin="round" />
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Referral Code Modal -->
    <div id="referralModal" class="fixed inset-0 z-50 flex items-center justify-center hidden">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-black/50" onclick="closeReferralModalFunc()"></div>

        <!-- Popup Content -->
        <div
            class="relative bg-[#1e3a8a] rounded-3xl shadow-2xl pb-8 w-full max-w-[419px] mx-4 transform translate-y-4 opacity-0 transition-all duration-300 ease-out">
            <!-- Content -->
            <div class="px-6 pt-8">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-white font-semibold text-lg">Mã giới thiệu của bạn</h3>
                    <button id="closeReferralModal" onclick="closeReferralModalFunc()"
                        class="text-gray-400 hover:text-white transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="mb-6">
                    <p class="text-white text-sm mb-4">Chia sẻ mã này với bạn bè để nhận phần thưởng:</p>
                    <div
                        class="flex items-center gap-2 bg-gray-900/50 rounded-lg p-3 border border-gray-700/50 overflow-hidden">
                        <input type="text" id="referralCodeInput" value="{{ Auth::user()->referral_code ?? '' }}"
                            readonly class="flex-1 min-w-0 bg-transparent text-white font-semibold text-lg outline-none">
                        <button id="copyReferralCode"
                            class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition-colors cursor-pointer flex items-center gap-2 whitespace-nowrap flex-shrink-0">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                            </svg>
                            <span id="copyText" class="whitespace-nowrap">Sao chép</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Lucky Money Success Modal -->
    <div id="luckyMoneySuccessModal" class="fixed inset-0 z-[10000] flex items-center justify-center hidden">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-black/70" onclick="closeLuckyMoneyModal(event)"></div>

        <!-- Modal Content -->
        <div class="relative z-10 w-full max-w-sm mx-4 rounded-3xl overflow-visible"
            style="background: linear-gradient(114.45deg, #3958F5 3.99%, #111838 19.52%, #111838 78.39%, #3958F5 107.73%);">
            <!-- Close Button -->
            <button onclick="closeLuckyMoneyModal(event)"
                class="absolute top-4 right-4 z-[50] w-8 h-8 flex items-center justify-center bg-white/20 hover:bg-white/30 rounded-full transition-colors pointer-events-auto cursor-pointer">
                <svg class="w-5 h-5 text-white pointer-events-none" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>

            <!-- Image - Nổi lên trên -->
            <div class="flex justify-center -mt-14 relative z-30">
                <img src="{{ asset('images/icons/giftcodemodalnew.png') }}" alt="Lucky Money"
                    class="w-fit h-fit object-fit">
            </div>

            <!-- Text Content -->
            <div class="px-6 pt-4 pb-8 text-center">
                <h2 class="text-white text-2xl font-bold mb-3">Chúc mừng bạn !</h2>
                <p id="luckyMoneyAmount" class="text-green-400 text-3xl font-bold mb-3">0 đá quý</p>
                <p class="text-[#FFFFFF80] text-[13px] leading-relaxed">Nhận thưởng thành công từ lì xì của Micex</p>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        /* Lucky Money Success Modal Animation */
        #luckyMoneySuccessModal {
            opacity: 0;
            transition: opacity 0.3s ease-out;
        }

        #luckyMoneySuccessModal.show {
            opacity: 1;
        }

        #luckyMoneySuccessModal .relative {
            transform: scale(0.9);
            transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        #luckyMoneySuccessModal.show .relative {
            transform: scale(1);
        }
    </style>
@endpush

@push('scripts')
    <script>
        // Toggle notification dropdown
        const notificationBtn = document.getElementById('notificationBtn');
        const notificationDropdown = document.getElementById('notificationDropdown');

        if (notificationBtn && notificationDropdown) {
            notificationBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                notificationDropdown.classList.toggle('hidden');

                // Mark notifications as read when opening dropdown
                if (!notificationDropdown.classList.contains('hidden')) {
                    const unreadNotifications = notificationDropdown.querySelectorAll('[data-notification-id]');
                    unreadNotifications.forEach(notification => {
                        const notificationId = notification.getAttribute('data-notification-id');
                        if (notificationId) {
                            fetch(`/notifications/${notificationId}/read`, {
                                method: 'POST',
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector(
                                        'meta[name="csrf-token"]').getAttribute('content'),
                                },
                            }).catch(() => {});
                        }
                    });
                }
            });

            // Close dropdown when clicking outside
            document.addEventListener('click', function(e) {
                if (!notificationBtn.contains(e.target) && !notificationDropdown.contains(e.target)) {
                    notificationDropdown.classList.add('hidden');
                }
            });
        }

        // Custom Carousel Script - không dùng Bootstrap JS để tránh conflict
        let currentSlide = 0;
        const slides = document.querySelectorAll('.carousel-item');
        const indicators = document.querySelectorAll('.carousel-indicators button');
        let autoSlideInterval;

        function showSlide(index) {
            // Fade out current slide
            const currentActive = document.querySelector('.carousel-item.active');
            if (currentActive) {
                currentActive.classList.add('fade-out');
                setTimeout(() => {
                    currentActive.classList.remove('active', 'fade-out');
                }, 300);
            }

            // Update indicators
            indicators.forEach((indicator, i) => {
                indicator.classList.remove('active');
            });

            // Show new slide with animation
            setTimeout(() => {
                if (slides[index]) {
                    slides[index].classList.add('active');
                    if (indicators[index]) {
                        indicators[index].classList.add('active');
                    }
                }
                currentSlide = index;
            }, 300);
        }

        function changeSlide(direction) {
            let newIndex = currentSlide + direction;
            if (newIndex < 0) {
                newIndex = slides.length - 1;
            } else if (newIndex >= slides.length) {
                newIndex = 0;
            }
            showSlide(newIndex);
            resetAutoSlide();
        }

        function goToSlide(index) {
            showSlide(index);
            resetAutoSlide();
        }

        function resetAutoSlide() {
            clearInterval(autoSlideInterval);
            if (slides.length > 1) {
                autoSlideInterval = setInterval(() => {
                    changeSlide(1);
                }, 5000);
            }
        }

        // Initialize carousel
        if (slides.length > 0) {
            showSlide(0);
            if (slides.length > 1) {
                resetAutoSlide();
            }
        }

        // Daily Reward Button - Connect to Lucky Money
        const openDailyRewardBtn = document.getElementById('openDailyRewardBtn');
        if (openDailyRewardBtn) {
            openDailyRewardBtn.addEventListener('click', async function() {
                // Use the same logic as lucky money
                if (openDailyRewardBtn.disabled) {
                    return;
                }

                openDailyRewardBtn.disabled = true;
                openDailyRewardBtn.textContent = 'Đang mở...';

                let csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ||
                    document.querySelector('input[name="_token"]')?.value;

                try {
                    let response = await fetch('/api/lucky-money/open', {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken || '',
                        },
                    });

                    if (response.status === 419) {
                        try {
                            const refreshResponse = await fetch('/csrf-token', {
                                method: 'GET',
                                credentials: 'same-origin',
                                headers: {
                                    'Accept': 'application/json',
                                },
                            });

                            if (refreshResponse.ok) {
                                const refreshData = await refreshResponse.json();
                                if (refreshData && refreshData.token) {
                                    const metaTag = document.querySelector('meta[name="csrf-token"]');
                                    if (metaTag) {
                                        metaTag.setAttribute('content', refreshData.token);
                                    }
                                    csrfToken = refreshData.token;

                                    response = await fetch('/api/lucky-money/open', {
                                        method: 'POST',
                                        headers: {
                                            'X-Requested-With': 'XMLHttpRequest',
                                            'Accept': 'application/json',
                                            'Content-Type': 'application/json',
                                            'X-CSRF-TOKEN': csrfToken,
                                        },
                                    });
                                }
                            }
                        } catch (refreshError) {
                            console.error('Failed to refresh CSRF token:', refreshError);
                        }
                    }

                    const data = await response.json();

                    if (response.ok && data.message) {
                        showLuckyMoneyModal(data.amount);
                        openDailyRewardBtn.textContent = 'Đã mở';
                        loadDailyRewardStatus();
                    } else {
                        const errorMsg = data.message || data.error || 'Có lỗi xảy ra';
                        if (typeof showToast === 'function') {
                            showToast(errorMsg, 'error');
                        }
                        openDailyRewardBtn.disabled = false;
                        openDailyRewardBtn.textContent = 'Mở ngay';
                    }
                } catch (error) {
                    console.error('Error opening daily reward:', error);
                    if (typeof showToast === 'function') {
                        showToast('Có lỗi xảy ra khi mở hộp quà. Vui lòng thử lại.', 'error');
                    }
                    openDailyRewardBtn.disabled = false;
                    openDailyRewardBtn.textContent = 'Mở ngay';
                }
            });
        }

        // Load daily reward status and amount
        function loadDailyRewardStatus() {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ||
                document.querySelector('input[name="_token"]')?.value;

            fetch('/api/lucky-money/status', {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken || '',
                    },
                })
                .then(response => response.json())
                .then(data => {
                    if (openDailyRewardBtn) {
                        if (data.has_opened_today) {
                            openDailyRewardBtn.disabled = true;
                            openDailyRewardBtn.textContent = 'Đã mở';
                        } else {
                            openDailyRewardBtn.disabled = false;
                            openDailyRewardBtn.textContent = 'Mở ngay';
                        }
                    }

                    // Update daily reward amount display (show max possible amount)
                    const dailyRewardAmountEl = document.getElementById('dailyRewardAmount');
                    if (dailyRewardAmountEl && data.max_gems) {
                        // Format as USDT (assuming 1 gem = 1 USDT or use conversion rate)
                        const amount = parseFloat(data.max_gems).toLocaleString('en-US', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        });
                        dailyRewardAmountEl.textContent = amount + ' USDT';
                    }
                })
                .catch(error => {
                    console.error('Error loading daily reward status:', error);
                });
        }

        // Load transaction statistics
        function loadTransactionStats() {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ||
                document.querySelector('input[name="_token"]')?.value;

            // Load trading people count and transaction amount
            fetch('/api/home/stats', {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken || '',
                    },
                })
                .then(response => {
                    if (response.ok) {
                        return response.json();
                    }
                    // If endpoint doesn't exist, use default values
                    return null;
                })
                .then(data => {
                    if (data) {
                        const tradingPeopleCountEl = document.getElementById('tradingPeopleCount');
                        const transactionAmountEl = document.getElementById('transactionAmount');

                        if (tradingPeopleCountEl && data.trading_people_count !== undefined) {
                            tradingPeopleCountEl.textContent = data.trading_people_count + ' Người';
                        }

                        if (transactionAmountEl && data.transaction_amount !== undefined) {
                            const amount = parseFloat(data.transaction_amount).toLocaleString('en-US', {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            });
                            transactionAmountEl.textContent = amount + '$';
                        }
                    }
                })
                .catch(error => {
                    // Silent fail - use default values from HTML
                    console.error('Error loading transaction stats:', error);
                });
        }

        // Auto-refresh transaction stats every 10 seconds
        setInterval(loadTransactionStats, 10000);

        // Referral Modal
        const referralBtn = document.getElementById('referralBtn');
        const inviteBtn = document.getElementById('inviteBtn');
        const referralModal = document.getElementById('referralModal');
        const copyReferralCode = document.getElementById('copyReferralCode');
        const referralCodeInput = document.getElementById('referralCodeInput');
        const copyText = document.getElementById('copyText');

        // Open modal with animation
        function openReferralModal() {
            if (referralModal) {
                referralModal.classList.remove('hidden');
                // Trigger animation by adding show class after a small delay
                setTimeout(() => {
                    referralModal.classList.add('show');
                }, 10);
            }
        }

        // Close modal with animation
        function closeReferralModalFunc() {
            if (referralModal) {
                referralModal.classList.remove('show');
                // Hide after animation completes
                setTimeout(() => {
                    referralModal.classList.add('hidden');
                }, 300);
            }
        }

        // Open modal
        if (referralBtn) {
            referralBtn.addEventListener('click', function() {
                openReferralModal();
            });
        }

        // Invite button also opens referral modal
        if (inviteBtn) {
            inviteBtn.addEventListener('click', function() {
                openReferralModal();
            });
        }

        // Copy referral code
        if (copyReferralCode && referralCodeInput) {
            copyReferralCode.addEventListener('click', function() {
                try {
                    navigator.clipboard.writeText(referralCodeInput.value).then(function() {
                        // Success feedback
                        copyText.textContent = 'Đã sao chép!';
                        copyReferralCode.classList.remove('bg-blue-500', 'hover:bg-blue-600');
                        copyReferralCode.classList.add('bg-green-500');

                        setTimeout(function() {
                            copyText.textContent = 'Sao chép';
                            copyReferralCode.classList.remove('bg-green-500');
                            copyReferralCode.classList.add('bg-blue-500', 'hover:bg-blue-600');
                        }, 2000);
                    }).catch(function(err) {
                        // Fallback for older browsers
                        document.execCommand('copy');
                        copyText.textContent = 'Đã sao chép!';
                        setTimeout(function() {
                            copyText.textContent = 'Sao chép';
                        }, 2000);
                    });
                } catch (err) {
                    // Fallback for older browsers
                    document.execCommand('copy');
                    copyText.textContent = 'Đã sao chép!';
                    setTimeout(function() {
                        copyText.textContent = 'Sao chép';
                    }, 2000);
                }
            });
        }

        // Countdown to end of year
        function updateCountdown() {
            // Check if countdown elements exist before updating
            const daysEl = document.getElementById('countdown-days');
            const hoursEl = document.getElementById('countdown-hours');
            const minutesEl = document.getElementById('countdown-minutes');
            const secondsEl = document.getElementById('countdown-seconds');
            
            // If countdown elements don't exist, skip update
            if (!daysEl || !hoursEl || !minutesEl || !secondsEl) {
                return;
            }
            
            const now = new Date();
            const currentYear = now.getFullYear();
            const endOfYear = new Date(currentYear, 11, 31, 23, 59, 59); // December 31, 23:59:59

            const diff = endOfYear - now;

            if (diff <= 0) {
                // Năm đã kết thúc, đếm ngược tới cuối năm tiếp theo
                const nextYear = currentYear + 1;
                const nextEndOfYear = new Date(nextYear, 11, 31, 23, 59, 59);
                const nextDiff = nextEndOfYear - now;

                const days = Math.floor(nextDiff / (1000 * 60 * 60 * 24));
                const hours = Math.floor((nextDiff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((nextDiff % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((nextDiff % (1000 * 60)) / 1000);

                daysEl.textContent = String(days).padStart(2, '0');
                hoursEl.textContent = String(hours).padStart(2, '0');
                minutesEl.textContent = String(minutes).padStart(2, '0');
                secondsEl.textContent = String(seconds).padStart(2, '0');
            } else {
                const days = Math.floor(diff / (1000 * 60 * 60 * 24));
                const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((diff % (1000 * 60)) / 1000);

                daysEl.textContent = String(days).padStart(2, '0');
                hoursEl.textContent = String(hours).padStart(2, '0');
                minutesEl.textContent = String(minutes).padStart(2, '0');
                secondsEl.textContent = String(seconds).padStart(2, '0');
            }
        }

        // Update countdown immediately and then every second (only if elements exist)
        updateCountdown();
        setInterval(updateCountdown, 1000);

        // Lucky Money functionality
        const openLuckyMoneyBtn = document.getElementById('openLuckyMoneyBtn');
        const dailyCounter = document.getElementById('dailyCounter');

        // Load lucky money status
        function loadLuckyMoneyStatus() {
            // Check if elements exist before using them
            if (!openLuckyMoneyBtn && !dailyCounter) {
                return; // Elements don't exist, skip
            }
            
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ||
                document.querySelector('input[name="_token"]')?.value;

            fetch('/api/lucky-money/status', {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken || '',
                    },
                })
                .then(response => response.json())
                .then(data => {
                    if (openLuckyMoneyBtn) {
                        if (data.has_opened_today) {
                            openLuckyMoneyBtn.disabled = true;
                            openLuckyMoneyBtn.textContent = 'Đã mở';
                        } else {
                            openLuckyMoneyBtn.disabled = false;
                            openLuckyMoneyBtn.textContent = 'Mở';
                        }
                    }
                    
                    if (dailyCounter) {
                        if (data.has_opened_today) {
                            dailyCounter.textContent = '0/1';
                        } else {
                            dailyCounter.textContent = '1/1';
                        }
                    }
                })
                .catch(error => {
                    console.error('Error loading lucky money status:', error);
                });
        }

        // Open lucky money
        if (openLuckyMoneyBtn) {
            openLuckyMoneyBtn.addEventListener('click', async function() {
                if (openLuckyMoneyBtn.disabled) {
                    return;
                }

                openLuckyMoneyBtn.disabled = true;
                openLuckyMoneyBtn.textContent = 'Đang mở...';

                // Get CSRF token with fallback
                let csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ||
                    document.querySelector('input[name="_token"]')?.value;

                try {
                    let response = await fetch('/api/lucky-money/open', {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken || '',
                        },
                    });

                    // Handle 419 CSRF token mismatch
                    if (response.status === 419) {
                        // Try to refresh token and retry once
                        try {
                            const refreshResponse = await fetch('/csrf-token', {
                                method: 'GET',
                                credentials: 'same-origin',
                                headers: {
                                    'Accept': 'application/json',
                                },
                            });

                            if (refreshResponse.ok) {
                                const refreshData = await refreshResponse.json();
                                if (refreshData && refreshData.token) {
                                    // Update meta tag
                                    const metaTag = document.querySelector('meta[name="csrf-token"]');
                                    if (metaTag) {
                                        metaTag.setAttribute('content', refreshData.token);
                                    }
                                    csrfToken = refreshData.token;

                                    // Retry request with new token
                                    response = await fetch('/api/lucky-money/open', {
                                        method: 'POST',
                                        headers: {
                                            'X-Requested-With': 'XMLHttpRequest',
                                            'Accept': 'application/json',
                                            'Content-Type': 'application/json',
                                            'X-CSRF-TOKEN': csrfToken,
                                        },
                                    });
                                }
                            }
                        } catch (refreshError) {
                            console.error('Failed to refresh CSRF token:', refreshError);
                        }
                    }

                    const data = await response.json();

                    if (response.ok && data.message) {
                        // Show success modal
                        showLuckyMoneyModal(data.amount);

                        // Update UI
                        if (openLuckyMoneyBtn) {
                            openLuckyMoneyBtn.textContent = 'Đã mở';
                        }
                        if (dailyCounter) {
                            dailyCounter.textContent = '0/1';
                        }
                    } else {
                        const errorMsg = data.message || data.error || 'Có lỗi xảy ra';
                        if (typeof showToast === 'function') {
                            showToast(errorMsg, 'error');
                        }
                        if (openLuckyMoneyBtn) {
                            openLuckyMoneyBtn.disabled = false;
                            openLuckyMoneyBtn.textContent = 'Mở';
                        }
                    }
                } catch (error) {
                    console.error('Error opening lucky money:', error);
                    if (typeof showToast === 'function') {
                        showToast('Có lỗi xảy ra khi mở hộp quà. Vui lòng thử lại.', 'error');
                    }
                    if (openLuckyMoneyBtn) {
                        openLuckyMoneyBtn.disabled = false;
                        openLuckyMoneyBtn.textContent = 'Mở';
                    }
                }
            });
        }

        // Load status on page load
        loadLuckyMoneyStatus();
        loadDailyRewardStatus();
        loadTransactionStats();

        // Lucky Money Modal Functions
        function showLuckyMoneyModal(amount) {
            const modal = document.getElementById('luckyMoneySuccessModal');
            const amountElement = document.getElementById('luckyMoneyAmount');

            if (modal && amountElement) {
                // Update amount
                amountElement.textContent = number_format(amount, 2) + ' đá quý';

                // Show modal
                modal.classList.remove('hidden');
                modal.style.display = 'flex';

                // Trigger animation
                setTimeout(() => {
                    modal.classList.add('show');
                }, 10);
            }
        }

        function closeLuckyMoneyModal(event) {
            if (event) {
                event.preventDefault();
                event.stopPropagation();
            }
            const modal = document.getElementById('luckyMoneySuccessModal');
            if (modal) {
                modal.classList.remove('show');
                setTimeout(() => {
                    modal.style.display = 'none';
                    modal.classList.add('hidden');
                    // Reload page to update balance
                    window.location.reload();
                }, 300);
            }
        }

        function number_format(number, decimals) {
            decimals = decimals || 2;
            number = parseFloat(number);
            return number.toFixed(decimals).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        }
    </script>
@endpush
