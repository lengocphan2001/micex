/**
 * CSRF Token Management
 * Automatically refreshes CSRF token and handles token mismatch errors
 */

(function() {
    'use strict';

    // Get CSRF token from meta tag
    function getCsrfToken() {
        const metaTag = document.querySelector('meta[name="csrf-token"]');
        const token = metaTag ? metaTag.getAttribute('content') : null;
        if (token) return token;
        // Fallback for pages that only have Blade hidden inputs
        const input = document.querySelector('input[name="_token"]');
        return input ? input.value : null;
    }

    // Apply token to meta tag + all Blade hidden _token inputs
    function applyCsrfToken(token) {
        if (!token) return;

        const metaTag = document.querySelector('meta[name="csrf-token"]');
        if (metaTag) {
            metaTag.setAttribute('content', token);
        }

        // Keep all already-rendered forms in sync
        document.querySelectorAll('input[name="_token"]').forEach((el) => {
            try {
                el.value = token;
            } catch (_) {
                // ignore
            }
        });
    }

    // Refresh CSRF token from server
    async function refreshCsrfToken() {
        try {
            const response = await fetch('/csrf-token', {
                method: 'GET',
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                },
            });

            if (response.ok) {
                const data = await response.json();
                if (data && data.token) {
                    // Update meta tag + all hidden inputs
                    applyCsrfToken(data.token);
                    return data.token;
                }
            } else {
                console.error('Failed to refresh CSRF token. Status:', response.status);
                // If 500 error, try to get token from meta tag
                const metaTag = document.querySelector('meta[name="csrf-token"]');
                if (metaTag) {
                    return metaTag.getAttribute('content');
                }
            }
        } catch (error) {
            console.error('Failed to refresh CSRF token:', error);
            // Fallback: try to get token from meta tag
            const metaTag = document.querySelector('meta[name="csrf-token"]');
            if (metaTag) {
                return metaTag.getAttribute('content');
            }
        }
        return null;
    }

    // NOTE: We intentionally do NOT auto-refresh CSRF on timers/focus.
    // Auto-refreshing while pages are open tends to rotate tokens unexpectedly and
    // can cause 419s when some JS submits stale form data.
    function setupCsrfTokenRefresh() {}

    // Enhanced fetch wrapper that handles CSRF token automatically
    window.csrfFetch = async function(url, options = {}) {
        const csrfToken = getCsrfToken();
        
        if (!csrfToken) {
            console.error('CSRF token not found');
            throw new Error('CSRF token not available');
        }

        // Merge headers (only set JSON content-type if caller didn't provide one
        // and the body is not FormData)
        const headers = {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
            ...(options.headers || {}),
        };

        const body = options.body;
        const isFormData = (typeof FormData !== 'undefined') && (body instanceof FormData);
        if (!isFormData && !('Content-Type' in headers)) {
            headers['Content-Type'] = 'application/json';
        }

        const fetchOptions = {
            ...options,
            headers,
            credentials: 'same-origin',
        };

        try {
            let response = await fetch(url, fetchOptions);

            // Handle 419 CSRF token mismatch
            if (response.status === 419) {
                // Try to refresh token and retry once
                const newToken = await refreshCsrfToken();
                if (newToken) {
                    // Update headers with new token
                    fetchOptions.headers['X-CSRF-TOKEN'] = newToken;
                    response = await fetch(url, fetchOptions);
                    
                    // If still 419 after retry, reload page
                    if (response.status === 419) {
                        if (window.showToast) {
                            window.showToast('Phiên đăng nhập đã hết hạn. Đang tải lại trang...', 'error');
                        }
                        setTimeout(() => {
                            window.location.reload();
                        }, 1000);
                        throw new Error('CSRF token mismatch - page will reload');
                    }
                } else {
                    // If refresh fails, reload page
                    if (window.showToast) {
                        window.showToast('Phiên đăng nhập đã hết hạn. Đang tải lại trang...', 'error');
                    }
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                    throw new Error('CSRF token mismatch - page will reload');
                }
            }

            return response;
        } catch (error) {
            console.error('Fetch error:', error);
            throw error;
        }
    };

    // Initialize on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', setupCsrfTokenRefresh);
    } else {
        setupCsrfTokenRefresh();
    }

    // Export for global use
    window.getCsrfToken = getCsrfToken;
    window.applyCsrfToken = applyCsrfToken;
    window.refreshCsrfToken = refreshCsrfToken;
})();

