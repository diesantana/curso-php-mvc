<div class="container-fluid bng-navbar">
    <div class="row">

        <div class="col-6 d-flex align-content-center p-3">
            <a href="?" class="d-flex align-items-center text-decoration-none link-light">
                <img src="assets/images/logo_32.png" alt="logo bng" height="32" class="me-3">
                <h3 class="mb-0"><?= APP_NAME ?></h3>
            </a>
        </div>

        <div class="col-6 text-end p-3">

            <div class="dropdown">
                <button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <!-- Username -->
                <i class="fa-regular fa-user me-2"></i><?= $user->name?> 
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="#"><i class="fa-solid fa-key me-2"></i>Alterar password</i></a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="?ct=main&mt=logout"><i class="fa-solid fa-right-from-bracket me-2"></i>Sair</a></li>
                </ul>
            </div>
        </div>

    </div>
</div>