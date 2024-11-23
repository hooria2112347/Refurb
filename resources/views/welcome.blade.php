<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Refurb</title>
    <link rel="icon" href="/images/icon.png" type="image/x-icon">
    @vite('resources/js/app.js') <!-- Ensures Vite compiles the assets correctly -->
</head>
<body>
    <div id="app"></div> <!-- Vue will mount here -->
</body>
</html>
