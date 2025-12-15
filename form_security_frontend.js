/**
 * Frontend compartilhado para proteção de formulários.
 *
 * Regras aplicadas no cliente:
 * - Gera e preenche campo de honeypot invisível (name="website")
 * - Gera timestamp de início (start_time em ms)
 * - Gera token CSRF por sessão (cookie + campo oculto)
 * - Opcionalmente exibe mensagens de sucesso/erro via query string (?status=ok|error&msg=...)
 *
 * Importante:
 * - O backend faz TODAS as validações críticas. Este arquivo apenas ajuda a
 *   alimentar os campos esperados pelo backend sem alterar o layout visual.
 */

(function () {
    function uuid4() {
        // Gera um token aleatório simples para CSRF
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
            setCookie('csrf_token', token, 1); // dura ~1 dia (sessão simples)
        }
        return token;
    }

    function setupForm(form) {
        if (!form) return;

        // Honeypot invisível (não impacta layout)
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

        // Time trap: start_time em ms
        let startInput = form.querySelector('input[name="start_time"]');
        if (!startInput) {
            startInput = document.createElement('input');
            startInput.type = 'hidden';
            startInput.name = 'start_time';
            form.appendChild(startInput);
        }
        startInput.value = String(Date.now());

        // CSRF: cookie + campo oculto
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

        let text;
        if (status === 'ok') {
            text = msg || 'Mensagem enviada com sucesso!';
            alert(text);
        } else if (status === 'error') {
            text = msg || 'Não foi possível enviar sua mensagem. Tente novamente.';
            alert(text);
        }
        // Opcional: remover parâmetros da URL após exibir
        if (window.history && window.history.replaceState) {
            const url = window.location.origin + window.location.pathname;
            window.history.replaceState({}, document.title, url);
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        // Configura todos os formulários de contato conhecidos
        const forms = [
            document.getElementById('contactForm'),
            document.getElementById('support-form')
        ].filter(Boolean);

        forms.forEach(setupForm);
        showStatusMessage();
    });
})();
