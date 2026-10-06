import './bootstrap';

const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');

// القائمة الجانبية: تصغير على سطح المكتب، وفتح/إغلاق كدرج على الشاشات الصغيرة
function initSidebar() {
    const toggle = document.getElementById('sidebarToggle');
    if (!toggle) {
        return;
    }

    const root = document.documentElement;
    const body = document.body;
    const desktop = window.matchMedia('(min-width: 992px)');
    const closeMobile = () => body.classList.remove('sidebar-open');

    toggle.addEventListener('click', () => {
        if (desktop.matches) {
            const collapsed = root.classList.toggle('sidebar-collapsed');
            try { localStorage.setItem('sidebar-collapsed', collapsed ? '1' : '0'); } catch (e) {}
            // الرسوم البيانية تحتاج إعادة قياس بعد انتهاء حركة القائمة
            setTimeout(() => window.dispatchEvent(new Event('resize')), 320);
        } else {
            body.classList.toggle('sidebar-open');
        }
    });

    document.querySelectorAll('[data-sidebar-close]').forEach(el => el.addEventListener('click', closeMobile));
    document.querySelectorAll('.sidebar .nav-link').forEach(el => el.addEventListener('click', () => {
        if (!desktop.matches) closeMobile();
    }));
    document.addEventListener('keydown', e => { if (e.key === 'Escape') closeMobile(); });

    // إيقاف الانتقالات أثناء عبور نقطة التحول حتى لا تنزلق القائمة أمام المستخدم
    desktop.addEventListener('change', () => {
        root.classList.add('resizing');
        closeMobile();
        requestAnimationFrame(() => requestAnimationFrame(() => root.classList.remove('resizing')));
    });
}

// الوضع الداكن: يُحفظ اختيار المستخدم ويُبلَّغ الرسوم البيانية بالتغيير
function initTheme() {
    const toggle = document.getElementById('themeToggle');
    if (!toggle) {
        return;
    }

    const root = document.documentElement;
    const meta = document.querySelector('meta[name="theme-color"]');
    const sync = () => meta?.setAttribute('content', root.getAttribute('data-bs-theme') === 'dark' ? '#0b1120' : '#4f46e5');
    sync();

    const apply = next => {
        root.setAttribute('data-bs-theme', next);
        try { localStorage.setItem('theme', next); } catch (e) {}
        sync();
        window.dispatchEvent(new CustomEvent('themechange', { detail: { theme: next } }));
    };

    toggle.addEventListener('click', () => {
        const next = root.getAttribute('data-bs-theme') === 'dark' ? 'light' : 'dark';

        // دائرة تتسع من مكان الزر في المتصفحات التي تدعم View Transitions
        if (document.startViewTransition && !reducedMotion.matches) {
            const rect = toggle.getBoundingClientRect();
            const x = rect.left + rect.width / 2;
            const y = rect.top + rect.height / 2;
            const radius = Math.hypot(Math.max(x, innerWidth - x), Math.max(y, innerHeight - y));
            root.style.setProperty('--vt-x', `${x}px`);
            root.style.setProperty('--vt-y', `${y}px`);
            root.style.setProperty('--vt-r', `${radius}px`);
            root.classList.add('theme-vt');
            document.startViewTransition(() => apply(next)).finished.finally(() => root.classList.remove('theme-vt'));
            return;
        }

        root.classList.add('theme-transition');
        apply(next);
        setTimeout(() => root.classList.remove('theme-transition'), 350);
    });
}

// الإشعارات المنبثقة: تختفي عند انتهاء شريط التقدم أو بالضغط على زر الإغلاق
function initToasts() {
    const hide = toast => {
        if (toast.classList.contains('is-hiding')) return;
        toast.classList.add('is-hiding');
        toast.addEventListener('animationend', () => toast.remove(), { once: true });
    };

    document.querySelectorAll('.app-toast').forEach(toast => {
        toast.querySelector('.app-toast-progress')?.addEventListener('animationend', () => hide(toast));
        toast.querySelector('[data-toast-close]')?.addEventListener('click', () => hide(toast));
    });
}

// مودال تأكيد موحّد بدل confirm() الخاص بالمتصفح
function initConfirm() {
    const modalEl = document.getElementById('confirmModal');
    if (!modalEl || !window.bootstrap) {
        window.confirmAction = ({ message }) => Promise.resolve(window.confirm(message));
        return;
    }

    const modal = window.bootstrap.Modal.getOrCreateInstance(modalEl);
    const content = modalEl.querySelector('.confirm-modal');
    const title = modalEl.querySelector('#confirmModalTitle');
    const text = modalEl.querySelector('[data-confirm-message]');
    const icon = modalEl.querySelector('.confirm-icon i');
    const ok = modalEl.querySelector('[data-confirm-ok]');
    let resolver = null;

    const settle = value => {
        if (resolver) {
            resolver(value);
            resolver = null;
        }
    };

    ok.addEventListener('click', () => {
        settle(true);
        modal.hide();
    });
    modalEl.addEventListener('hidden.bs.modal', () => settle(false));
    modalEl.addEventListener('shown.bs.modal', () => ok.focus());

    window.confirmAction = ({ message, title: heading, variant = 'primary', okText } = {}) => {
        settle(false);
        const danger = variant === 'danger';
        content.classList.toggle('is-danger', danger);
        title.textContent = heading || (danger ? 'تأكيد الحذف' : 'تأكيد العملية');
        text.textContent = message || 'هل أنت متأكد من تنفيذ هذه العملية؟';
        icon.className = danger ? 'far fa-trash-can' : 'fas fa-question';
        ok.className = `btn flex-fill ${danger ? 'btn-danger' : 'btn-primary'}`;
        ok.textContent = okText || (danger ? 'نعم، احذف' : 'تأكيد');

        return new Promise(resolve => {
            resolver = resolve;
            modal.show();
        });
    };

    document.addEventListener('click', event => {
        const trigger = event.target.closest('[data-confirm]');
        if (!trigger || trigger.dataset.confirmed === '1') {
            return;
        }

        event.preventDefault();
        const form = trigger.form || trigger.closest('form');
        // لا نسأل عن التأكيد قبل أن تكون الحقول المطلوبة صحيحة
        if (form && !form.checkValidity()) {
            form.reportValidity();
            return;
        }
        const isDelete = form?.querySelector('input[name="_method"]')?.value?.toUpperCase() === 'DELETE';

        window.confirmAction({
            message: trigger.dataset.confirm,
            title: trigger.dataset.confirmTitle,
            okText: trigger.dataset.confirmOk,
            variant: trigger.dataset.confirmVariant || (isDelete ? 'danger' : 'primary'),
        }).then(confirmed => {
            if (!confirmed) return;
            if (form) {
                form.requestSubmit ? form.requestSubmit(trigger.type === 'submit' ? trigger : undefined) : form.submit();
            } else if (trigger.href) {
                window.location.href = trigger.href;
            }
        });
    });
}

// منع الإرسال المزدوج: الزر يتعطل ويظهر عليه مؤشر تحميل
function initSubmitLoading() {
    document.addEventListener('submit', event => {
        const form = event.target;
        if (event.defaultPrevented || form.method.toLowerCase() !== 'post' || form.target === '_blank' || form.hasAttribute('data-no-loading')) {
            return;
        }

        if (form.dataset.submitting === '1') {
            event.preventDefault();
            return;
        }
        form.dataset.submitting = '1';

        const button = event.submitter || form.querySelector('[type="submit"]');
        // التأجيل حتى تُجمع بيانات النموذج، وإلا سيسقط اسم الزر وقيمته من الطلب
        setTimeout(() => {
            if (!button) return;
            button.disabled = true;
            button.classList.add('is-loading');
            button.insertAdjacentHTML('afterbegin', '<span class="spinner-border btn-spinner" aria-hidden="true"></span>');
        }, 0);
    });

    // عند الرجوع للصفحة من ذاكرة المتصفح نعيد الأزرار لحالتها
    window.addEventListener('pageshow', event => {
        if (!event.persisted) return;
        document.querySelectorAll('form[data-submitting]').forEach(form => delete form.dataset.submitting);
        document.querySelectorAll('.btn.is-loading').forEach(button => {
            button.disabled = false;
            button.classList.remove('is-loading');
            button.querySelector('.btn-spinner')?.remove();
        });
    });
}

// عدّاد متحرك لأرقام الإحصائيات (القيمة النهائية مكتوبة في الصفحة أصلاً)
function initCounters() {
    if (reducedMotion.matches) {
        return;
    }

    const run = el => {
        const target = parseFloat(el.dataset.count);
        const decimals = parseInt(el.dataset.decimals || '0', 10);
        const format = value => value.toLocaleString('en-US', { minimumFractionDigits: decimals, maximumFractionDigits: decimals });
        const duration = 1100;
        let start = null;

        const step = now => {
            start ??= now;
            const progress = Math.min((now - start) / duration, 1);
            const eased = 1 - Math.pow(1 - progress, 4);
            el.textContent = format(target * eased);
            if (progress < 1) requestAnimationFrame(step);
        };

        requestAnimationFrame(step);
    };

    // العدّ يبدأ عندما يظهر الرقم على الشاشة
    const observer = 'IntersectionObserver' in window
        ? new IntersectionObserver(entries => entries.forEach(entry => {
            if (!entry.isIntersecting) return;
            observer.unobserve(entry.target);
            run(entry.target);
        }), { threshold: .4 })
        : null;

    document.querySelectorAll('[data-count]').forEach(el => {
        const target = parseFloat(el.dataset.count);
        if (!Number.isFinite(target) || target === 0) return;
        const decimals = parseInt(el.dataset.decimals || '0', 10);
        el.textContent = (0).toLocaleString('en-US', { minimumFractionDigits: decimals, maximumFractionDigits: decimals });
        observer ? observer.observe(el) : run(el);
    });
}

function initTooltips() {
    if (!window.bootstrap) return;
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
        window.bootstrap.Tooltip.getOrCreateInstance(el, { trigger: 'hover' });
    });
}

// خلفية تنزلق خلف عنصر القائمة الذي يمر عليه الماوس
function initNavPill() {
    const nav = document.getElementById('sidebarNav');
    const pill = nav?.querySelector('.nav-hover-pill');
    if (!nav || !pill || !window.matchMedia('(hover: hover)').matches) {
        return;
    }

    nav.classList.add('has-pill');
    let visible = false;

    nav.addEventListener('mouseover', event => {
        const link = event.target.closest('.nav-link');
        if (!link) return;
        // أول ظهور يكون في المكان مباشرة بدون انزلاق من أعلى القائمة
        pill.classList.toggle('no-slide', !visible);
        pill.style.transform = `translateY(${link.offsetTop}px)`;
        pill.style.height = `${link.offsetHeight}px`;
        pill.classList.add('is-visible');
        if (!visible) requestAnimationFrame(() => pill.classList.remove('no-slide'));
        visible = true;
    });

    nav.addEventListener('mouseleave', () => {
        pill.classList.remove('is-visible');
        visible = false;
    });
}

// طي/فتح مجموعات القائمة مع حفظ الحالة
function initNavGroups() {
    const key = 'nav-collapsed-groups';
    const save = () => {
        const closed = [...document.querySelectorAll('#sidebarNav .nav-group.is-collapsed')].map(group => group.dataset.group);
        try { localStorage.setItem(key, JSON.stringify(closed)); } catch (e) {}
    };

    document.querySelectorAll('#sidebarNav .nav-group-title').forEach(button => {
        button.addEventListener('click', () => {
            const group = button.closest('.nav-group');
            const collapsed = group.classList.toggle('is-collapsed');
            button.setAttribute('aria-expanded', String(!collapsed));
            save();
        });
    });
}

// اسم العنصر يطفو بجانب الأيقونة عندما تكون القائمة مصغرة
function initNavFlyout() {
    const nav = document.getElementById('sidebarNav');
    if (!nav) return;

    const desktop = window.matchMedia('(min-width: 992px)');
    const flyout = document.createElement('div');
    flyout.className = 'nav-flyout';
    flyout.setAttribute('aria-hidden', 'true');
    document.body.appendChild(flyout);

    const hide = () => flyout.classList.remove('is-visible');

    nav.addEventListener('mouseover', event => {
        const link = event.target.closest('.nav-link');
        if (!link || !desktop.matches || !document.documentElement.classList.contains('sidebar-collapsed')) {
            hide();
            return;
        }
        const rect = link.getBoundingClientRect();
        const badge = link.querySelector('.nav-badge');
        flyout.textContent = link.dataset.label + (badge ? ` (${badge.textContent.trim()})` : '');
        flyout.style.top = `${rect.top + rect.height / 2}px`;
        flyout.style.right = `${window.innerWidth - rect.left + 14}px`;
        flyout.classList.add('is-visible');
    });

    nav.addEventListener('mouseleave', hide);
    nav.addEventListener('scroll', hide, { passive: true });
}

// إضاءة تتبع الماوس داخل القائمة الجانبية
function initSpotlight() {
    const sidebar = document.getElementById('sidebar');
    if (!sidebar || reducedMotion.matches) return;

    let frame = null;
    sidebar.addEventListener('pointermove', event => {
        if (frame) return;
        frame = requestAnimationFrame(() => {
            const rect = sidebar.getBoundingClientRect();
            sidebar.style.setProperty('--mx', `${event.clientX - rect.left}px`);
            sidebar.style.setProperty('--my', `${event.clientY - rect.top}px`);
            frame = null;
        });
    });
}

// شريط تحميل أعلى الصفحة عند الانتقال لصفحة أخرى
function initPageProgress() {
    const bar = document.querySelector('.page-progress');
    if (!bar) return;

    const start = () => {
        bar.classList.remove('is-loading');
        void bar.offsetWidth;
        bar.classList.add('is-loading');
    };

    document.addEventListener('click', event => {
        const link = event.target.closest('a[href]');
        if (!link || event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
        if (link.target && link.target !== '_self') return;
        if (link.hasAttribute('download') || link.hasAttribute('data-bs-toggle') || link.hasAttribute('data-confirm')) return;

        const url = new URL(link.href, location.href);
        if (url.origin !== location.origin || !/^https?:$/.test(url.protocol)) return;
        if (url.pathname === location.pathname && url.search === location.search && url.hash) return;
        start();
    });

    // التأجيل حتى تنتهي كل مستمعات الإرسال، فلا يبدأ الشريط إذا ألغى أحدها الإرسال
    document.addEventListener('submit', event => {
        setTimeout(() => {
            if (!event.defaultPrevented && event.target.target !== '_blank') start();
        }, 0);
    });

    window.addEventListener('pageshow', event => {
        if (event.persisted) bar.classList.remove('is-loading');
    });
}

// تموّج عند الضغط على الأزرار وعناصر القائمة
function initRipple() {
    if (reducedMotion.matches) return;

    document.addEventListener('pointerdown', event => {
        const host = event.target.closest('.btn, .sidebar .nav-link, .icon-btn, .legend-chip, .back-to-top');
        if (!host || host.disabled) return;

        const rect = host.getBoundingClientRect();
        const size = Math.max(rect.width, rect.height) * 2.2;
        const ripple = document.createElement('span');
        ripple.className = 'ripple';
        ripple.style.width = ripple.style.height = `${size}px`;
        ripple.style.left = `${event.clientX - rect.left}px`;
        ripple.style.top = `${event.clientY - rect.top}px`;
        host.classList.add('ripple-host');
        host.appendChild(ripple);
        ripple.addEventListener('animationend', () => ripple.remove(), { once: true });
    });
}

// ميل ثلاثي الأبعاد خفيف لكروت الإحصائيات مع لمعة تتبع الماوس
function initTilt() {
    if (reducedMotion.matches || !window.matchMedia('(hover: hover)').matches) return;

    document.querySelectorAll('.stat-card').forEach(card => {
        card.addEventListener('pointermove', event => {
            const rect = card.getBoundingClientRect();
            const x = (event.clientX - rect.left) / rect.width;
            const y = (event.clientY - rect.top) / rect.height;
            card.classList.add('is-tilting');
            card.style.setProperty('--ry', `${(x - .5) * 8}deg`);
            card.style.setProperty('--rx', `${(.5 - y) * 8}deg`);
            card.style.setProperty('--sx', `${x * 100}%`);
            card.style.setProperty('--sy', `${y * 100}%`);
        });
        card.addEventListener('pointerleave', () => {
            card.classList.remove('is-tilting');
            card.style.removeProperty('--rx');
            card.style.removeProperty('--ry');
        });
    });
}

// ظل الشريط العلوي وزر العودة لأعلى حسب التمرير
function initScrollEffects() {
    const topbar = document.querySelector('.topbar');
    const backToTop = document.getElementById('backToTop');
    let ticking = false;

    const update = () => {
        const y = window.scrollY;
        topbar?.classList.toggle('is-scrolled', y > 8);
        backToTop?.classList.toggle('is-visible', y > 400);
        ticking = false;
    };

    window.addEventListener('scroll', () => {
        if (!ticking) {
            ticking = true;
            requestAnimationFrame(update);
        }
    }, { passive: true });

    backToTop?.addEventListener('click', () => window.scrollTo({ top: 0, behavior: reducedMotion.matches ? 'auto' : 'smooth' }));
    update();
}

initSidebar();
initNavPill();
initNavGroups();
initNavFlyout();
initSpotlight();
initPageProgress();
initRipple();
initTilt();
initScrollEffects();
initTheme();
initToasts();
initConfirm();
initSubmitLoading();
initCounters();
initTooltips();
