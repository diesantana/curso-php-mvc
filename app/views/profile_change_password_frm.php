<div class="container-fluid my-5">
    <div class="row justify-content-center">
        <div class="col-lg-5 col-md-6 col-sm-8 col-10">
            <div class="card p-4 mb-5">


                <!-- Mensagem de sucesso -->
                <?php if (isset($successMsg)): ?>
                    <div class="alert alert-success p-2 text-center">
                        <?= $successMsg ?>
                    </div>
                <?php endif; ?>
                <form action="?ct=main&mt=handle_change_password" method="post" novalidate>
                    <div class="row justify-content-center">
                        <div class="col-8">

                            <h4 class="mb-3 text-center">Alterar a password</h4>

                            <hr>

                            <div class="mb-3">
                                <label for="text_current_password" class="form-label">Password atual</label>
                                <input type="password" name="text_current_password" id="text_current_password" class="form-control" required>
                            </div>

                            <div class="mb-3">
                                <label for="text_new_password" class="form-label">Nova password</label>
                                <input type="password" name="text_new_password" id="text_new_password" class="form-control" required>
                            </div>

                            <div class="mb-4">
                                <label for="text_repeat_new_password" class="form-label">Repetir a nova password</label>
                                <input type="password" name="text_repeat_new_password" id="text_repeat_new_password" class="form-control" required>
                            </div>

                        </div>
                    </div>

                    <div class="mb-3 text-center">
                        <a href="?ct=main&mt=index" class="btn btn-secondary px-3"><i class="fa-solid fa-xmark me-2"></i>Cancelar</a>
                        <button type="submit" class="btn btn-secondary px-3"><i class="fa-solid fa-check me-2"></i>Alterar</button>
                    </div>

                </form>
                <!-- Erros do servidor -->
                <?php if (isset($serverErrors)): ?>
                    <div class="alert alert-danger p-2 text-center">
                        <ul>
                            <?php foreach ($serverErrors as $error): ?>
                                <li><?= $error ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                <!-- Erros de validação -->
                <?php if (isset($validationErrors)): ?>
                    <div class="alert alert-danger p-2 text-center">
                        <ul>
                            <?php foreach ($validationErrors as $error): ?>
                                <li><?= $error ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </div>
</div>