<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['google_id'])) {
    header('Location: /');
    exit;
}

$db_file = __DIR__ . '/database.sqlite';
$db = new SQLite3($db_file);

// Remove or comment out the debugging code
/*
// Check if the table exists
$result = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='characters'");
if ($result->fetchArray() === false) {
    die("The 'characters' table doesn't exist. Please run db_setup.php first.");
}

// If the table exists, show its structure
$result = $db->query("PRAGMA table_info(characters)");
echo "Table structure:<br>";
while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    echo $row['name'] . " (" . $row['type'] . ")<br>";
}

// Show the first few rows of data
$result = $db->query("SELECT * FROM characters LIMIT 5");
echo "First 5 rows:<br>";
while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    print_r($row);
    echo "<br>";
}
*/

// Fetch characters from the database
$user_id = SQLite3::escapeString($_SESSION['google_id']);
$stmt = $db->prepare('SELECT id, name FROM characters WHERE user_id = :user_id ORDER BY created_at DESC');
$stmt->bindValue(':user_id', $user_id, SQLITE3_TEXT);
$result = $stmt->execute();

$characters = [];
while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $characters[] = $row;
}

$db->close();

// Function to get the first initial of the name
function getFirstInitial($name) {
    return strtoupper(substr($name, 0, 1));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Nevermade</title>
    <link href="/assets/css/output.css" rel="stylesheet">
</head>
<body>
<div class="flex h-screen w-full overflow-hidden">
    <!-- Navbar  -->
    <nav class="flex flex-col w-1/6 h-full bg-zinc-900 text-white px-8 py-4 border-r border-zinc-500">
        <div class="px-4 py-2 flex flex-col h-full">
            <a href="/" class="text-2xl font-bold">Nevermade</a>
            <div class="flex flex-col flex-1">

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

    <div class="flex flex-col w-full text-center items-center px-8 py-4 h-full bg-zinc-900 text-white">
        <h1 class="text-4xl font-semibold mb-6">Welcome to your Dashboard</h1>
        <p class="text-gray-500 dark:text-gray-400 text-xl mb-8">
            Here you can manage your characters and conversations.
        </p>
        <!-- Add character list -->
        <div class="w-full max-w-2xl">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-2xl font-semibold">Your Characters</h2>
                <a href="/create-character.php" class="bg-blue-600 text-white rounded-md px-4 py-2 hover:bg-blue-700">Create New Character</a>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <?php foreach ($characters as $character): ?>
                    <a href="/chat.php?id=<?php echo $character['id']; ?>" class="bg-zinc-800 rounded-lg p-4 hover:bg-zinc-700 transition-colors">
                        <h3 class="text-xl font-semibold"><?php echo htmlspecialchars($character['name']); ?></h3>
                        <p class="text-gray-400">Click to chat</p>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>
</body>
</html>