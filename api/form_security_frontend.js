/**
 * Frontend compartilhado em https://soarinho.com/api/form_security_frontend.js
 *
 * Faz:
 * - Honeypot invisível (input name="website")
 * - Time trap (hidden start_time em ms)
 * - CSRF (cookie csrf_token + hidden csrf_token)
 * - Leitura de ?status=&msg= para exibir alert de sucesso/erro
 *
 * Toda validação crítica é feita no backend.
 */

(function () {
    function uuid4() {
        if (window.crypto && window.crypto.getRandomValues) {
            const array = new Uint8Array(16);
            window.crypto.getRandomValues(array);
            return Array.from(array, b => ('0' + b.toString(16)).slice(-2)).join('');
        }
        return Math.random().toString(36).substring(2) + Date.now().toString(36);
    }

    function getCookie(name) {
        const value = `; ${document.cookie}`;
        const parts = value.split(`; ${name}=`);
        if (parts.length === 2) return parts.pop().split(';').shift();
        return null;
    }

    function setCookie(name, value, days) {
        let expires = '';
        if (days) {
            const date = new Date();
            date.setTime(date.getTime() + days * 24 * 60 * 60 * 1000);
            expires = '; expires=' + date.toUTCString();
        }
        document.cookie = name + '=' + encodeURIComponent(value) + expires + '; path=/; SameSite=Lax';
    }

    function ensureCsrfToken() {
        let token = getCookie('csrf_token');
        if (!token) {
            token = uuid4();
            setCookie('csrf_token', token, 1);
        }
        return token;
    }

    function setupForm(form) {
        if (!form) return;

        // Honeypot
        let honeypot = form.querySelector('input[name="website"]');
        if (!honeypot) {
            honeypot = document.createElement('input');
            honeypot.type = 'text';
            honeypot.name = 'website';
            honeypot.autocomplete = 'off';
            honeypot.tabIndex = -1;
            honeypot.setAttribute('aria-hidden', 'true');
            honeypot.style.position = 'absolute';
            honeypot.style.left = '-9999px';
            honeypot.style.opacity = '0';
            form.appendChild(honeypot);
        }

        // Time trap
        let startInput = form.querySelector('input[name="start_time"]');
        if (!startInput) {
            startInput = document.createElement('input');
            startInput.type = 'hidden';
            startInput.name = 'start_time';
            form.appendChild(startInput);
        }
        startInput.value = String(Date.now());

        // CSRF
        const csrfToken = ensureCsrfToken();
        let csrfInput = form.querySelector('input[name="csrf_token"]');
        if (!csrfInput) {
            csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = 'csrf_token';
            form.appendChild(csrfInput);
        }
        csrfInput.value = csrfToken;
    }

    function showStatusMessage() {
        const params = new URLSearchParams(window.location.search);
        const status = params.get('status');
        const msg = params.get('msg');
        if (!status) return;

        const text = msg || (status === 'ok'
            ? 'Mensagem enviada com sucesso!'
            : 'Não foi possível enviar sua mensagem. Tente novamente.');

        alert(text);

        if (window.history && window.history.replaceState) {
            const url = window.location.origin + window.location.pathname + window.location.hash;
            window.history.replaceState({}, document.title, url);
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        const forms = [];
        const byId = ['contactForm', 'support-form'];
        byId.forEach(id => {
            const el = document.getElementById(id);
            if (el) forms.push(el);
        });
        if (!forms.length) {
            document.querySelectorAll('form').forEach(f => forms.push(f));
        }

        forms.forEach(setupForm);
        showStatusMessage();
    });
})();

