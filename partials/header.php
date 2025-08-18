<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>MCiSmartSpace</title>

    <link rel="stylesheet" href="https://cdn.materialdesignicons.com/4.9.95/css/materialdesignicons.min.css">

    <!-- Bootstrap -->
    <link href="../vendors/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="../vendors/fontawesome/css/font-awesome.min.css" rel="stylesheet">
    <!-- jQuery custom content scroller -->
    <link href="../vendors/malihu-custom-scrollbar-plugin/jquery.mCustomScrollbar.min.css" rel="stylesheet" />

    <!-- Custom Theme Style -->
    <link href="../public/css/user_styles/custom.css" rel="stylesheet">
    <link href="../public/css/user_styles/custom2.css" rel="stylesheet">

    <!-- Include our custom CSS -->
    <link href="../public/css/user_styles/room-browser.css" rel="stylesheet">
    <link href="../public/css/user_styles/room-browser-styles.css" rel="stylesheet">
    <link href="../public/css/user_styles/room-reservation.css" rel="stylesheet">
    <link href="../public/css/user_styles/room_status.css" rel="stylesheet">
    <link href="../public/css/user_styles/reservation_history.css" rel="stylesheet">

    <style>
        .title {
            font-size: 1.75rem;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 0.5rem;
            margin-top: 1.5rem;
        }

        .subtitle {
            color: #64748b;
            font-size: 1rem;
            margin-bottom: 1.5rem;
        }

        @media (max-width: 768px) {
            .view-toggle.btn-group {
                display: none;
            }
        }

        .status-tabs {
            display: flex;
            border-bottom: none;
            margin-bottom: 2rem;
            overflow-x: auto;
            gap: 0.75rem;
            padding-bottom: 0.5rem;
            scroll-behavior: smooth;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: none;
            /* Hide scrollbar for Firefox */
            -ms-overflow-style: none;
            /* Hide scrollbar for IE and Edge */
            position: relative;
            padding-left: 0.25rem;
            padding-right: 0.25rem;
        }

        /* Hide scrollbar for Webkit browsers */
        .status-tabs::-webkit-scrollbar {
            display: none;
        }
    </style>
</head>

<body class="nav-md">
    <div class="container body">
        <div class="main_container">