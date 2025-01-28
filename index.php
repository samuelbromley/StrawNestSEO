<?php
session_start();
$timeout = 600; // Session timeout time
$_SESSION['last_activity'] = time(); // Set 'last_activity' time to now

// Check for activity timeout to destroy session
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $timeout)) {
    session_unset();
    session_destroy();
}

// Function to match config lines
function match_config_line($config_line, $config_parameter) {
    return strpos($config_line, $config_parameter) === 0;
}

// Read config file and set variables
$config = file("config.php", FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$config_vars = ['title' => '', 'heading1' => '', 'heading2' => '', 'footer' => '', 'preview_len' => 300, 'items' => 5];

foreach ($config as $line) {
    foreach ($config_vars as $key => $value) {
        if (match_config_line($line, "$key:")) {
            $config_vars[$key] = trim(substr($line, strlen($key) + 1));
        }
    }
}

extract($config_vars); // Extract variables from the array

// Set session variables
$_SESSION['page'] = $_GET['page'] ?? 1;
if (empty($_SESSION['num_of_pages'])) {
    $_SESSION['num_of_pages'] = ceil(count(glob("pages/*.txt")) / $items);
}

// Function to create pages array
function create_pages_array() {
    $_SESSION['pages_array'] = glob("pages/*.txt");
    usort($_SESSION['pages_array'], function($a, $b) {
        return filemtime($b) - filemtime($a);
    });
}

// Function to generate main content preview
function main_contents_preview($preview_len, $items) {
    create_pages_array();
    $first_index = ($_SESSION['page'] - 1) * $items;
    for ($i = $first_index; $i < $first_index + $items; $i++) {
        if (!empty($_SESSION['pages_array'][$i])) {
            entry_preview($preview_len, $i);
        }
    }
}

// Function to generate single entry preview
function entry_preview($preview_len, $entry_index) {
    $page_file = file($_SESSION['pages_array'][$entry_index], FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $preview_string = "";
    $letters_count = 0;

    foreach ($page_file as $page_line) {
        if (match_config_line($page_line, "=title:")) {
            $preview_string .= "<h2><a href='" . prepare_link_to_get($entry_index) . "'>" . substr($page_line, 7) . "</a></h2>";
        } elseif ($letters_count <= $preview_len) {
            $preview_string .= $page_line;
            $letters_count += strlen($page_line);
        }
    }

    echo substr($preview_string, 0, $preview_len);
}

// Function to prepare link for GET
function prepare_link_to_get($index) {
    return substr($_SESSION['pages_array'][$index], 6, -4);
}

// Function to generate navigation
function navigation() {
    for ($i = 1; $i <= $_SESSION['num_of_pages']; $i++) {
        echo $_SESSION['page'] == $i ? "<b>$i</b> " : "<a href='index.php?page=$i'>$i</a> ";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo $title; ?></title>
    <link rel="stylesheet" href="/css/mono-color.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>@import url('https://fonts.googleapis.com/css2?family=DM+Serif+Text:ital@0;1&family=Oswald:wght@200..700&display=swap');</style>
</head>
<body>
<div class="container">
    <div class="content">
        <header class="tacenter">
            <a href="/"><img src="/inc/cake3.svg" alt="logo"></a>
            <h1><?php echo $heading1; ?></h1>
        </header>
        <div class="row">
            <div class="2 col"></div>
            <div class="10 col"><hr />
                <main><?php main_contents_preview($preview_len, $items); ?></main>
                <hr />
                <nav><?php navigation(); ?></nav>
            </div>
            <div class="2 col"></div>
        </div>
        <footer>
            <hr/>
            <p><?php echo $footer . date("Y") . " StrawNestSEO CMS"; ?></p>
        </footer>
    </div>
</div>
</body>
</html>