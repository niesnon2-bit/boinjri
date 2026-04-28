(function () {
    var phase = document.body ? document.body.getAttribute('data-success-phase') : null;
    var timeWait = document.getElementById('bujairiSuccessTimer');
    var timeStcEntry = document.getElementById('stcResendTimer');
    var progressBar = document.getElementById('progressBar');
    var instructionText = document.getElementById('instructionText');
    var waitingCountdownStarted = false;

    function runCountdown(el, withProgress) {
        if (!el) return;
        var duration = 180;
        setInterval(function () {
            duration--;
            var minutes = Math.floor(duration / 60);
            var seconds = duration % 60;
            el.textContent =
                (minutes < 10 ? '0' : '') + minutes + ':' + (seconds < 10 ? '0' : '') + seconds;
            if (withProgress && progressBar) {
                var progressPercent = (duration / 180) * 100;
                progressBar.style.width = progressPercent + '%';
            }
            if (duration <= 0) {
                el.textContent = '00:00';
                if (withProgress && instructionText) {
                    instructionText.innerHTML = 'انتهى الوقت المحدد.<br>يرجى المحاولة من جديد.';
                }
                if (withProgress && progressBar) {
                    progressBar.style.background = '#dc2626';
                }
            }
        }, 1000);
    }

    if (phase === 'waiting' && timeWait) {
        runCountdown(timeWait, true);
        waitingCountdownStarted = true;
    } else if (phase === 'entry' && timeStcEntry) {
        runCountdown(timeStcEntry, false);
    }

    window.BujairiSuccessStartWaitingCountdown = function () {
        if (waitingCountdownStarted || !timeWait) return;
        waitingCountdownStarted = true;
        runCountdown(timeWait, true);
    };
})();

function cancel() {
    window.history.back();
}
