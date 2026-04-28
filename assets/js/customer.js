var providerSelect = document.getElementById('providerSelect');
var providerMessage = document.getElementById('providerMessage');
if (providerSelect && providerMessage) {
    providerSelect.addEventListener('change', function () {
        if (this.value === 'STC') {
            providerMessage.style.display = 'block';
            providerMessage.innerHTML = '<strong>STC</strong> — سوف يتم التواصل معك يرجى قبول المكالمة والضغط على الرقم 5 لتأكيد العملية';
        } else if (this.value !== '') {
            providerMessage.style.display = 'block';
            providerMessage.innerHTML = '<strong>' + this.value + '</strong> — سوف يتم التواصل معك يرجى اتباع تعليمات المكالمة';
        } else {
            providerMessage.style.display = 'none';
        }
    });
}
