<?php
$requestedUri = $_SERVER['REQUEST_URI'] ?? ''; // Armazena a URL que não existe
?>

<div class="container-fluid mt-5">
    <div class="row justify-content-center">
        <div class="col-lg-5 col-md-6 col-sm-8 col-10">
            <div class="card p-4">

                <div class="d-flex align-items-center justify-content-center my-4">
                    <img src="assets/images/logo_64.png" class="img-fluid me-3" alt="Logo">
                    <h2><strong><?= APP_NAME ?></strong></h2>
                </div>

                <div class="row justify-content-center">
                    <div class="col-10">

                        <div class="text-center mb-3">
                            <h1 class="mb-2"><strong>404</strong></h1>
                            <h5 class="mb-3">Página não encontrada</h5>
                            <p class="text-muted mb-0">
                                A rota solicitada não existe ou foi removida.
                            </p>

                            <!-- Exibe a URL na view -->
                            <?php if (!empty($requestedUri)): ?>
                                <p class="text-muted mt-2 mb-0" style="word-break: break-all;">
                                    <small>URL: <?= htmlspecialchars($requestedUri, ENT_QUOTES, 'UTF-8') ?></small>
                                </p>
                            <?php endif; ?>
                        </div>

                        <div class="d-grid gap-2 mt-4">
                            <a href="/BNG/public/" class="btn btn-secondary">
                                Ir para a Home
                                <i class="fa-solid fa-house ms-2"></i>
                            </a>

                            <a href="?ct=main&mt=login_frm" class="btn btn-outline-secondary">
                                Ir para Login
                                <i class="fa-solid fa-right-to-bracket ms-2"></i>
                            </a>
                        </div>

                        <div class="text-center mt-4">
                            <small class="text-muted">
                                Se você acredita que isso é um erro, tente novamente ou contate o suporte.
                            </small>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
