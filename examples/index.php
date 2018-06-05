<?php
$schema = (@$_SERVER["HTTPS"] == "on") ? "https://" : "http://";
$fullUrl = $schema . $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
$parsedURL = parse_url($fullUrl);
$basePath = $parsedURL['path'];
define('BASE_PATH', $basePath);

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/elements/functions.php';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>arajcany\PrePressTricks</title>

    <?php require_once('elements/corebs.phtml') ?>

</head>

<body>

<?php require_once('elements/navbar.phtml') ?>

<main role="main" class="container">

    <div class="starter-template">
        <h1>PrePressTricks Examples</h1>

        <?php if (!isset($parsedURL['query']) || $parsedURL['query'] == '') { ?>
            <p class="lead">
                Select an Example from the Menu.
            </p>
        <?php } ?>
    </div>

    <?php if (isset($parsedURL['query'])) { ?>
        <div class="row">
            <div class="col-12">
                <?php
                parse_str($parsedURL['query'], $queryArray);
                $folder = $queryArray['f'];
                $page = $queryArray['p'];
                require_once(__DIR__ . "/{$folder}/{$page}.php");
                ?>
            </div>
        </div>
    <?php } ?>


</main><!-- /.container -->

<?php require_once('elements/corejs.phtml') ?>

</body>
</html>

