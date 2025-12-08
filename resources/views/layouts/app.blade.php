<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>@yield('title', 'My App')</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  {{-- Bootstrap CSS --}}
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

  <style>
    body {
      overflow-x: hidden;
    }

    .sidebar {
      width: 240px;
      height: 100vh;
      position: fixed;
      left: 0;
      top: 0;
      background: #1f2937;
      color: white;
      padding-top: 20px;
    }

    .sidebar a {
      color: white;
      text-decoration: none;
      padding: 12px 20px;
      display: block;
    }

    .sidebar a:hover {
      background: #374151;
    }

    .content {
      margin-left: 240px;
      padding: 25px;
    }
  </style>
</head>

<body>

  {{-- Left Side Navbar --}}
  @include('layouts.sidebar')

  {{-- Main Page Content --}}
  <div class="content">
    @yield('content')
  </div>

  {{-- Bootstrap JS --}}
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>