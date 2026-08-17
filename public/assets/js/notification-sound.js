// Generate a pleasant notification chime using Web Audio API
function playNotificationSound() {
    try {
        const ctx = new (window.AudioContext || window.webkitAudioContext)();
        const notes = [880, 1108.73, 1318.51]; // A5, C#6, E6 (A major chord)
        
        notes.forEach((freq, i) => {
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.connect(gain);
            gain.connect(ctx.destination);
            
            osc.type = 'sine';
            osc.frequency.value = freq;
            
            const startTime = ctx.currentTime + i * 0.12;
            gain.gain.setValueAtTime(0, startTime);
            gain.gain.linearRampToValueAtTime(0.15, startTime + 0.05);
            gain.gain.exponentialRampToValueAtTime(0.001, startTime + 0.6);
            
            osc.start(startTime);
            osc.stop(startTime + 0.7);
        });
    } catch (e) {
        // Web Audio not supported, silently fail
    }
}
