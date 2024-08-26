<?php
session_start();

// Redirect logged-in users to the dashboard
if (isset($_SESSION['google_id'])) {
    header('Location: /dashboard.php');
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nevermade</title>
    <link href="/assets/css/output.css" rel="stylesheet">
</head>
<body>
<div class="flex flex-col h-screen w-full overflow-hidden items-center">
  <!-- Add navbar here -->
  <nav class="w-full bg-white">
    <div class="max-w-[1200px] mx-auto px-4 py-2 flex justify-between items-center">
      <a href="/" class="text-2xl font-bold">Nevermade</a>
      <div>
        <?php
        function isLoggedIn() {
          return isset($_SESSION['google_id']);
        }

        if (isLoggedIn()) {
          echo '<div class="flex items-center">';
          echo '<a href="/dashboard.php" class="mr-4 text-blue-600 hover:text-blue-800">Dashboard</a>';
          echo '<img src="' . htmlspecialchars($_SESSION['profile_picture']) . '" alt="Profile" class="w-10 h-10 rounded-full">';
          echo '</div>';
        } else {
          echo '<a href="/google_login.php" class="bg-white text-gray-700 px-4 py-2 rounded-lg border border-gray-300 hover:bg-gray-100 transition-colors duration-300 flex items-center">
                  <img src="/assets/images/google_logo.svg" alt="Google" class="w-5 h-5 mr-2">
                  Sign in with Google
                </a>';
        }
        ?>
      </div>
    </div>
  </nav>

  <div class="flex flex-col w-full max-w-[1200px] text-center items-center pt-8 h-full">
  <div class="flex flex-col max-w-xl my-12">
      <h1 class="text-7xl font-semibold mb-6">
        Explore a world of endless possibilities
      </h1>
      <p class="text-gray-500 dark:text-gray-400 text-2xl mb-8 transition-colors duration-700">
        Create your own characters and have endless conversations
      </p>
      <a href="<?php echo isLoggedIn() ? '/create-character.php' : '/google_login.php'; ?>">
        <button class="flex items-center justify-center mx-auto bg-black text-white hover:bg-slate-700 hover:drop-shadow-xl rounded-2xl px-6 py-4 gap-4 transition-all duration-500">
          <svg
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 24 24"
            fill="rgb(250 204 21)"
            class="size-6"
          >
            <path
              fillRule="evenodd"
              d="M9 4.5a.75.75 0 0 1 .721.544l.813 2.846a3.75 3.75 0 0 0 2.576 2.576l2.846.813a.75.75 0 0 1 0 1.442l-2.846.813a3.75 3.75 0 0 0-2.576 2.576l-.813 2.846a.75.75 0 0 1-1.442 0l-.813-2.846a3.75 3.75 0 0 0-2.576-2.576l-2.846-.813a.75.75 0 0 1 0-1.442l2.846-.813A3.75 3.75 0 0 0 7.466 7.89l.813-2.846A.75.75 0 0 1 9 4.5ZM18 1.5a.75.75 0 0 1 .728.568l.258 1.036c.236.94.97 1.674 1.91 1.91l1.036.258a.75.75 0 0 1 0 1.456l-1.036.258c-.94.236-1.674.97-1.91 1.91l-.258 1.036a.75.75 0 0 1-1.456 0l-.258-1.036a2.625 2.625 0 0 0-1.91-1.91l-1.036-.258a.75.75 0 0 1 0-1.456l1.036-.258a2.625 2.625 0 0 0 1.91-1.91l.258-1.036A.75.75 0 0 1 18 1.5ZM16.5 15a.75.75 0 0 1 .712.513l.394 1.183c.15.447.5.799.948.948l1.183.395a.75.75 0 0 1 0 1.422l-1.183.395c-.447.15-.799.5-.948.948l-.395 1.183a.75.75 0 0 1-1.422 0l-.395-1.183a1.5 1.5 0 0 0-.948-.948l-1.183-.395a.75.75 0 0 1 0-1.422l1.183-.395c.447-.15.799-.5.948-.948l.395-1.183A.75.75 0 0 1 16.5 15Z"
              clipRule="evenodd"
            />
          </svg>
          Create Character
          <svg
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
            strokeWidth="1.5"
            stroke="currentColor"
            class="size-6"
          >
            <path
              strokeLinecap="round"
              strokeLinejoin="round"
              d="m8.25 4.5 7.5 7.5-7.5 7.5"
            />
          </svg>
        </button>
</a>
    </div>
</div>
</div>
</body>
</html>