<!-- Toast Container -->
<div id="toastContainer" class="fixed top-4 left-1/2 transform -translate-x-1/2 z-50 pointer-events-none w-full max-w-[450px] px-4">
    <!-- Toast messages will be inserted here -->
</div>

<script>
    function showToast(message, type = 'error') {
        const container = document.getElementById('toastContainer');
        if (!container) return;

        // Create toast element
        const toast = document.createElement('div');
        toast.className = `pointer-events-auto mb-3 px-4 py-3 rounded-lg shadow-lg transition-all duration-300 transform translate-y-0 opacity-100 w-full ${
            type === 'error' 
                ? 'bg-red-500/90 text-white' 
                : type === 'success'
                ? 'bg-green-500/90 text-white'
                : 'bg-blue-500/90 text-white'
        }`;
        
        toast.innerHTML = `
            <div class="flex items-center gap-2">
                <span class="text-sm font-medium break-words">${escapeHtml(message)}</span>
            </div>
        `;

        // Add to container
        container.appendChild(toast);

        // Animate in
        setTimeout(() => {
            toast.style.transform = 'translateY(0)';
            toast.style.opacity = '1';
        }, 10);

        // Remove after 3 seconds
        setTimeout(() => {
            toast.style.transform = 'translateY(-20px)';
            toast.style.opacity = '0';
            setTimeout(() => {
                if (toast.parentNode) {
                    toast.parentNode.removeChild(toast);
                }
            }, 300);
        }, 3000);
    }
    
    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
</script>

