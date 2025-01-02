<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Refurb</title>
    <link rel="icon" href="/images/icon.png" type="image/x-icon">
    @vite('resources/js/app.js') <!-- Ensures Vite compiles the assets correctly --><!-- In the <head> section -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
        
        <!-- At the end of the body section -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
        
</head>
<body>
    <div id="app"></div> <!-- Vue will mount here -->
</body>
</html>
