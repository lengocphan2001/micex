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
        min-height: 200px;
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
    
    #referralModal.show > div:last-child {
        transform: translateY(0);
        opacity: 1;
    }
</style>
@endpush

@section('header')
<header class="w-full px-4 py-4 flex items-center justify-between bg-gray-900 border-b border-gray-800">
    <div class="text-white text-xl font-bold">MICEX</div>
    <div class="flex items-center gap-4">
        <!-- Profile Icon -->
        <a href="#" class="text-white flex items-center justify-center">
            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
            </svg>
        </a>
        <!-- Gift Box with Badge -->
        <a href="#" class="text-white relative flex items-center justify-center">
            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"></path>
            </svg>
            <span class="absolute top-0 right-0 bg-red-500 text-white text-[8px] font-bold rounded-full min-w-[14px] h-[14px] flex items-center justify-center px-0.5 leading-none transform translate-x-1/2 -translate-y-1/2">New</span>
        </a>
        <!-- Notification Bell with Dropdown -->
        <div class="relative">
            <button id="notificationBtn" class="text-white relative flex items-center justify-center hover:opacity-80 transition-opacity">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                </svg>
                @if(isset($unreadCount) && $unreadCount > 0)
                <span class="absolute -top-1 -right-1 bg-red-500 text-white text-[10px] font-bold rounded-full min-w-[18px] h-[18px] flex items-center justify-center px-1">
                    {{ $unreadCount > 99 ? '99+' : $unreadCount }}
                </span>
                @endif
            </button>
            
            <!-- Dropdown Menu -->
            <div id="notificationDropdown" class="hidden absolute right-0 mt-2 w-[calc(100vw-2rem)] md:w-80 max-w-sm bg-[#0f1118] rounded-xl shadow-2xl border border-gray-700/50 z-50 max-h-[500px] overflow-hidden flex flex-col">
                <!-- Dropdown Header -->
                <div class="px-4 py-3 border-b border-gray-700/50 flex items-center justify-between bg-[#0f1118]">
                    <div class="flex items-center gap-2">
                        <h3 class="text-white font-semibold text-base">Thông báo</h3>
                        @if(isset($unreadCount) && $unreadCount > 0)
                        <span class="bg-red-500 text-white text-xs font-bold rounded-full px-2 py-0.5">
                            {{ $unreadCount }}
                        </span>
                        @endif
                    </div>
                    <a href="{{ route('notifications') }}" class="text-blue-400 text-sm hover:text-blue-300 font-medium transition-colors">Xem tất cả</a>
                </div>
                
                <!-- Notifications List -->
                <div class="overflow-y-auto hide-scrollbar flex-1">
                    @forelse($recentNotifications ?? [] as $notification)
                    <a href="{{ route('notifications') }}" class="block px-4 py-3 hover:bg-gray-800/50 border-b border-gray-700/30 transition-colors {{ !$notification->is_read ? 'bg-blue-500/10 border-l-2 border-l-blue-500' : '' }}" data-notification-id="{{ $notification->id }}">
                        <div class="flex items-start gap-3">
                            <div class="relative flex-shrink-0 mt-0.5">
                                @if($notification->type === 'deposit_approved' || $notification->type === 'withdraw_approved')
                                    <div class="w-8 h-8 rounded-full bg-green-500/20 flex items-center justify-center">
                                        <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                    </div>
                                @elseif($notification->type === 'deposit_rejected' || $notification->type === 'withdraw_rejected')
                                    <div class="w-8 h-8 rounded-full bg-red-500/20 flex items-center justify-center">
                                        <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </div>
                                @elseif($notification->type === 'promotion')
                                    <div class="w-8 h-8 rounded-full bg-yellow-500/20 flex items-center justify-center">
                                        <svg class="w-4 h-4 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"></path>
                                        </svg>
                                    </div>
                                @else
                                    <div class="w-8 h-8 rounded-full bg-blue-500/20 flex items-center justify-center">
                                        <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                                        </svg>
                                    </div>
                                @endif
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between gap-2">
                                    <p class="text-white font-semibold text-sm mb-1 flex-1">{{ $notification->title }}</p>
                                    @if(!$notification->is_read)
                                        <span class="w-2 h-2 bg-blue-500 rounded-full flex-shrink-0 mt-1.5"></span>
                                    @endif
                                </div>
                                <p class="text-gray-400 text-xs line-clamp-2 leading-relaxed mb-1">{{ $notification->message }}</p>
                                <p class="text-gray-500 text-xs">{{ $notification->created_at->diffForHumans() }}</p>
                            </div>
                        </div>
                    </a>
                    @empty
                    <div class="px-4 py-12 text-center">
                        <svg class="w-12 h-12 text-gray-600 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
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
    @if($sliders->count() > 0)
        <div class="mx-4 mt-4 relative">
            <div id="sliderCarousel" class="carousel slide">
                <div class="carousel-inner">
                    @foreach($sliders as $index => $slider)
                        <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                            <div class="rounded-xl p-6 relative overflow-hidden flex items-start slider-content border-[0.5px] border-[#FF9D00]" style="height: 200px; background: linear-gradient(to bottom, #3958F5, #111111);">
                                <!-- Left Content -->
                                <div class="flex-1 relative z-10 pr-4 slider-text">
                                    <div class="text-white/80 text-base font-semibold slider-badge">MICEX</div>
                                    <h2 class="text-white text-lg font-bold mb-2 slider-title">{{ $slider->title }}</h2>
                                    @if($slider->button_title)
                                        <button class="bg-white/90 text-blue-700 font-semibold px-4 py-2 rounded-full text-xs mb-3 hover:bg-white transition-all duration-300 hover:scale-105 slider-button">
                                            {{ $slider->button_title }}
                                        </button>
                                    @endif
                                    @if($slider->description)
                                        <p class="text-white/90 text-xs slider-description">{{ $slider->description }}</p>
                                    @endif
                                </div>
                                <!-- Right Image -->
                                @if($slider->image)
                                    <div class="flex-shrink-0 w-32 h-32 relative z-10 slider-image">
                                        <img src="{{ asset('storage/' . $slider->image) }}" alt="{{ $slider->title }}" class="w-full h-full object-cover rounded-lg">
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
                
                @if($sliders->count() > 1)
                    <!-- Dots Indicator -->
                    <div class="carousel-indicators">
                        @foreach($sliders as $index => $slider)
                            <button type="button" onclick="goToSlide({{ $index }})" class="{{ $index === 0 ? 'active' : '' }}"></button>
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
                <button class="bg-white/90 text-blue-700 font-semibold px-4 py-2 rounded-full text-sm mb-3 hover:bg-white transition-colors">
                    Vác cuốc lên đi cày nào ?
                </button>
                <p class="text-white/90 text-sm">Giới thiệu bạn bè ngay hôm nay để nhận phần thưởng liền tay !</p>
            </div>
        </div>
    @endif

    <!-- Login/Register Prompt - Only show if not authenticated -->
    @guest
    <div class="mx-4 mt-4 bg-gray-800 rounded-xl p-4 flex items-center gap-4">
        <div class="w-16 h-16 bg-gray-700 rounded-lg flex items-center justify-center flex-shrink-0 overflow-hidden">
            <img src="{{ asset('images/phone.png') }}" alt="Phone" class="w-full h-full object-contain">
        </div>
        <div class="flex-1">
            <h3 class="text-white font-semibold mb-1">Đăng nhập/Đăng ký</h3>
            <p class="text-gray-400 text-sm">Để bắt đầu hành trình kiếm tiền cùng Micex</p>
        </div>
        <a href="{{ route('login') }}" class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center flex-shrink-0 hover:bg-blue-600 transition-colors">
            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
            </svg>
        </a>
    </div>
    @endguest

    <!-- Gift/Reward Section -->
    <div class="mx-4 mt-3 rounded-xl p-6 relative overflow-hidden" style="background-color: #111111;">
        <!-- Spotlight Effect -->
        <div class="gift-spotlight"></div>
        <!-- Light Effect -->
        <div class="absolute top-0 left-1/2 transform -translate-x-1/2 w-40 h-40 bg-yellow-400/20 rounded-full blur-3xl"></div>
        <!-- Gift Box -->
        <div class="relative z-10 flex flex-col items-center">
            <div class="w-40 h-40 mb-4 relative gift-container">
                <!-- Gift Image -->
                <img src="{{ asset('images/gift.png') }}" alt="Gift Box" class="w-full h-full object-contain gift-image">
            </div>
            <h3 class="text-white text-base font-bold mb-2">Đào liền tay ! Ring quà về ngay?</h3>
            <p class="text-gray-400 text-xs mb-4">Phần thưởng lên tới 1000 USDT đang chờ bạn</p>
            <a href="{{ route('explore') }}" class="inline-block bg-blue-500 hover:bg-blue-600 text-white text-sm font-semibold px-6 py-3 rounded-lg mb-4 transition-colors text-center">
                Đào ngay
            </a>
            <!-- Countdown Timer -->
            <div class="flex gap-2 text-white text-xs items-center justify-center">
                <div class="flex items-center gap-1">
                    <div class="bg-gray-700 px-2 py-1 rounded font-mono font-bold">06</div>
                    <span class="text-gray-400">Ngày</span>
                </div>
                <div class="flex items-center gap-1">
                    <div class="bg-gray-700 px-2 py-1 rounded font-mono font-bold">23</div>
                    <span class="text-gray-400">giờ</span>
                </div>
                <div class="flex items-center gap-1">
                    <div class="bg-gray-700 px-2 py-1 rounded font-mono font-bold">52</div>
                    <span class="text-gray-400">phút</span>
                </div>
                <div class="flex items-center gap-1">
                    <div class="bg-gray-700 px-2 py-1 rounded font-mono font-bold">18</div>
                    <span class="text-gray-400">giây</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Referral Task Section -->
    <div class="flex flex-col gap-3 mx-4 mt-4 mb-20 border border-gray-700 rounded-xl p-3">
        <h3 class="text-white font-semibold mb-2">Hoàn thành nhiệm vụ giới thiệu bạn bè mới</h3>
        <p class="text-gray-400 text-sm mb-4">Cơ hội đào ra đá quý có phần thưởng giá trị lên tới 1000$</p>
        <button id="referralBtn" class="w-fit opacity-100 rounded-[20px] bg-blue-500 hover:bg-blue-600 text-white font-semibold px-4 py-1 flex items-center justify-center gap-2 transition-colors cursor-pointer">
            <span>Giới thiệu bạn bè ngay</span>
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
            </svg>
        </button>
        <p class=" opacity-100 rounded-[20px] border-[0.5px] border-[#636465] bg-[#111111] py-2 text-gray-500 text-xs text-center flex items-center justify-center">Bạn sẽ nhận được gói quà tặng 20 USDT sau khi hoàn tất</p>
    </div>

    <!-- Referral Code Modal -->
    <div id="referralModal" class="fixed inset-0 z-50 flex items-center justify-center hidden">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-black/50" onclick="closeReferralModalFunc()"></div>
        
        <!-- Popup Content -->
        <div class="relative bg-[#1e3a8a] rounded-3xl shadow-2xl pb-8 w-full max-w-[419px] mx-4 transform translate-y-4 opacity-0 transition-all duration-300 ease-out">
            <!-- Content -->
            <div class="px-6 pt-8">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-white font-semibold text-lg">Mã giới thiệu của bạn</h3>
                    <button id="closeReferralModal" onclick="closeReferralModalFunc()" class="text-gray-400 hover:text-white transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <div class="mb-6">
                    <p class="text-white text-sm mb-4">Chia sẻ mã này với bạn bè để nhận phần thưởng:</p>
                    <div class="flex items-center gap-2 bg-gray-900/50 rounded-lg p-3 border border-gray-700/50 overflow-hidden">
                        <input type="text" id="referralCodeInput" value="{{ Auth::user()->referral_code ?? '' }}" readonly class="flex-1 min-w-0 bg-transparent text-white font-semibold text-lg outline-none">
                    <button id="copyReferralCode" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition-colors cursor-pointer flex items-center gap-2 whitespace-nowrap flex-shrink-0">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                        </svg>
                        <span id="copyText" class="whitespace-nowrap">Sao chép</span>
                    </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

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
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            },
                        }).catch(err => console.error('Error marking notification as read:', err));
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

    // Referral Modal
    const referralBtn = document.getElementById('referralBtn');
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
</script>
@endpush
