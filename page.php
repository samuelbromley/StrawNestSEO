<?php
session_start();
$timeout = 600; // Session timeout time
$_SESSION['last_activity'] = time(); // Set 'last_activity' time to now
global $first_line;
$first_line = "";

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
$config_vars = ['title' => '', 'heading1' => '', 'heading2' => '', 'footer' => ''];

foreach ($config as $line) {
    foreach ($config_vars as $key => $value) {
        if (match_config_line($line, "$key:")) {
            $config_vars[$key] = trim(substr($line, strlen($key) + 1));
        }
    }
}

extract($config_vars); // Extract variables from the array

// Function to generate single entry preview
function entry_preview($filename) {
    $page_file = fopen($filename, "r");
    $page_body = "";

    while (!feof($page_file)) {
        $page_line = fgets($page_file);
        if (match_config_line($page_line, "=title:")) {
            $page_body .= "<h1>" . substr(trim($page_line), 7) . "</h1><p>";
        } elseif (match_config_line($page_line, "=image:")) {
            $page_body .= "<img style='width: 100%' src='images/" . substr($page_line, 7) . "'>";
        } elseif (match_config_line($page_line, "=imageSmall:")) {
            $page_body .= "<img style='width: 30%' src='images/" . substr($page_line, 12) . "'>";
        } elseif (substr(trim($page_line), 0, 2) === '##') {
            $page_body .= "<h2>" . substr(trim($page_line), 2) . "</h2>";
        } elseif (substr(trim($page_line), 0, 2) === '//') {
            $page_body .= "<h3>" . substr(trim($page_line), 2) . "</h3>";    
        } elseif ($page_line === '' || empty($page_line) || trim($page_line) === '') {
            $page_body = trim($page_body);
            $page_body .= "</p>\r<p>";
        } else {
            $page_body .= $page_line;
        }
    }
    $page_body .= "</p>\r<p><i>Last edited: " . date("d-m-Y H:i:s", filemtime($filename)) . "</i></p>\r";
    echo $page_body;
}

#get JUST the title and then use it for the h1

// Function to get the first line of a file
function get_first_line($filename) {

    global $first_line;
    $first_line = "";    // Check if the file exists
    if (file_exists($filename)) {
        // Open the file for reading
        $file = fopen($filename, "r");

        // Read the first line
        $first_line = fgets($file);

        // Close the file
        fclose($file);

        // Return the first line
        return substr(trim($first_line), 7);

     #   echo substr(trim($first_line), 7);
    } else {
        // Return an error message if the file doesn't exist
        return "The file does not exist.";
    }
}



// Function to get entry path
function entry_path() {
    return "pages/" . $_GET['entry'] . ".txt";
}

$first_line = get_first_line(entry_path());
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title><?php echo $first_line;  ?></title>
    <link rel="stylesheet" href="/css/mono-color.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>@import url('https://fonts.googleapis.com/css2?family=DM+Serif+Text:ital@0;1&family=Oswald:wght@200..700&display=swap');</style>
</head>
<body>
<div class="container">
    <div class="content">
        <header class="tacenter">
            <a href="/"><img src="/inc/cake3.svg" alt="x"></a>
            <p class="blog_header"><a href='/blog'><?php echo trim($heading1); ?></a></p>
        </header>
        <div class="row">
            <div class="2 col"></div>
            <div class="10 col"><hr /><?php entry_preview(entry_path()); ?></div>
            <div class="2 col"></div>
        </div>
        <footer>
            <hr/>
            <p><?php echo $footer . date("Y") . "x"; ?></p>
        </footer>
    </div>
</div>
</body>
</html>
