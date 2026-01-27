<div class="container-fluid mt-5 mb-5">
    <div class="row justify-content-center pb-5">
        <div class="col-lg-8 col-md-10">
            <div class="card p-4">

                <div class="row justify-content-center">
                    <div class="col-10">

                        <h4 class="mb-4"><strong>Carregar ficheiro de clientes</strong></h4>
                        
                        <p class="text-center">Carregar ficheiro em formato CSV ou XLSX. Se não tem o template do ficheiro, faça download <a href="assets/file_template/template.xlsx">AQUI</a></p>

                        <hr>

                        <form action="?ct=agent&mt=handle_upload" method="post" enctype="multipart/form-data" novalidate>

                            <div class="mb-4">
                                <label for="clients_file" class="form-label">Ficheiro de clientes</label>
                                <input type="file" name="clients_file" id="clients_file" value="" class="form-control" required>
                            </div>
                            
                            <div class="mb-4 text-center">
                                <a href="?ct=agent&mt=my_clients" class="btn btn-secondary"><i class="fa-solid fa-xmark me-2"></i>Cancelar</a>
                                <button type="submit" class="btn btn-secondary"><i class="fa-solid fa-upload me-2"></i>Carregar</button>
                            </div>

                            <!-- Mensagens de erro -->
                            <?php if(isset($serverError)): ?>
                                <div class="alert alert-danger p-2 text-center">
                                    <?= $serverError ?>
                                </div>
                            <?php endif;?>
                            <!-- Relatório -->
                            <?php if(isset($report)): ?>
                                <div class="alert alert-info p-2">
                                    <ul>
                                        <li>Arquivo: <?=$report['fileName']?></li>
                                        <li>Total de registros encontrados no arquivo: <?=$report['total']?></li>
                                        <li>Total de registros adicionados: <?=$report['totalAdded']?></li>
                                        <li>Total de registros ignorados: <?=$report['totalIgnored']?></li>
                                    </ul>
                                </div>
                            <?php endif;?>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>