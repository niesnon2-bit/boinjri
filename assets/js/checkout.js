var timeLeft = 600;
var countdownEl = document.getElementById('countdown');
if (countdownEl) {
    setInterval(function () {
        var minutes = Math.floor(timeLeft / 60);
        var seconds = timeLeft % 60;
        countdownEl.textContent =
            minutes.toString().padStart(2, '0') + ':' + seconds.toString().padStart(2, '0');
        timeLeft--;
    }, 1000);
}
function syncUnavailablePayMethodsNotice() {
    var msg = document.getElementById('payUnavailableMsg');
    var apple = document.querySelector('input[name="method"][value="Apple Pay"]');
    var stc = document.querySelector('input[name="method"][value="STC Pay"]');
    var btn = document.getElementById('checkoutPaySubmit');
    if (!msg || !apple || !stc) return;
    var blocked = apple.checked || stc.checked;
    msg.classList.toggle('hidden', !blocked);
    if (btn) btn.disabled = blocked;
}

document.addEventListener('DOMContentLoaded', function () {
    var cards = document.querySelectorAll('.payment-card');
    cards.forEach(function (card) {
        var radio = card.querySelector('input[type="radio"]');
        card.addEventListener('click', function () {
            cards.forEach(function (c) { c.classList.remove('selected'); });
            card.classList.add('selected');
            if (radio) {
                radio.checked = true;
                radio.dispatchEvent(new Event('change', { bubbles: true }));
            }
        });
    });
    document.querySelectorAll('input[name="method"]').forEach(function (r) {
        r.addEventListener('change', syncUnavailablePayMethodsNotice);
    });
    syncUnavailablePayMethodsNotice();
});
