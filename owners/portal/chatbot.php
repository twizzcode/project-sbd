<?php
session_start();
require_once __DIR__ . '/../includes/owner_auth.php';
require_once __DIR__ . '/../../config/database.php';

$page_title = 'PetCare Assistant';

// --- KONFIGURASI API ---
// TODO: Ganti dengan API Key yang sebenarnya atau simpan di .env
$apiKey = ''; 
$apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent';

$response = '';
$error = '';

// --- LOGIKA PHP ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['message'])) {
    $userMessage = $_POST['message'];

    // Tambahkan context untuk membuat chatbot lebih spesifik ke veterinary
    $systemPrompt = 'nda adalah "VetBot", asisten virtual profesional untuk Klinik Hewan "Sahabat Satwa". Anda memiliki pengetahuan luas tentang kesehatan anjing, kucing, dan hewan peliharaan umum lainnya.

TONE & STYLE:
1. Ramah, empatik, dan menenangkan (pemilik hewan sering panik saat hewannya sakit).
2. Gunakan bahasa Indonesia yang baik, mudah dipahami, tapi tetap akurat secara medis.
3. Hindari istilah medis yang terlalu rumit tanpa penjelasan.
4. Gunakan emoji sesekali agar terkesan hangat (🐾, 🐱, 🐶).

CRITICAL RULES (PENTING):
1. SAFETY FIRST: Anda adalah AI, bukan pengganti dokter hewan fisik. Jika pengguna mendeskripsikan gejala darurat (muntah darah, kejang, sesak napas, keracunan, kecelakaan), ANDA WAJIB menyuruh mereka segera membawa hewan ke klinik terdekat. Jangan berikan "resep obat keras".
2. SCOPE: Hanya jawab pertanyaan seputar kesehatan hewan, nutrisi, perilaku hewan, dan info klinik.
3. OFF-TOPIC: Jika pengguna bertanya hal lain (misal: coding, politik, resep masakan manusia), tolak dengan sopan: "Maaf, saya hanya ahli dalam kesehatan hewan peliharaan. Ada yang bisa saya bantu soal anabul Anda? 🐾"

STRUCTURE OF ANSWER:
1. Validasi perasaan pemilik (contoh: "Wah, kasihan sekali si Meong...").
2. Analisis kemungkinan penyebab ringan.
3. Saran pertolongan pertama di rumah (jika aman).
4. Disclaimer (Saran ke dokter hewan).';
    
    $fullMessage = $systemPrompt . "\n\nPertanyaan: " . $userMessage;

    // Siapkan data JSON sesuai format Gemini API
    $data = [
        "contents" => [
            [
                "parts" => [
                    ["text" => $fullMessage]
                ]
            ]
        ],
        "generationConfig" => [
            "temperature" => 0.7,
            "maxOutputTokens" => 2048,
        ]
    ];

    // Inisialisasi cURL
    $ch = curl_init($apiUrl);
    
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'x-goog-api-key: ' . $apiKey
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);

    // Eksekusi dan ambil respon
    $result = curl_exec($ch);
    
    if (curl_errno($ch)) {
        $error = 'Gagal terhubung ke AI Assistant: ' . curl_error($ch);
    } else {
        $decoded = json_decode($result, true);
        
        if (isset($decoded['candidates'][0]['content']['parts'][0]['text'])) {
            $response = $decoded['candidates'][0]['content']['parts'][0]['text'];
        } elseif (isset($decoded['error'])) {
            $error = "API Error: " . ($decoded['error']['message'] ?? 'Unknown error');
        } else {
            // Debug: tampilkan full response
            $error = "Error Response: " . htmlspecialchars(json_encode($decoded, JSON_PRETTY_PRINT));
        }
    }
    
    curl_close($ch);
}

include __DIR__ . '/../includes/owner_header.php';
?>

<style>
    .chat-container {
        max-width: 900px;
        margin: 0 auto;
        height: calc(100vh - 200px);
        display: flex;
        flex-direction: column;
    }

    .chat-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 20px;
        border-radius: 16px 16px 0 0;
        text-align: center;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }

    .chat-header h2 {
        margin: 0;
        font-size: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
    }

    .chat-header p {
        margin: 8px 0 0 0;
        opacity: 0.9;
        font-size: 14px;
    }

    .chat-messages {
        flex: 1;
        overflow-y: auto;
        padding: 20px;
        background: #f8f9fa;
        border-left: 1px solid #e0e0e0;
        border-right: 1px solid #e0e0e0;
    }

    .message-wrapper {
        margin-bottom: 20px;
        animation: fadeInUp 0.3s ease-out;
    }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .message-user {
        display: flex;
        justify-content: flex-end;
        gap: 12px;
    }

    .message-ai {
        display: flex;
        justify-content: flex-start;
        gap: 12px;
    }

    .message-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        flex-shrink: 0;
    }

    .avatar-user {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }

    .avatar-ai {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        color: white;
    }

    .message-content {
        max-width: 70%;
        padding: 14px 18px;
        border-radius: 16px;
        position: relative;
        word-wrap: break-word;
    }

    .message-user .message-content {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-bottom-right-radius: 4px;
    }

    .message-ai .message-content {
        background: white;
        color: #333;
        border-bottom-left-radius: 4px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .message-content p {
        margin: 0 0 8px 0;
    }

    .message-content p:last-child {
        margin-bottom: 0;
    }

    .message-content pre {
        background: rgba(0,0,0,0.05);
        padding: 10px;
        border-radius: 8px;
        overflow-x: auto;
        margin: 8px 0;
    }

    .chat-input-area {
        padding: 20px;
        background: white;
        border: 1px solid #e0e0e0;
        border-radius: 0 0 16px 16px;
        box-shadow: 0 -4px 6px rgba(0,0,0,0.05);
    }

    .input-group {
        display: flex;
        gap: 12px;
        align-items: flex-end;
    }

    .input-wrapper {
        flex: 1;
        position: relative;
    }

    .chat-input {
        width: 100%;
        min-height: 50px;
        max-height: 120px;
        padding: 12px 16px;
        border: 2px solid #e0e0e0;
        border-radius: 25px;
        font-size: 15px;
        font-family: inherit;
        resize: none;
        transition: all 0.3s;
    }

    .chat-input:focus {
        outline: none;
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    .send-button {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        border: none;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        font-size: 20px;
        cursor: pointer;
        transition: all 0.3s;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .send-button:hover:not(:disabled) {
        transform: scale(1.05);
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
    }

    .send-button:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    .error-message {
        background: #fee;
        border: 1px solid #fcc;
        color: #c33;
        padding: 12px 16px;
        border-radius: 12px;
        margin-bottom: 16px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .welcome-message {
        text-align: center;
        padding: 40px 20px;
        color: #666;
    }

    .welcome-message i {
        font-size: 64px;
        color: #667eea;
        margin-bottom: 20px;
    }

    .welcome-message h3 {
        color: #333;
        margin-bottom: 10px;
    }

    .suggestion-cards {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 12px;
        margin-top: 20px;
        max-width: 600px;
        margin-left: auto;
        margin-right: auto;
    }

    .suggestion-card {
        background: white;
        padding: 16px;
        border-radius: 12px;
        border: 2px solid #e0e0e0;
        cursor: pointer;
        transition: all 0.3s;
        text-align: left;
    }

    .suggestion-card:hover {
        border-color: #667eea;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.2);
    }

    .suggestion-card i {
        font-size: 24px;
        color: #667eea;
        margin-bottom: 8px;
    }

    .suggestion-card p {
        margin: 0;
        font-size: 14px;
        color: #333;
        font-weight: 500;
    }

    .typing-indicator {
        display: flex;
        gap: 4px;
        padding: 16px;
        background: white;
        border-radius: 16px;
        width: fit-content;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .typing-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #667eea;
        animation: typing 1.4s infinite;
    }

    .typing-dot:nth-child(2) {
        animation-delay: 0.2s;
    }

    .typing-dot:nth-child(3) {
        animation-delay: 0.4s;
    }

    @keyframes typing {
        0%, 60%, 100% {
            transform: translateY(0);
            opacity: 0.7;
        }
        30% {
            transform: translateY(-10px);
            opacity: 1;
        }
    }

    .char-count {
        position: absolute;
        right: 16px;
        bottom: 8px;
        font-size: 12px;
        color: #999;
    }

    @media (max-width: 768px) {
        .chat-container {
            height: calc(100vh - 150px);
        }

        .message-content {
            max-width: 85%;
        }

        .suggestion-cards {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="container-fluid px-4 py-4">
    <div class="chat-container">
        <div class="bg-white rounded-lg shadow-lg" style="display: flex; flex-direction: column; height: 100%;">
            <!-- Chat Header -->
            <div class="chat-header">
                <h2>
                    <i class="fas fa-robot"></i>
                    PetCare AI Assistant
                </h2>
                <p>Tanya apa saja tentang kesehatan hewan peliharaan Anda</p>
            </div>

            <!-- Chat Messages -->
            <div class="chat-messages" id="chatMessages">
                <?php if ($error): ?>
                    <div class="error-message">
                        <i class="fas fa-exclamation-circle"></i>
                        <span><?php echo htmlspecialchars($error); ?></span>
                    </div>
                <?php endif; ?>

                <?php if (!empty($_POST['message']) && !$error): ?>
                    <!-- User Message -->
                    <div class="message-wrapper message-user">
                        <div class="message-content">
                            <?php echo nl2br(htmlspecialchars($_POST['message'])); ?>
                        </div>
                        <div class="message-avatar avatar-user">
                            <i class="fas fa-user"></i>
                        </div>
                    </div>

                    <!-- AI Response -->
                    <?php if ($response): ?>
                        <div class="message-wrapper message-ai">
                            <div class="message-avatar avatar-ai">
                                <i class="fas fa-robot"></i>
                            </div>
                            <div class="message-content">
                                <?php 
                                // Simple markdown-like formatting
                                $formattedResponse = $response;
                                // Bold text
                                $formattedResponse = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $formattedResponse);
                                // Italic text
                                $formattedResponse = preg_replace('/\*(.*?)\*/', '<em>$1</em>', $formattedResponse);
                                // Line breaks
                                $formattedResponse = nl2br($formattedResponse);
                                echo $formattedResponse;
                                ?>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <!-- Welcome Message -->
                    <div class="welcome-message">
                        <i class="fas fa-paw"></i>
                        <h3>Halo, <?php echo htmlspecialchars($_SESSION['nama_lengkap']); ?>! 👋</h3>
                        <p>Saya adalah asisten AI yang siap membantu menjawab pertanyaan Anda tentang kesehatan hewan peliharaan.</p>
                        
                        <div class="suggestion-cards">
                            <div class="suggestion-card" onclick="fillInput('Bagaimana cara merawat kucing yang sedang hamil?')">
                                <i class="fas fa-cat"></i>
                                <p>Perawatan kucing hamil</p>
                            </div>
                            <div class="suggestion-card" onclick="fillInput('Apa saja vaksin yang wajib untuk anjing?')">
                                <i class="fas fa-syringe"></i>
                                <p>Vaksin anjing</p>
                            </div>
                            <div class="suggestion-card" onclick="fillInput('Makanan apa yang tidak boleh diberikan ke hewan peliharaan?')">
                                <i class="fas fa-ban"></i>
                                <p>Makanan terlarang</p>
                            </div>
                            <div class="suggestion-card" onclick="fillInput('Bagaimana mengatasi kutu pada hewan peliharaan?')">
                                <i class="fas fa-bug"></i>
                                <p>Mengatasi kutu</p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Chat Input -->
            <div class="chat-input-area">
                <form method="POST" id="chatForm" onsubmit="return handleSubmit(event)">
                    <div class="input-group">
                        <div class="input-wrapper">
                            <textarea 
                                name="message" 
                                id="messageInput"
                                class="chat-input" 
                                placeholder="Ketik pertanyaan Anda di sini..."
                                maxlength="500"
                                required
                                onkeydown="handleKeyPress(event)"
                                oninput="updateCharCount()"
                            ></textarea>
                            <span class="char-count" id="charCount">0/500</span>
                        </div>
                        <button type="submit" class="send-button" id="sendButton">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Auto-resize textarea
const textarea = document.getElementById('messageInput');
textarea.addEventListener('input', function() {
    this.style.height = 'auto';
    this.style.height = Math.min(this.scrollHeight, 120) + 'px';
});

// Handle Enter key (send) and Shift+Enter (new line)
function handleKeyPress(event) {
    if (event.key === 'Enter' && !event.shiftKey) {
        event.preventDefault();
        document.getElementById('chatForm').submit();
    }
}

// Handle form submit
function handleSubmit(event) {
    const input = document.getElementById('messageInput');
    const button = document.getElementById('sendButton');
    
    if (input.value.trim() === '') {
        event.preventDefault();
        return false;
    }

    // Disable button and show loading
    button.disabled = true;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    
    // Show typing indicator
    const chatMessages = document.getElementById('chatMessages');
    const typingIndicator = document.createElement('div');
    typingIndicator.className = 'message-wrapper message-ai';
    typingIndicator.id = 'typingIndicator';
    typingIndicator.innerHTML = `
        <div class="message-avatar avatar-ai">
            <i class="fas fa-robot"></i>
        </div>
        <div class="typing-indicator">
            <div class="typing-dot"></div>
            <div class="typing-dot"></div>
            <div class="typing-dot"></div>
        </div>
    `;
    chatMessages.appendChild(typingIndicator);
    
    return true;
}

// Fill input with suggestion
function fillInput(text) {
    const input = document.getElementById('messageInput');
    input.value = text;
    input.focus();
    updateCharCount();
}

// Update character count
function updateCharCount() {
    const input = document.getElementById('messageInput');
    const count = document.getElementById('charCount');
    count.textContent = input.value.length + '/500';
    
    if (input.value.length > 450) {
        count.style.color = '#e53e3e';
    } else {
        count.style.color = '#999';
    }
}

// Auto-scroll to bottom on load
window.addEventListener('load', function() {
    const chatMessages = document.getElementById('chatMessages');
    chatMessages.scrollTop = chatMessages.scrollHeight;
});

// Focus on input when page loads
document.getElementById('messageInput').focus();
</script>

<?php include __DIR__ . '/../includes/owner_footer.php'; ?>
