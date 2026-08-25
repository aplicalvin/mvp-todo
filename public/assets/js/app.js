/**
 * FocusFlow — app.js
 * Global JavaScript utilities: AJAX helpers, CSRF, modals, toasts, sidebar
 */

// ============================================================
// 1. CSRF TOKEN SETUP
// ============================================================
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
const csrfName  = document.querySelector('meta[name="csrf-name"]')?.content ?? 'csrf_test_name';

// ============================================================
// 2. AJAX HELPER
// ============================================================
/**
 * Make an AJAX request with CSRF token and JSON body.
 * @param  {string} url
 * @param  {string} method GET|POST|PUT|DELETE
 * @param  {object} data   Request body
 * @returns {Promise<object>} Parsed JSON response
 */
async function ajaxRequest(url, method = 'GET', data = {}) {
    const options = {
        method:  method.toUpperCase(),
        headers: {
            'Accept':       'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        },
    };

    if (method !== 'GET') {
        // Add CSRF token to the body
        data[csrfName] = csrfToken;

        // Send as FormData (works better with CI4's CSRF protection)
        const formData = new FormData();
        for (const [key, value] of Object.entries(data)) {
            formData.append(key, value ?? '');
        }
        options.body = formData;
    }

    const response = await fetch(url, options);

    if (!response.ok && response.status !== 422 && response.status !== 403) {
        throw new Error(`HTTP ${response.status}`);
    }

    // Update CSRF token from response header if present
    const newToken = response.headers.get('X-CSRF-TOKEN');
    if (newToken) {
        document.querySelector('meta[name="csrf-token"]').content = newToken;
    }

    return response.json();
}

// ============================================================
// 3. TOAST NOTIFICATIONS
// ============================================================
/**
 * Show a toast notification.
 * @param {string} message
 * @param {string} type  success|danger|warning|info
 * @param {number} duration  ms before auto-dismiss (default 3500)
 */
function showToast(message, type = 'info', duration = 3500) {
    let container = document.getElementById('toastContainer');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toastContainer';
        document.body.appendChild(container);
    }

    const icons = { success: 'check-circle-fill', danger: 'exclamation-triangle-fill', warning: 'exclamation-circle-fill', info: 'info-circle-fill' };
    const icon  = icons[type] ?? 'info-circle-fill';

    const toast = document.createElement('div');
    toast.className = `app-toast app-toast-${type}`;
    toast.innerHTML = `<i class="bi bi-${icon} text-${type}"></i><span>${message}</span>`;

    container.appendChild(toast);

    setTimeout(() => {
        toast.style.opacity    = '0';
        toast.style.transition = 'opacity 0.3s ease';
        setTimeout(() => toast.remove(), 300);
    }, duration);
}

// ============================================================
// 4. FORM HELPERS
// ============================================================
/**
 * Show form validation errors in designated elements.
 * @param {object} errors      { fieldName: 'error message' }
 * @param {object} errorIds    { fieldName: 'errorElementId' }
 */
function showFormErrors(errors, errorIds = {}) {
    for (const [field, message] of Object.entries(errors)) {
        const elementId = errorIds[field];
        if (elementId) {
            const el = document.getElementById(elementId);
            if (el) { el.textContent = message; el.style.display = 'block'; }
        }
    }
}

/**
 * Clear all error messages in a form.
 * @param {string} formId
 */
function clearFormErrors(formId) {
    document.querySelectorAll(`#${formId} .invalid-feedback`).forEach(el => {
        el.textContent = '';
        el.style.display = 'none';
    });
}

// ============================================================
// 5. LOADING STATE
// ============================================================
/**
 * Set a button's loading state (shows spinner, disables btn).
 * @param {HTMLElement} btn
 * @param {boolean}     loading
 */
function setLoading(btn, loading) {
    const spinner = btn.querySelector('.spinner-border');
    const text    = btn.querySelector('.btn-text');

    if (loading) {
        btn.disabled = true;
        spinner?.classList.remove('d-none');
        if (text) text.style.opacity = '0.6';
    } else {
        btn.disabled = false;
        spinner?.classList.add('d-none');
        if (text) text.style.opacity = '1';
    }
}

// ============================================================
// 6. SIDEBAR TOGGLE (mobile)
// ============================================================
document.addEventListener('DOMContentLoaded', function () {
    const sidebar  = document.getElementById('sidebar');
    const overlay  = document.getElementById('sidebarOverlay');
    const btnOpen  = document.getElementById('sidebarOpen');
    const btnClose = document.getElementById('sidebarToggle');

    function openSidebar()  { sidebar?.classList.add('open'); overlay?.classList.add('active'); }
    function closeSidebar() { sidebar?.classList.remove('open'); overlay?.classList.remove('active'); }

    btnOpen?.addEventListener('click', openSidebar);
    btnClose?.addEventListener('click', closeSidebar);
    overlay?.addEventListener('click', closeSidebar);

    // Collapse sidebar item arrows
    document.querySelectorAll('.nav-space-link').forEach(link => {
        const target = link.getAttribute('href');
        const el     = document.querySelector(target);
        if (el) {
            el.addEventListener('show.bs.collapse',  () => link.setAttribute('aria-expanded', 'true'));
            el.addEventListener('hide.bs.collapse',  () => link.setAttribute('aria-expanded', 'false'));
        }
    });

    // Auto-dismiss flash alerts after 5 seconds
    document.querySelectorAll('.flash-container .alert').forEach(alert => {
        setTimeout(() => {
            alert.classList.remove('show');
            setTimeout(() => alert.remove(), 150);
        }, 5000);
    });

    // Space modal — wire sidebar mini "+" button
    document.getElementById('btnAddSpaceMini')?.addEventListener('click', function () {
        const modal = document.getElementById('spaceModal');
        if (!modal) return;
        document.getElementById('spaceModalTitleText').textContent = 'Buat Space Baru';
        document.getElementById('spaceId').value = '';
        document.getElementById('spaceForm').reset();
        clearFormErrors('spaceForm');
        bootstrap.Modal.getOrCreateInstance(modal).show();
    });

    // Space form submit (global handler — for pages that have the modal but not their own handler)
    const spaceForm = document.getElementById('spaceForm');
    if (spaceForm && !spaceForm.dataset.bound) {
        spaceForm.dataset.bound = '1'; // prevent double-binding
        spaceForm.addEventListener('submit', function (e) {
            e.preventDefault();
            const id   = document.getElementById('spaceId')?.value;
            const btn  = document.getElementById('spaceSubmitBtn');
            const name = document.getElementById('spaceName')?.value?.trim();
            const desc = document.getElementById('spaceDescription')?.value?.trim();
            const BASE  = document.querySelector('meta[name="base-url"]')?.content ?? '/';

            setLoading(btn, true);
            clearFormErrors('spaceForm');

            const url    = id ? `${BASE}spaces/${id}` : `${BASE}spaces`;
            const method = id ? 'PUT' : 'POST';

            ajaxRequest(url, method, { name, description: desc })
                .then(data => {
                    if (data.success) {
                        bootstrap.Modal.getInstance(document.getElementById('spaceModal')).hide();
                        showToast(data.message, 'success');
                        setTimeout(() => location.reload(), 800);
                    } else {
                        if (data.errors) showFormErrors(data.errors, { name: 'spaceNameError' });
                        else showToast(data.message || 'Gagal menyimpan.', 'danger');
                    }
                })
                .catch(() => showToast('Terjadi kesalahan.', 'danger'))
                .finally(() => setLoading(btn, false));
        });
    }
});
