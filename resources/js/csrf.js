/**
 * CSRF Token Management
 * Automatically refreshes CSRF token and handles token mismatch errors
 */

(function() {
    'use strict';

    // Get CSRF token from meta tag
    function getCsrfToken() {
        const metaTag = document.querySelector('meta[name="csrf-token"]');
        return metaTag ? metaTag.getAttribute('content') : null;
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
                    // Update meta tag
                    const metaTag = document.querySelector('meta[name="csrf-token"]');
                    if (metaTag) {
                        metaTag.setAttribute('content', data.token);
                    }
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

    // Setup automatic token refresh
    function setupCsrfTokenRefresh() {
        // Refresh token every 30 minutes (before session expires)
        setInterval(async () => {
            await refreshCsrfToken();
        }, 30 * 60 * 1000); // 30 minutes

        // Refresh token on page visibility change (user comes back to tab)
        document.addEventListener('visibilitychange', async () => {
            if (!document.hidden) {
                await refreshCsrfToken();
            }
        });

        // Refresh token on focus
        window.addEventListener('focus', async () => {
            await refreshCsrfToken();
        });
    }

    // Enhanced fetch wrapper that handles CSRF token automatically
    window.csrfFetch = async function(url, options = {}) {
        const csrfToken = getCsrfToken();
        
        if (!csrfToken) {
            console.error('CSRF token not found');
            throw new Error('CSRF token not available');
        }

        // Merge headers
        const headers = {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            ...options.headers,
        };

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
    window.refreshCsrfToken = refreshCsrfToken;
})();

