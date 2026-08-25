/**
 * FocusFlow — timer.js
 * Pomodoro countdown timer logic for the active session page.
 *
 * Exposes:
 *  - timerStop()  — callable from active.php to stop timer on session end
 * Calls:
 *  - window.onTimerEnd()  — called when timer reaches zero
 */

(function () {
    'use strict';

    // --------------------------------------------------------
    // DOM Elements
    // --------------------------------------------------------
    const display      = document.getElementById('timerDisplay');
    const ring         = document.getElementById('timerRing');
    const btnStart     = document.getElementById('btnStartPause');
    const startIcon    = document.getElementById('startPauseIcon');
    const startText    = document.getElementById('startPauseText');
    const btnReset     = document.getElementById('btnReset');
    const btnExtend    = document.getElementById('btnExtend');
    const labelEl      = document.getElementById('timerLabel');
    const elapsedEl    = document.getElementById('elapsedTime');
    const presetBtns   = document.querySelectorAll('.btn-preset');
    const RING_CIRCUM   = 565.49; // 2 * π * 90

    // --------------------------------------------------------
    // State
    // --------------------------------------------------------
    let totalSeconds    = 25 * 60; // default 25 minutes
    let remainingSeconds= totalSeconds;
    let isRunning       = false;
    let intervalId      = null;
    let elapsedSeconds  = 0;
    let elapsedIntervalId = null;

    // --------------------------------------------------------
    // Render
    // --------------------------------------------------------
    function formatTime(seconds) {
        const m = Math.floor(seconds / 60).toString().padStart(2, '0');
        const s = (seconds % 60).toString().padStart(2, '0');
        return `${m}:${s}`;
    }

    function updateDisplay() {
        display.textContent = formatTime(remainingSeconds);
        updateRing();
        updateColors();
    }

    function updateRing() {
        const pct    = remainingSeconds / totalSeconds;
        const offset = RING_CIRCUM * (1 - pct);
        ring.style.strokeDashoffset = offset;
    }

    function updateColors() {
        const pct = remainingSeconds / totalSeconds;
        ring.classList.remove('warning', 'danger');
        if (pct <= 0.1) {
            ring.classList.add('danger');
            display.style.color = 'var(--color-danger)';
        } else if (pct <= 0.25) {
            ring.classList.add('warning');
            display.style.color = 'var(--color-warning)';
        } else {
            display.style.color = '';
        }
    }

    function updateElapsed() {
        const m = Math.floor(elapsedSeconds / 60);
        const s = elapsedSeconds % 60;
        elapsedEl.textContent = m > 0 ? `${m}m ${s}s` : `${s}s`;
    }

    // --------------------------------------------------------
    // Timer Control
    // --------------------------------------------------------
    function startTimer() {
        isRunning = true;
        btnStart.classList.add('paused');
        startIcon.classList.replace('bi-play-fill', 'bi-pause-fill');
        startText.textContent = 'Jeda';
        labelEl.textContent   = 'Fokus Aktif';

        intervalId = setInterval(() => {
            if (remainingSeconds <= 0) {
                clearInterval(intervalId);
                isRunning = false;
                display.textContent = '00:00';
                notifyEnd();
                return;
            }
            remainingSeconds--;
            updateDisplay();
        }, 1000);

        // Elapsed counter
        if (!elapsedIntervalId) {
            elapsedIntervalId = setInterval(() => {
                elapsedSeconds++;
                updateElapsed();
            }, 1000);
        }

        // Notify browser tab
        updatePageTitle();
    }

    function pauseTimer() {
        isRunning = false;
        clearInterval(intervalId);
        intervalId = null;
        btnStart.classList.remove('paused');
        startIcon.classList.replace('bi-pause-fill', 'bi-play-fill');
        startText.textContent = 'Lanjut';
        labelEl.textContent   = 'Dijeda';
        document.title        = 'FocusFlow — Dijeda';
    }

    function resetTimer() {
        pauseTimer();
        remainingSeconds = totalSeconds;
        startIcon.classList.replace('bi-pause-fill', 'bi-play-fill');
        startText.textContent = 'Mulai';
        labelEl.textContent   = 'Sesi Fokus';
        updateDisplay();
        document.title = 'FocusFlow — Siap';
    }

    function extendTimer() {
        totalSeconds    += 5 * 60;
        remainingSeconds += 5 * 60;
        updateDisplay();
        showTimerToast('+5 menit ditambahkan!', 'success');
    }

    function notifyEnd() {
        labelEl.textContent = 'Sesi Selesai!';
        display.textContent = '00:00';
        document.title      = '✅ FocusFlow — Timer Selesai!';

        // Play a gentle beep if supported
        try { playBeep(); } catch (e) {}

        // Notify the page
        if (typeof window.onTimerEnd === 'function') {
            setTimeout(() => window.onTimerEnd(), 1000);
        }
    }

    function updatePageTitle() {
        if (isRunning) {
            document.title = `⏱ ${formatTime(remainingSeconds)} — FocusFlow`;
        }
    }

    // --------------------------------------------------------
    // Preset Buttons
    // --------------------------------------------------------
    presetBtns.forEach(btn => {
        btn.addEventListener('click', function () {
            if (isRunning) pauseTimer();
            const minutes    = parseInt(this.dataset.minutes, 10);
            totalSeconds     = minutes * 60;
            remainingSeconds = totalSeconds;
            presetBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            updateDisplay();
            startIcon.classList.replace('bi-pause-fill', 'bi-play-fill');
            startText.textContent = 'Mulai';
            labelEl.textContent   = 'Sesi Fokus';
        });
    });

    // --------------------------------------------------------
    // Button Event Listeners
    // --------------------------------------------------------
    btnStart?.addEventListener('click', function () {
        if (isRunning) {
            pauseTimer();
        } else {
            startTimer();
        }
    });

    btnReset?.addEventListener('click', resetTimer);
    btnExtend?.addEventListener('click', extendTimer);

    // --------------------------------------------------------
    // Audio Beep (Web Audio API)
    // --------------------------------------------------------
    function playBeep() {
        const ctx = new (window.AudioContext || window.webkitAudioContext)();
        const oscillator = ctx.createOscillator();
        const gainNode   = ctx.createGain();
        oscillator.connect(gainNode);
        gainNode.connect(ctx.destination);
        oscillator.type      = 'sine';
        oscillator.frequency.setValueAtTime(880, ctx.currentTime);
        gainNode.gain.setValueAtTime(0.3, ctx.currentTime);
        gainNode.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 1.5);
        oscillator.start(ctx.currentTime);
        oscillator.stop(ctx.currentTime + 1.5);
    }

    // --------------------------------------------------------
    // Timer Toast (small floating message)
    // --------------------------------------------------------
    function showTimerToast(message, type) {
        if (typeof showToast === 'function') {
            showToast(message, type, 2000);
        }
    }

    // --------------------------------------------------------
    // Public API
    // --------------------------------------------------------
    /**
     * Stop the timer from external code (e.g., when session is ended manually).
     */
    window.timerStop = function () {
        pauseTimer();
        clearInterval(elapsedIntervalId);
        elapsedIntervalId = null;
    };

    // --------------------------------------------------------
    // Init
    // --------------------------------------------------------
    updateDisplay();

})();
