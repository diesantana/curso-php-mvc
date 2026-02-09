<div class="container-fluid mt-5 mb-5">
    <div class="row justify-content-center pb-5">
        <div class="col-lg-8 col-md-10">
            <div class="card p-4">

                <div class="row justify-content-center">
                    <div class="col-10">

                        <h4><strong>Adicionar novo agente</strong></h4>

                        <hr>
                                                    <!-- Exibe erros de validação -->
                            <?php if (!empty($validationErrors)): ?>
                                <div class="alert alert-danger p-2 text-center">
                                    <ul>
                                        <?php foreach ($validationErrors as $error): ?>
                                            <li><?= $error ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>

                            <!-- Exibe erros no servidor -->
                            <?php if (!empty($serverErrors)): ?>
                                <div class="alert alert-danger p-2 text-center"><?= $serverErrors ?></div>
                            <?php endif; ?>

                        <form action="?ct=admincontroller&mt=handle_new_agent" method="post" novalidate>

                            <div class="mb-3">
                                <label for="text_email" class="form-label">Email</label>
                                <input type="email" name="text_email" id="text_email" value="" class="form-control" required>
                            </div>

                            <div class="mb-3">
                                <label for="select_profile" class="form-label">Perfil</label>
                                <select name="select_profile" id="select_profile" class="form-control" required>
                                    <option value="admin">Administrador</option>
                                    <option value="agent">Agente</option>
                                </select>
                            </div>

                            <div class="mb-3 text-center">
                                <a href="" class="btn btn-secondary"><i class="fa-solid fa-xmark me-2"></i>Cancelar</a>
                                <button type="submit" class="btn btn-secondary"><i class="fa-solid fa-user-plus me-2"></i>Criar agente</button>
                            </div>

                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>