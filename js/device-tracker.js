// Device Fingerprinting and Tracking System
class DeviceTracker {
    constructor() {
        this.deviceId = null;
        this.init();
    }

    // Generate device fingerprint
    generateFingerprint() {
        const canvas = document.createElement('canvas');
        const ctx = canvas.getContext('2d');
        ctx.textBaseline = 'top';
        ctx.font = '14px Arial';
        ctx.fillText('Device fingerprint', 2, 2);
        const canvasFingerprint = canvas.toDataURL();

        const fingerprint = {
            userAgent: navigator.userAgent,
            language: navigator.language,
            platform: navigator.platform,
            screenResolution: `${screen.width}x${screen.height}`,
            colorDepth: screen.colorDepth,
            timezone: Intl.DateTimeFormat().resolvedOptions().timeZone,
            canvas: canvasFingerprint.substring(0, 50), // First 50 chars
            cookieEnabled: navigator.cookieEnabled,
            doNotTrack: navigator.doNotTrack,
            hardwareConcurrency: navigator.hardwareConcurrency || 0,
            maxTouchPoints: navigator.maxTouchPoints || 0,
            webgl: this.getWebGLFingerprint()
        };

        // Create hash from fingerprint
        return this.hashCode(JSON.stringify(fingerprint));
    }

    // Get WebGL fingerprint
    getWebGLFingerprint() {
        try {
            const canvas = document.createElement('canvas');
            const gl = canvas.getContext('webgl') || canvas.getContext('experimental-webgl');
            if (!gl) return 'no-webgl';
            
            const renderer = gl.getParameter(gl.RENDERER);
            const vendor = gl.getParameter(gl.VENDOR);
            return `${vendor}-${renderer}`.substring(0, 50);
        } catch (e) {
            return 'webgl-error';
        }
    }

    // Simple hash function
    hashCode(str) {
        let hash = 0;
        for (let i = 0; i < str.length; i++) {
            const char = str.charCodeAt(i);
            hash = ((hash << 5) - hash) + char;
            hash = hash & hash; // Convert to 32-bit integer
        }
        return Math.abs(hash).toString(36);
    }

    // Initialize device tracking
    init() {
        // Try to get existing device ID from localStorage
        this.deviceId = localStorage.getItem('device_id');
        
        if (!this.deviceId) {
            // Generate new device ID based on fingerprint
            this.deviceId = this.generateFingerprint();
            localStorage.setItem('device_id', this.deviceId);
        }

        // Store additional device info
        const deviceInfo = {
            deviceId: this.deviceId,
            lastSeen: new Date().toISOString(),
            userAgent: navigator.userAgent,
            screenResolution: `${screen.width}x${screen.height}`
        };
        
        localStorage.setItem('device_info', JSON.stringify(deviceInfo));
        
        // Send device info to server for tracking
        this.registerDevice();
    }

    // Register device with server
    async registerDevice() {
        try {
            const deviceInfo = JSON.parse(localStorage.getItem('device_info'));
            
            const response = await fetch('/ramalan-cuaca/api/register_device.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(deviceInfo)
            });
            
            if (response.ok) {
                const result = await response.json();
                console.log('Device registered:', result);
            }
        } catch (error) {
            console.log('Device registration failed:', error);
        }
    }

    // Get device ID
    getDeviceId() {
        return this.deviceId;
    }

    // Check if device is persistent (has been seen before)
    isPersistentDevice() {
        const deviceInfo = localStorage.getItem('device_info');
        if (!deviceInfo) return false;
        
        const info = JSON.parse(deviceInfo);
        const lastSeen = new Date(info.lastSeen);
        const now = new Date();
        const hoursSinceLastSeen = (now - lastSeen) / (1000 * 60 * 60);
        
        // Consider device persistent if seen within last 24 hours
        return hoursSinceLastSeen < 24;
    }

    // Update last seen timestamp
    updateLastSeen() {
        const deviceInfo = JSON.parse(localStorage.getItem('device_info') || '{}');
        deviceInfo.lastSeen = new Date().toISOString();
        localStorage.setItem('device_info', JSON.stringify(deviceInfo));
    }
}

// Initialize device tracker when DOM is loaded
let deviceTracker;
document.addEventListener('DOMContentLoaded', function() {
    deviceTracker = new DeviceTracker();
});

// Export for use in other scripts
if (typeof module !== 'undefined' && module.exports) {
    module.exports = DeviceTracker;
}