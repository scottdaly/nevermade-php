<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['google_id'])) {
    header('Location: /');
    exit;
}

$db_file = __DIR__ . '/database.sqlite';
$db = new SQLite3($db_file);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $character_name = SQLite3::escapeString($_POST['character_name'] ?? '');
    $character_description = SQLite3::escapeString($_POST['character_description'] ?? '');
    $user_id = SQLite3::escapeString($_SESSION['google_id']);

    $stmt = $db->prepare('INSERT INTO characters (user_id, name, description) VALUES (:user_id, :name, :description)');
    $stmt->bindValue(':user_id', $user_id, SQLITE3_TEXT);
    $stmt->bindValue(':name', $character_name, SQLITE3_TEXT);
    $stmt->bindValue(':description', $character_description, SQLITE3_TEXT);
    $result = $stmt->execute();

    if ($result) {
        header('Location: /dashboard.php');
        exit;
    } else {
        $error_message = "Failed to create character. Please try again.";
    }
}

function getFirstInitial($name) {
    return strtoupper(substr($name, 0, 1));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Character - Nevermade</title>
    <link href="/assets/css/output.css" rel="stylesheet">
</head>
<body>
<div class="flex h-screen w-full overflow-hidden">
    <!-- Sidebar -->
    <nav class="flex flex-col w-1/6 h-full bg-zinc-900 text-white px-8 py-4 border-r border-zinc-500">
        <div class="px-4 py-2 flex flex-col h-full">
            <a href="/" class="text-2xl font-bold">Nevermade</a>
            <div class="flex flex-col flex-1">
                <a href="/dashboard.php" class="mt-8 text-zinc-300 hover:text-white">Dashboard</a>
            </div>
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
            <a href="/logout.php" class="border border-zinc-500 text-zinc-200 rounded-md px-4 py-2 items-center justify-center text-center">Logout</a>
        </div>
    </nav>

    <!-- Main content -->
    <div class="flex flex-col w-5/6 h-full bg-zinc-800 text-white p-8">
        <h1 class="text-4xl font-semibold mb-6">Create a New Character</h1>
        <form action="" method="post" class="max-w-2xl">
            <div class="mb-4">
                <label for="character_name" class="block text-sm font-medium text-gray-300 mb-2">Character Name</label>
                <input type="text" id="character_name" name="character_name" required
                       class="w-full px-3 py-2 bg-zinc-700 border border-zinc-600 rounded-md text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="mb-4">
                <label for="character_description" class="block text-sm font-medium text-gray-300 mb-2">Character Description</label>
                <textarea id="character_description" name="character_description" rows="4" required
                          class="w-full px-3 py-2 bg-zinc-700 border border-zinc-600 rounded-md text-white focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
            </div>
            <button type="submit" class="bg-blue-600 text-white rounded-md px-6 py-2 hover:bg-blue-700">Create Character</button>
        </form>
    </div>
</div>
</body>
</html>