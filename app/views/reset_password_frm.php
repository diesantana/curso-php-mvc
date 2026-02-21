<div class="container-fluid py-3">
    <div class="row justify-content-center">
        <div class="col-lg-5 col-md-6 col-sm-8 col-10">
            <div class="card p-4">

                <div class="d-flex align-items-center justify-content-center my-4">
                    <img src="assets/images/logo_64.png" class="img-fluid me-3">
                    <h2><strong><?= APP_NAME ?></strong></h2>
                </div>

                <div class="row justify-content-center">
                    <div class="col-12 px-3">

                        <p class="text-center">
                            Informe seu <strong>e-mail</strong> cadastrado.<br>
                            Enviaremos um <strong>código de verificação</strong> para que você possa redefinir sua senha.
                        </p>

                        <form action="?ct=main&mt=handle_recover_password" method="post" novalidate>

                            <div class="mb-4">
                                <label for="text_username" class="form-label">E-mail</label>
                                <input type="email" name="text_username" id="text_username" value="" class="form-control" required>
                            </div>

                            <div class="mb-4 text-center">
                                <a href="?ct=main&mt=index" class="btn btn-secondary me-2">
                                    Voltar
                                </a>

                                <button type="submit" class="btn btn-secondary">
                                    Enviar código
                                </button>
                            </div>

                        </form>

                        <!-- ERROS DE VALIDAÇÃO -->
                        <?php if (!empty($validation_errors)): ?>
                            <div class="alert alert-danger p-2 text-center">
                                <?php foreach ($validation_errors as $error): ?>
                                    <p class="mb-1"><?= $error ?></p>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <!-- ERROS DO SERVIDOR -->
                        <?php if (!empty($server_error)): ?>
                            <div class="alert alert-danger p-2 text-center">
                                <?php foreach ($server_error as $error): ?>
                                    <p class="mb-1"><?= $error ?></p>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                    </div>
                </div>

            </div>
        </div>
    </div>
</div>