<?php
// Start the session to manage login state
session_start();

// Redirect user to dashboard if they are already logged in
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    header("Location: dashboard.php");
    exit();
}

$error_message = '';
$db_connected = false;

// --- Database Configuration (XAMPP Default Settings) ---
$host = 'localhost';
$db   = 'pch_portal_db'; // Ensure your database is named this, or update here
$user = 'root';          // XAMPP default MySQL username
$pass = '';              // XAMPP default MySQL password (blank)
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    $db_connected = true;
} catch (\PDOException $e) {
    // Show a general error if connection fails
    $error_message = 'ERROR: Could not connect to the database. Ensure XAMPP is running and the database exists.';
}

// --- Login Processing Logic ---
if ($_SERVER['REQUEST_METHOD'] === 'POST'&& $db_connected) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // Check for empty fields
    if (empty($username) || empty($password)) {
        $error_message = 'Please enter both username and password.';
    } else {
        $stmt = $pdo->prepare('SELECT full_name, prize_balance, password_text FROM users WHERE username = ?');
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user) {
            // Check the plaintext password against the value stored in the database
            if ($password === $user['password_text']) { 

                // Authentication successful!
                $_SESSION['logged_in'] = true;
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['prize_balance'] = $user['prize_balance'];

                header("Location: dashboard.php");
                exit();
            }
        }

        // Authentication failed
        $error_message = 'Invalid Winner ID or Password. Please check your credentials.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Winner Portal Login</title>
<script src="https://cdn.tailwindcss.com"></script>
<script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
'pch-blue': '#1e3a8a',
'pch-gold': '#facc15',
'danger-red': '#ef4444',
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                }
            }
        }
</script>
</head>
<body class="bg-pch-blue min-h-screen flex items-center justify-center font-sans p-4">

<div class="w-full max-w-md bg-white p-8 md:p-10 rounded-xl shadow-2xl border-t-8 border-pch-gold">

<div class="text-center mb-8">
<div class="inline-block p-3 rounded-full bg-pch-blue shadow-lg mb-4">
<svg class="w-8 h-8 text-pch-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3v-1m17-3v-1a3 3 0 00-3-3h-2"></path>
</svg>
</div>
<h1 class="text-3xl font-extrabold text-pch-blue">Secure Winner Login</h1>
<p class="text-gray-500 mt-2">Access your PCH Prize Financial Portal</p>
</div>

<!-- Dynamic Error Message Display based on PHP variable -->
<?php if (!empty($error_message)): ?>
<div class="p-3 mb-4 text-sm font-medium text-danger-red bg-red-100 rounded-lg border border-danger-red text-center" role="alert">
<?php echo htmlspecialchars($error_message); ?>
</div>
<?php endif; ?>

<form action="login.php" method="POST" class="space-y-6">

<div>
<label for="username" class="block text-sm font-medium text-gray-700 mb-1">Winner ID (Username)</label>
<input type="text" id="username" name="username" required class="w-full p-3 border border-gray-300 rounded-lg focus:ring-pch-gold focus:border-pch-gold shadow-sm transition" placeholder="Ray2024PCH">
</div>

<div>
<label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
<input type="password" id="password" name="password" required class="w-full p-3 border border-gray-300 rounded-lg focus:ring-pch-gold focus:border-pch-gold shadow-sm transition" placeholder="••••••••">
</div>

<button type="submit" class="w-full bg-pch-blue hover:bg-opacity-90 text-white font-semibold py-3 rounded-lg transition duration-300 shadow-lg text-lg transform hover:scale-[1.005]">
                Log In Securely
</button>
</form>

</div>
</body>
</html>
