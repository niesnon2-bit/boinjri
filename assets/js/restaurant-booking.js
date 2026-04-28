document.addEventListener('DOMContentLoaded', function () {
    var MIN_CHARGE = window.BUJAIRI_MIN_CHARGE || 50;
    var selectedTimeBtn = null;
    var dateSelect = document.getElementById('dateSelect');
    if (!dateSelect) return;
    var today = new Date();
    for (var i = 0; i < 10; i++) {
        var newDate = new Date();
        newDate.setDate(today.getDate() + i);
        var formattedValue = newDate.toISOString().split('T')[0];
        var formattedText = newDate.toLocaleDateString('ar-SA', {
            weekday: 'long', day: 'numeric', month: 'long', year: 'numeric'
        });
        var option = document.createElement('option');
        option.value = formattedValue;
        option.textContent = formattedText;
        dateSelect.appendChild(option);
    }
    var container = document.getElementById('timeSlotsContainer');
    var startHour = 9, endHour = 25;
    for (var hour = startHour; hour < endHour; hour++) {
        for (var mi = 0; mi < 2; mi++) {
            var min = mi === 0 ? 0 : 30;
            var displayHour24 = hour % 24;
            var period = displayHour24 >= 12 ? 'م' : 'ص';
            var displayHour12 = displayHour24 % 12 === 0 ? 12 : displayHour24 % 12;
            var hh = displayHour12.toString().padStart(2, '0');
            var mm = min.toString().padStart(2, '0');
            var timeText = hh + ':' + mm + ' ' + period;
            var btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'time-slot border rounded-lg py-2 text-sm transition font-medium';
            btn.innerText = timeText;
            (function (b) {
                b.onclick = function () { selectTime(b); };
            })(btn);
            container.appendChild(btn);
        }
    }
    var MAX_GUESTS = window.BUJAIRI_MAX_GUESTS || 20;
    window.updateQty = function (delta) {
        var input = document.getElementById('guestCount');
        if (!input) return;
        var current = parseInt(input.value, 10);
        if (isNaN(current)) current = 1;
        var newVal = current + delta;
        if (newVal < 1) newVal = 1;
        if (newVal > MAX_GUESTS) newVal = MAX_GUESTS;
        input.value = newVal;
        updateTotal(newVal);
    };
    function updateTotal(qty) {
        var total = qty * MIN_CHARGE;
        var el = document.getElementById('totalPrice');
        if (el) el.textContent = total.toFixed(2) + ' ريال';
        var q = document.getElementById('totalQty');
        if (q) q.textContent = String(qty);
    }
    (function initTotal() {
        var input = document.getElementById('guestCount');
        var qty = input ? parseInt(input.value, 10) : 1;
        if (isNaN(qty) || qty < 1) qty = 1;
        updateTotal(qty);
    })();
    window.selectTime = function (btn) {
        if (selectedTimeBtn) selectedTimeBtn.classList.remove('selected');
        btn.classList.add('selected');
        selectedTimeBtn = btn;
        var st = document.getElementById('selectedTimeInput');
        if (st) st.value = btn.innerText;
    };
    var bf = document.getElementById('bookingForm');
    if (bf) {
        bf.addEventListener('submit', function (e) {
            if (!selectedTimeBtn) e.preventDefault();
        });
    }
});
