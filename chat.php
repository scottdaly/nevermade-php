<?php
session_start();

// Add this near the top of your file
ini_set('display_errors', 1);
ini_set('log_errors', 1);
error_reporting(E_ALL);
ini_set('error_log', __DIR__ . '/error.log');

// Check if the user is logged in
if (!isset($_SESSION['google_id'])) {
    header('Location: /');
    exit;
}

$db_file = __DIR__ . '/database.sqlite';
$db = new SQLite3($db_file);

// Debug: Check if the messages table exists
$result = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='messages'");
if ($result->fetchArray() === false) {
    die("The 'messages' table doesn't exist. Please run db_setup.php first.");
}

// Get the character ID from the URL
$character_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch character details from the database
$stmt = $db->prepare('SELECT id, name, description FROM characters WHERE id = :id AND user_id = :user_id');
$stmt->bindValue(':id', $character_id, SQLITE3_INTEGER);
$stmt->bindValue(':user_id', $_SESSION['google_id'], SQLITE3_TEXT);
$result = $stmt->execute();

$character = $result->fetchArray(SQLITE3_ASSOC);

if (!$character) {
    // Character not found or doesn't belong to the user
    header('Location: /dashboard.php');
    exit;
}

// Load configuration
$config = require __DIR__ . '/config.php';
define('ANTHROPIC_API_KEY', $config['anthropic_api_key']);

function getChatResponse($message, $character) {
    $url = 'https://api.anthropic.com/v1/messages';
    $data = [
        'model' => 'claude-3-5-sonnet-20240620',
        'system' => "You are {$character['name']}. {$character['description']}",
        'messages' => [
            ['role' => 'user', 'content' => $message]
        ],
        'max_tokens' => 4000
    ];

    $options = [
        'http' => [
            'method' => 'POST',
            'header' => [
                'Content-Type: application/json',
                'x-api-key: ' . ANTHROPIC_API_KEY,
                'anthropic-version: 2023-06-01'
            ],
            'content' => json_encode($data),
            'ignore_errors' => true // This allows us to capture error responses
        ]
    ];

    $context = stream_context_create($options);
    $result = file_get_contents($url, false, $context);

    if ($result === FALSE) {
        $error = error_get_last();
        return "Error: " . $error['message'];
    }

    $response_data = json_decode($result, true);
    
    if (isset($response_data['error'])) {
        return "API Error: " . $response_data['error']['message'];
    }

    return $response_data['content'][0]['text'];
}

// Handle message submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    $message = SQLite3::escapeString($_POST['message']);
    $stmt = $db->prepare('INSERT INTO messages (character_id, sender, content) VALUES (:character_id, :sender, :content)');
    $stmt->bindValue(':character_id', $character_id, SQLITE3_INTEGER);
    $stmt->bindValue(':sender', 'user', SQLITE3_TEXT);
    $stmt->bindValue(':content', $message, SQLITE3_TEXT);
    $stmt->execute();

    // Get response from Claude
    $aiResponse = getChatResponse($message, $character);
    error_log("API Response: " . print_r($aiResponse, true));

    // Save AI response
    $stmt = $db->prepare('INSERT INTO messages (character_id, sender, content) VALUES (:character_id, :sender, :content)');
    $stmt->bindValue(':character_id', $character_id, SQLITE3_INTEGER);
    $stmt->bindValue(':sender', 'character', SQLITE3_TEXT);
    $stmt->bindValue(':content', $aiResponse, SQLITE3_TEXT);
    $stmt->execute();
}

// Fetch messages for this character
$stmt = $db->prepare('SELECT * FROM messages WHERE character_id = :character_id ORDER BY created_at ASC');
$stmt->bindValue(':character_id', $character_id, SQLITE3_INTEGER);
$result = $stmt->execute();

$messages = [];
while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $messages[] = $row;
}

$db->close();

function getFirstInitial($name) {
    return strtoupper(substr($name, 0, 1));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat with <?php echo htmlspecialchars($character['name']); ?> - Nevermade</title>
    <link href="/assets/css/output.css" rel="stylesheet">
</head>
<body>
<div class="flex h-screen w-full overflow-hidden">
    <!-- Sidebar -->
    <nav class="flex flex-col w-1/6 h-full bg-zinc-900 text-white px-4 py-4 border-r border-zinc-500">
        <div class="flex flex-col h-full">
            <a href="/" class="text-2xl font-bold mb-4">Nevermade</a>
            <a href="/dashboard.php" class="mb-4 text-zinc-300 hover:text-white">Dashboard</a>
            <h2 class="text-xl mb-2">Character</h2>
            <div class="mb-4">
                <h3 class="font-semibold"><?php echo htmlspecialchars($character['name']); ?></h3>
                <p class="text-sm text-zinc-400"><?php echo htmlspecialchars($character['description']); ?></p>
            </div>
            <div class="mt-auto">
                <div class="flex mb-4 items-center">
                    <?php if (isset($_SESSION['profile_picture']) && $_SESSION['profile_picture']): ?>
                        <img src="<?php echo htmlspecialchars($_SESSION['profile_picture']); ?>" alt="Profile" class="w-10 h-10 rounded-full">
                    <?php else: ?>
                        <div class="w-10 h-10 rounded-full bg-gray-700 flex items-center justify-center text-white font-bold">
                            <?php echo getFirstInitial($_SESSION['name']); ?>
                        </div>
                    <?php endif; ?>
                    <span class="ml-4"><?php echo htmlspecialchars($_SESSION['name']); ?></span>
                </div>
                <a href="/logout.php" class="block w-full border border-zinc-500 text-zinc-200 rounded-md px-4 py-2 text-center">Logout</a>
            </div>
        </div>
    </nav>

    <!-- Main content -->
    <div class="flex flex-col w-5/6 h-full bg-zinc-800 text-white">
        <!-- Character name header -->
        <div class="bg-zinc-700 px-6 py-4">
            <h1 class="text-2xl font-semibold"><?php echo htmlspecialchars($character['name']); ?></h1>
        </div>

        <!-- Chat messages -->
        <div class="flex-1 overflow-y-auto px-6 py-4" id="chat-messages">
            <?php foreach ($messages as $message): ?>
                <div class="mb-4 <?php echo $message['sender'] === 'user' ? 'text-right' : ''; ?>">
                    <div class="inline-block bg-zinc-700 rounded-lg px-4 py-2 max-w-3/4">
                        <?php echo htmlspecialchars($message['content']); ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Message input -->
        <div class="bg-zinc-700 px-6 py-4">
            <form action="" method="post" class="flex">
                <input type="text" name="message" placeholder="Type your message..." 
                       class="flex-1 bg-zinc-600 text-white rounded-l-md px-4 py-2 focus:outline-none">
                <button type="submit" class="bg-blue-600 text-white rounded-r-md px-6 py-2 hover:bg-blue-700">Send</button>
            </form>
        </div>
    </div>
</div>
<script>
    // Scroll to the bottom of the chat messages
    function scrollToBottom() {
        const chatMessages = document.getElementById('chat-messages');
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    // Call scrollToBottom when the page loads and after sending a message
    window.onload = scrollToBottom;
    document.querySelector('form').addEventListener('submit', function() {
        setTimeout(scrollToBottom, 100);
    });
</script>
</body>
</html>