<div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 py-8">
    <div class="max-w-4xl mx-auto px-4">
        <div class="bg-white rounded-lg shadow-xl overflow-hidden">
            <!-- Header -->
            <div class="bg-gradient-to-r from-blue-600 to-indigo-600 text-white p-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <div class="w-12 h-12 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                            <span class="text-xl font-bold">VK</span>
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold">Vlastimil Kalášek</h1>
                            <p class="text-blue-100">MES System Support Specialist & Developer</p>
                            <p class="text-sm text-blue-200 italic">"Life is a bug, and you're the debugger"</p>
                        </div>
                    </div>
                    <!-- Status indicator -->
                    <div class="flex items-center space-x-2">
                        <div id="status-indicator" class="w-3 h-3 bg-red-400 rounded-full"></div>
                        <span id="status-text" class="text-sm text-blue-100">Checking...</span>
                    </div>
                </div>
            </div>

            <!-- Chat messages -->
            <div id="chat-messages" class="h-96 overflow-y-auto p-6 space-y-4 bg-gray-50">
                <div class="text-center text-gray-500">
                    <div class="bg-white p-4 rounded-lg shadow-sm inline-block">
                        <p class="font-medium">Ahoj! 👋</p>
                        <p class="text-sm mt-2">Zeptej se mě na cokoliv o mé práci, zkušenostech, projektech nebo dovednostech!</p>
                        <div class="mt-3 flex flex-wrap gap-2 justify-center">
                            <button class="suggestion-btn bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-xs hover:bg-blue-200 transition-colors">
                                Jaké máš zkušenosti?
                            </button>
                            <button class="suggestion-btn bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs hover:bg-green-200 transition-colors">
                                Ukaž mi své CV
                            </button>
                            <button class="suggestion-btn bg-purple-100 text-purple-700 px-3 py-1 rounded-full text-xs hover:bg-purple-200 transition-colors">
                                Jaké projekty děláš?
                            </button>
                            <button class="suggestion-btn bg-orange-100 text-orange-700 px-3 py-1 rounded-full text-xs hover:bg-orange-200 transition-colors">
                                Certifikáty a školení
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Typing indicator -->
            <div id="typing-indicator" class="hidden px-6 py-2">
                <div class="flex items-center space-x-2 text-gray-500">
                    <div class="flex space-x-1">
                        <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce"></div>
                        <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.1s"></div>
                        <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.2s"></div>
                    </div>
                    <span class="text-sm">Vlastimil píše...</span>
                </div>
            </div>

            <!-- Input -->
            <div class="p-6 border-t bg-white">
                <div class="flex space-x-3">
                    <input type="text"
                        id="chat-input"
                        placeholder="Zeptej se na cokoliv..."
                        class="flex-1 p-4 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <button id="send-btn"
                        class="bg-blue-600 text-white px-8 py-4 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                        <span id="send-text">Odeslat</span>
                        <span id="loading-text" class="hidden">Odesílám...</span>
                    </button>
                </div>
                <div class="mt-2 text-xs text-gray-500">
                    Tip: Zkus se zeptat na "CV", "certifikáty", "projekty" pro detailní informace
                </div>
                <!-- Error message -->
                <div id="error-message" class="hidden mt-2 p-2 bg-red-100 border border-red-300 text-red-700 rounded text-sm">
                    <span id="error-text"></span>
                    <button id="retry-btn" class="ml-2 underline hover:no-underline">Zkusit znovu</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
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

        let initialMessageShown = true;
        let lastFailedMessage = '';

        // Check API status on load
        setTimeout(checkApiStatus, 500); // Delay to ensure page is loaded

        function checkApiStatus() {
            console.log('Checking API status...');
            fetch('/__Framework/1_v0/public/api/v1/chat/status')
                .then(response => {
                    console.log('Status response:', response.status);
                    return response.json();
                })
                .then(data => {
                    console.log('Status data:', data);
                    // BaseApiController wraps response in success/data structure
                    const apiData = data.success ? data.data : data;
                    console.log('Parsed status:', apiData);

                    if (apiData.status === 'ok') {
                        setStatus('online');
                    } else {
                        setStatus('offline');
                    }
                })
                .catch((error) => {
                    console.error('Status check failed:', error);
                    setStatus('offline');
                });
        }

        function setStatus(status) {
            console.log('Setting status to:', status);
            if (status === 'online') {
                statusIndicator.className = 'w-3 h-3 bg-green-400 rounded-full animate-pulse';
                statusText.textContent = 'Online';
            } else {
                statusIndicator.className = 'w-3 h-3 bg-red-400 rounded-full';
                statusText.textContent = 'Offline';
            }
        }

        function showError(message, retryMessage = '') {
            errorText.textContent = message;
            lastFailedMessage = retryMessage;
            errorMessage.classList.remove('hidden');

            if (retryMessage) {
                retryBtn.classList.remove('hidden');
            } else {
                retryBtn.classList.add('hidden');
            }
        }

        function hideError() {
            errorMessage.classList.add('hidden');
        }

        function addMessage(content, isUser = false, isError = false) {
            console.log('Adding message:', content, 'isUser:', isUser, 'isError:', isError);

            // Remove initial message on first user message
            if (isUser && initialMessageShown) {
                chatMessages.innerHTML = '';
                initialMessageShown = false;
            }

            const messageDiv = document.createElement('div');
            messageDiv.className = `flex ${isUser ? 'justify-end' : 'justify-start'} mb-4`;

            const bubble = document.createElement('div');
            let bubbleClass = `max-w-xs lg:max-2xl px-4 py-3 rounded-2xl ${
        isUser 
            ? 'bg-blue-600 text-white rounded-br-md' 
            : isError
                ? 'bg-red-100 text-red-800 border border-red-300 rounded-bl-md'
                : 'bg-white text-gray-800 shadow-md rounded-bl-md border'
        }`;
            bubble.className = bubbleClass;

            if (isUser) {
                bubble.textContent = content;
            } else {
                // Format bot response with line breaks and basic markdown
                let formattedContent = content.replace(/\n/g, '<br>');

                // Simple markdown formatting
                formattedContent = formattedContent
                    .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
                    .replace(/\*(.*?)\*/g, '<em>$1</em>')
                    .replace(/`(.*?)`/g, '<code class="bg-gray-100 px-1 rounded">$1</code>');

                bubble.innerHTML = formattedContent;
            }

            messageDiv.appendChild(bubble);
            chatMessages.appendChild(messageDiv);

            // Scroll to bottom
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }

        function showTyping() {
            typingIndicator.classList.remove('hidden');
            chatMessages.scrollTop = chatMessages.scrollHeight;
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

            console.log('Sending message:', messageText);
            hideError();

            // Add user message
            addMessage(messageText, true);
            if (!question) chatInput.value = '';
            setLoading(true);

            try {
                const response = await fetch('/__Framework/1_v0/public/api/v1/chat', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        question: messageText
                    })
                });

                console.log('Response status:', response.status);

                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }

                const data = await response.json();
                console.log('Full response:', data);

                // BaseApiController wraps response in success/data structure
                const apiData = data.success ? data.data : data;
                console.log('Parsed chat data:', apiData);

                if (apiData.response) {
                    addMessage(apiData.response);
                    setStatus('online');
                } else if (apiData.error || data.error) {
                    const errorMsg = apiData.error || data.error;
                    console.error('API Error:', errorMsg);
                    throw new Error(errorMsg);
                } else {
                    console.error('Unexpected response structure:', data);
                    throw new Error('Neočekávaná odpověď ze serveru');
                }

            } catch (error) {
                console.error('Chat error:', error);
                setStatus('offline');

                let errorMsg = 'Omlouvám se, došlo k chybě při zpracování.';

                if (error.name === 'TypeError' && error.message.includes('fetch')) {
                    errorMsg = 'Problém s připojením k serveru.';
                } else if (error.message.includes('timeout')) {
                    errorMsg = 'Vypršel časový limit požadavku.';
                } else if (error.message.includes('HTTP 429')) {
                    errorMsg = 'Příliš mnoho požadavků. Zkuste to za chvíli.';
                } else if (error.message.includes('HTTP 401')) {
                    errorMsg = 'Problém s autentizací API.';
                } else if (error.message) {
                    errorMsg = error.message;
                }

                addMessage(errorMsg, false, true);
                showError(errorMsg, messageText);

            } finally {
                setLoading(false);
            }
        }

        // Event listeners
        sendBtn.addEventListener('click', () => sendMessage());

        chatInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendMessage();
            }
        });

        // Retry button
        if (retryBtn) {
            retryBtn.addEventListener('click', function() {
                if (lastFailedMessage) {
                    sendMessage(lastFailedMessage);
                }
            });
        }

        // Suggestion buttons
        suggestionBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                sendMessage(this.textContent);
            });
        });

        // Focus input on load
        chatInput.focus();

        // Periodic status check
        setInterval(checkApiStatus, 60000); // Check every minute
    });
</script>