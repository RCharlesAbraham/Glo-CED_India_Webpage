/**
 * Path Configuration for Frontend
 * Detects the correct base path for API calls and asset loading
 * Works with both /STP/ subdirectory and domain root deployment
 */

(function() {
    // Detect if we're in /STP/ subdirectory or at domain root
    const currentPath = window.location.pathname;
    const basePath = currentPath.includes('/STP/') ? '/STP' : '';
    
    window.APP_CONFIG = {
        basePath: basePath,
        apiUrl: basePath + '/backend',
        assetsUrl: basePath + '/assets',
        pagesUrl: basePath + '/pages'
    };
    
    // Function to get API endpoint
    window.getApiUrl = function(endpoint) {
        return window.APP_CONFIG.apiUrl + '/' + endpoint.replace(/^\//,'');
    };
    
    // Function to get asset URL
    window.getAssetUrl = function(asset) {
        return window.APP_CONFIG.assetsUrl + '/' + asset.replace(/^\//,'');
    };
    
    // Auto-update dynamically generated content
    document.addEventListener('DOMContentLoaded', function() {
        if (window.APP_CONFIG.basePath) {
            // Update any hardcoded /STP/ paths if needed
            document.querySelectorAll('[action], [href], [src]').forEach(el => {
                if (el.action && el.action.includes('/STP/')) {
                    el.action = el.action.replace('/STP/', window.APP_CONFIG.basePath + '/');
                }
                if (el.href && el.href.includes('/STP/')) {
                    el.href = el.href.replace('/STP/', window.APP_CONFIG.basePath + '/');
                }
                if (el.src && el.src.includes('/STP/')) {
                    el.src = el.src.replace('/STP/', window.APP_CONFIG.basePath + '/');
                }
            });
        }
    });
})();
