<!-- top navigation -->
<div class="top_nav">
    <div class="nav_menu">
        <div class="nav toggle">
            <label class="hamburger">
                <input type="checkbox" id="menu_toggle" />
                <svg viewBox="0 0 32 32">
                    <path
                        class="line line-top-bottom"
                        d="M27 10 13 10C10.8 10 9 8.2 9 6 9 3.5 10.8 2 13 2 15.2 2 17 3.8 17 6L17 26C17 28.2 18.8 30 21 30 23.2 30 25 28.2 25 26 25 23.8 23.2 22 21 22L7 22"></path>
                    <path class="line" d="M7 16 27 16"></path>
                </svg>
            </label>
        </div>
        <nav class="nav navbar-nav">
            <ul class=" navbar-right">
                <li class="nav-item dropdown open" style="padding-left: 15px;">
                    <a href="javascript:;" class="user-profile dropdown-toggle" aria-haspopup="true" id="navbarDropdown" data-toggle="dropdown" aria-expanded="false">
                        <span>Hello, <?php echo $_SESSION['first_name']; ?></span>
                    </a>
                    <div class="dropdown-menu dropdown-usermenu pull-right" aria-labelledby="navbarDropdown">

                        <a class="dropdown-item" href="edit_profile.php"><i class="fa fa-user pull-right"></i>Edit Profile</a>
                        <a class="dropdown-item" href="../auth/logout.php"><i class="fa fa-sign-out pull-right"></i> Log Out</a>
                    </div>
                </li>

                <li role="presentation" class="nav-item dropdown open">
                    <!-- Notifications dropdown -->
                    <!-- ... -->
                </li>
            </ul>
        </nav>
    </div>
</div>
<!-- /top navigation -->

<style>
    .hamburger {
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-top: 10px;
    }

    .hamburger input {
        display: none;
    }

    .hamburger svg {
        /* The size of the SVG defines the overall size */
        height: 2.5em;
        /* Define the transition for transforming the SVG */
        transition: transform 400ms cubic-bezier(0.4, 0, 0.2, 1);
    }

    .line {
        fill: none;
        stroke: #0f4228;
        stroke-linecap: round;
        stroke-linejoin: round;
        stroke-width: 2.5;
        transition:
            stroke-dasharray 400ms cubic-bezier(0.4, 0, 0.2, 1),
            stroke-dashoffset 400ms cubic-bezier(0.4, 0, 0.2, 1);
    }

    .line-top-bottom {
        stroke-dasharray: 12 63;
    }

    .hamburger input:checked+svg {
        transform: rotate(-45deg);
    }

    .hamburger input:checked+svg .line-top-bottom {
        stroke-dasharray: 20 300;
        stroke-dashoffset: -32.42;
    }

    .nav.toggle {
        display: flex;
        align-items: center;
        height: 100%;
        padding-left: 10px;
    }
</style>