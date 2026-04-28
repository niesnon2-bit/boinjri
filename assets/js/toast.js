/**
 * عرض إشعار بسيط — بدون اعتراض النماذج تلقائياً
 */
function showToast(message, type) {
    type = type || 'success';
    var toast = document.createElement('div');
    toast.textContent = message;
    toast.style.cssText = 'position:fixed;bottom:30px;left:50%;transform:translateX(-50%);' +
        'background:' + (type === 'error' ? '#dc2626' : '#16a34a') + ';color:#fff;padding:14px 24px;' +
        'border-radius:12px;font-size:14px;z-index:9999;box-shadow:0 15px 35px rgba(0,0,0,0.2);';
    document.body.appendChild(toast);
    setTimeout(function () { if (toast.parentNode) toast.remove(); }, 2200);
}
