<!-- Chat area pod navem: 100vh - 95px -->
<div class="bg-black">
    <div class="max-w-4xl mx-auto px-4">
        <div class="relative h-[calc(100vh-95px)] overflow-hidden">

            <div id="chat-scroll" class="h-full overflow-y-auto overscroll-contain hide-scrollbar">

                <!-- persistent disclaimer -->
                <div class="py-3">
                    <div class="rounded-2xl border border-white/10 bg-white/5 p-3 text-xs text-white/60">
                        <?= __('chat_disclaimer_text_before', [], 'chat') ?>
                        <a href="<?= locale_url('contact') ?>" class="underline hover:no-underline text-white">
                            <?= __('chat_disclaimer_contact_link', [], 'chat') ?>
                        </a>.
                    </div>
                </div>

                <div id="chat-messages" class="py-6 space-y-6 pb-40">

                    <!-- Intro -->
                    <div id="initial-message" class="rounded-2xl border border-white/10 bg-white/5 p-5">
                        <div class="text-white font-semibold text-lg"><?= __('chat_intro_title', [], 'chat') ?></div>

                        <div class="mt-3 rounded-xl border border-white/10 bg-white/5 p-3">
                            <div class="text-xs text-white/70 leading-relaxed">
                                <?= __('chat_intro_body', [], 'chat') ?>
                                <a href="<?= locale_url('contact') ?>" class="underline hover:no-underline text-white">
                                    <?= __('chat_intro_contact_form_link', [], 'chat') ?>
                                </a>.
                            </div>
                        </div>

                        <div class="mt-4 flex flex-wrap gap-2">
                            <button class="suggestion-btn px-3 py-1.5 rounded-full text-xs border border-white/10 bg-white/5 text-white/80 hover:bg-white/10 transition">
                                <?= __('chat_suggestion_experience', [], 'chat') ?>
                            </button>
                            <button class="suggestion-btn px-3 py-1.5 rounded-full text-xs border border-white/10 bg-white/5 text-white/80 hover:bg-white/10 transition">
                                <?= __('chat_suggestion_tech', [], 'chat') ?>
                            </button>
                            <button class="suggestion-btn px-3 py-1.5 rounded-full text-xs border border-white/10 bg-white/5 text-white/80 hover:bg-white/10 transition">
                                <?= __('chat_suggestion_projects', [], 'chat') ?>
                            </button>
                            <button class="suggestion-btn px-3 py-1.5 rounded-full text-xs border border-white/10 bg-white/5 text-white/80 hover:bg-white/10 transition">
                                <?= __('chat_suggestion_pricing', [], 'chat') ?>
                            </button>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Composer -->
            <div class="absolute left-0 right-0 bottom-0 border-white/10 bg-black">
                <div class="py-4">

                    <!-- Typing indicator -->
                    <div id="typing-indicator"
                        class="hidden absolute left-0 right-0 bottom-[100px] border-white/10 bg-black/80">
                        <div class="py-2">
                            <div class="flex items-center gap-2 text-white/60 text-sm">
                                <div class="flex gap-1">
                                    <div class="w-2 h-2 bg-white/40 rounded-full animate-bounce"></div>
                                    <div class="w-2 h-2 bg-white/40 rounded-full animate-bounce" style="animation-delay: 0.1s"></div>
                                    <div class="w-2 h-2 bg-white/40 rounded-full animate-bounce" style="animation-delay: 0.2s"></div>
                                </div>
                                <span><?= __('chat_typing_text', [], 'chat') ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Status -->
                    <div class="flex items-center justify-end gap-2 mb-2">
                        <div id="status-indicator" class="w-2.5 h-2.5 bg-red-400 rounded-full"></div>
                        <span id="status-text" class="text-xs text-white/60">
                            <?= __('chat_status_checking', [], 'chat') ?>
                        </span>
                    </div>

                    <!-- Error -->
                    <div id="error-message" class="hidden mb-3 rounded-xl border border-red-500/30 bg-red-500/10 p-3 text-sm text-red-200">
                        <span id="error-text"></span>
                        <button id="retry-btn" class="ml-2 underline hover:no-underline">
                            <?= __('chat_retry_button', [], 'chat') ?>
                        </button>
                    </div>

                    <div class="flex flex-col md:flex-row items-stretch md:items-end gap-3">
                        <div class="flex-1 rounded-2xl border border-white/10 bg-white/5 px-4 py-3">
                            <textarea
                                id="chat-input"
                                rows="1"
                                placeholder="<?= __('chat_input_placeholder', [], 'chat') ?>"
                                class="w-full resize-none bg-transparent outline-none text-white placeholder:text-white/40 leading-relaxed"></textarea>
                        </div>

                        <button id="send-btn"
                            class="w-full md:w-auto shrink-0 rounded-2xl px-5 py-3 bg-[var(--color-green)] text-white font-semibold hover:brightness-110 transition disabled:opacity-50 disabled:cursor-not-allowed">
                            <span id="send-text"><?= __('chat_send_button', [], 'chat') ?></span>
                            <span id="loading-text" class="hidden"><?= __('chat_sending_text', [], 'chat') ?></span>
                        </button>

                        <button id="clear-chat-btn"
                            class="w-full md:w-auto shrink-0 rounded-2xl px-5 py-3 bg-red-600 text-white font-semibold hover:brightness-110 transition disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer">
                            <?= __('chat_clear_button', [], 'chat') ?>
                        </button>
                    </div>

                    <div class="mt-1 text-[11px] text-white/35 leading-relaxed">
                        <?= __('chat_input_hint', [], 'chat') ?>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>


<script>
    document.addEventListener('DOMContentLoaded', function() {
        const chatScroll = document.getElementById('chat-scroll');
        const chatMessages = document.getElementById('chat-messages');
        const chatInput = document.getElementById('chat-input');
        const sendBtn = document.getElementById('send-btn');
        const sendText = document.getElementById('send-text');
        const loadingText = document.getElementById('loading-text');
        const suggestionBtns = document.querySelectorAll('.suggestion-btn');
        const typingIndicator = document.getElementById('typing-indicator');
        const statusIndicator = document.getElementById('status-indicator');
        const statusText = document.getElementById('status-text');
        const errorMessage = document.getElementById('error-message');
        const errorText = document.getElementById('error-text');
        const retryBtn = document.getElementById('retry-btn');
        const clearBtn = document.getElementById('clear-chat-btn');

        const STORAGE_KEY = 'vk_chat_history_v1';

        let initialMessageShown = true;
        let lastFailedMessage = '';
        let history = [];

        if (typeof STATUS_URL === 'undefined' || typeof CHAT_URL === 'undefined') {
            console.error('Missing API constants (STATUS_URL / CHAT_URL).');
            setStatus('offline');
            return;
        }

        if (clearBtn) {
            clearBtn.addEventListener('click', clearHistory);
        }

        function isNearBottom(el, threshold = 160) {
            return el.scrollHeight - el.scrollTop - el.clientHeight < threshold;
        }

        function scrollToBottom(el) {
            el.scrollTo({
                top: el.scrollHeight,
                behavior: 'smooth'
            });
        }

        function loadHistory() {
            try {
                return JSON.parse(localStorage.getItem(STORAGE_KEY) || '[]');
            } catch (e) {
                return [];
            }
        }

        function saveHistory(items) {
            try {
                localStorage.setItem(STORAGE_KEY, JSON.stringify(items));
            } catch (e) {
                // storage plný / zakázaný -> ignoruj
            }
        }

        function clearHistory() {
            if (!confirm('Opravdu chcete smazat celou historii chatu?')) return;
            history = [];
            try {
                localStorage.removeItem(STORAGE_KEY);
                window.location.reload();
            } catch (e) {}
            // vyčisti UI
            chatMessages.innerHTML = '';
            initialMessageShown = false;
        }

        // textarea autosize
        function autoResizeTextarea(el) {
            el.style.height = 'auto';
            const max = 220;
            const h = Math.min(el.scrollHeight, max);
            el.style.height = h + 'px';
            el.style.overflowY = (el.scrollHeight > max) ? 'auto' : 'hidden';
        }

        autoResizeTextarea(chatInput);
        chatInput.addEventListener('input', () => autoResizeTextarea(chatInput));

        setTimeout(checkApiStatus, 500);

        function checkApiStatus() {
            fetch(STATUS_URL)
                .then(r => r.json())
                .then(data => {
                    const apiData = data.success ? data.data : data;
                    if (apiData.status === 'ok') setStatus('online', apiData.provider ?? null);
                    else setStatus('offline');
                })
                .catch(() => setStatus('offline'));
        }

        function setStatus(status, provider = null) {
            if (status === 'online') {
                statusIndicator.className = 'w-2.5 h-2.5 bg-green-400 rounded-full animate-pulse';
                statusText.textContent = provider ? `Online (${provider})` : 'Online';
            } else {
                statusIndicator.className = 'w-2.5 h-2.5 bg-red-400 rounded-full';
                statusIndicator.className = 'w-2.5 h-2.5 bg-red-400 rounded-full';
                statusText.textContent = 'Offline';
            }
        }

        function showError(message, retryMessage = '') {
            errorText.textContent = message;
            lastFailedMessage = retryMessage;
            errorMessage.classList.remove('hidden');
            if (retryMessage) retryBtn.classList.remove('hidden');
            else retryBtn.classList.add('hidden');
        }

        function hideError() {
            errorMessage.classList.add('hidden');
        }

        function formatBot(content) {
            let html = (content ?? '').toString().replace(/\n/g, '<br>');
            html = html
                .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
                .replace(/\*(.*?)\*/g, '<em>$1</em>')
                .replace(/`(.*?)`/g, '<code class="px-1.5 py-0.5 rounded bg-white/10 border border-white/10">$1</code>');
            return html;
        }

        function renderMessage(content, role = 'assistant', isError = false) {
            const row = document.createElement('div');
            row.className = role === 'user' ?
                'rounded-2xl border border-white/10 bg-white/5 p-4' :
                isError ?
                'rounded-2xl border border-red-500/30 bg-red-500/10 p-4' :
                'rounded-2xl border border-white/10 bg-black/40 p-4';

            const header = document.createElement('div');
            header.className = 'flex items-center gap-3 mb-2';

            const avatar = document.createElement('div');
            avatar.className = 'w-8 h-8 rounded-full flex items-center justify-center ' + (role === 'user' ?
                'bg-white/10 text-white/80' :
                'bg-[var(--color-green)] text-black font-bold');
            avatar.textContent = role === 'user' ? 'Ty' : 'VK';

            const name = document.createElement('div');
            name.className = 'text-sm font-semibold text-white';
            name.textContent = role === 'user' ? 'Ty' : 'Vlastimil';

            header.appendChild(avatar);
            header.appendChild(name);

            const body = document.createElement('div');
            body.className = role === 'user' ?
                'text-white whitespace-pre-wrap' :
                isError ? 'text-red-100 whitespace-pre-wrap' : 'text-white/90 leading-relaxed';

            if (role === 'user') body.textContent = content;
            else body.innerHTML = formatBot(content);

            row.appendChild(header);
            row.appendChild(body);

            chatMessages.appendChild(row);
        }

        function addMessage(content, role = 'assistant', isError = false) {
            const stick = role === 'user' || isNearBottom(chatScroll);

            // při první user zprávě smaž intro box
            if (role === 'user' && initialMessageShown) {
                const initial = document.getElementById('initial-message');
                if (initial) initial.remove();
                initialMessageShown = false;
            }

            renderMessage(content, role, isError);

            // ulož do historie
            history.push({
                role,
                content,
                isError: !!isError,
                ts: Date.now()
            });
            saveHistory(history);

            if (stick) scrollToBottom(chatScroll);
        }

        function showTyping() {
            typingIndicator.classList.remove('hidden');
            if (isNearBottom(chatScroll)) scrollToBottom(chatScroll);
        }

        function hideTyping() {
            typingIndicator.classList.add('hidden');
        }

        function setLoading(loading) {
            sendBtn.disabled = loading;
            chatInput.disabled = loading;

            if (loading) {
                sendText.classList.add('hidden');
                loadingText.classList.remove('hidden');
                showTyping();
            } else {
                sendText.classList.remove('hidden');
                loadingText.classList.add('hidden');
                hideTyping();
            }
        }

        async function sendMessage(question = null) {
            const messageText = question || chatInput.value.trim();
            if (!messageText) return;

            hideError();
            addMessage(messageText, 'user');

            if (!question) {
                chatInput.value = '';
                autoResizeTextarea(chatInput);
            }

            setLoading(true);

            try {
                const resp = await fetch(CHAT_URL, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        question: messageText
                    })
                });

                if (!resp.ok) throw new Error(`HTTP ${resp.status}: ${resp.statusText}`);

                const data = await resp.json();
                const apiData = data.success ? data.data : data;

                if (apiData.response) {
                    addMessage(apiData.response, 'assistant', false);
                    setStatus('online', apiData.provider ?? null);
                } else if (apiData.error || data.error) {
                    throw new Error(apiData.error || data.error);
                } else {
                    throw new Error('Neočekávaná odpověď ze serveru');
                }

            } catch (error) {
                setStatus('offline');

                let msg = 'Omlouvám se, došlo k chybě při zpracování.';
                const m = (error.message || '');

                if (error.name === 'TypeError' && m.includes('fetch')) msg = 'Problém s připojením k serveru.';
                else if (m.includes('timeout')) msg = 'Vypršel časový limit požadavku.';
                else if (m.includes('HTTP 429')) msg = 'Příliš mnoho požadavků. Zkuste to za chvíli.';
                else if (m.includes('HTTP 401')) msg = 'Problém s autentizací API.';
                else if (m) msg = m;

                addMessage(msg, 'assistant', true);
                showError(msg, messageText);

            } finally {
                setLoading(false);
                setTimeout(() => {
                    chatInput.focus();
                }, 50);
            }
        }

        // --- INIT: načti historii a vykresli ---
        history = loadHistory();

        if (history.length > 0) {
            const initial = document.getElementById('initial-message');
            if (initial) initial.remove();
            initialMessageShown = false;

            history.forEach(m => renderMessage(m.content, m.role, !!m.isError));

            // jump (bez smooth) po načtení, ať to nebliká
            setTimeout(() => {
                chatScroll.scrollTop = chatScroll.scrollHeight;
            }, 0);
        }

        // Event listeners
        sendBtn.addEventListener('click', () => sendMessage());

        chatInput.addEventListener('keydown', (e) => {
            // Ctrl/Cmd + L = clear chat (volitelné)
            if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'l') {
                e.preventDefault();
                clearHistory();
                return;
            }

            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendMessage();
            }
        });

        if (retryBtn) {
            retryBtn.addEventListener('click', () => {
                if (lastFailedMessage) sendMessage(lastFailedMessage);
            });
        }

        suggestionBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                sendMessage(this.textContent.trim());
            });
        });

        chatInput.focus();
        setInterval(checkApiStatus, 60000);
    });
</script>
