<div class="container-fluid mb-5 bg-white">
    <div class="row justify-content-center pb-5">
        <div class="col-12 p-4">

            <div class="row">
                <div class="col">
                    <h4><strong>Dados estatísticos</strong></h4>
                </div>
                <div class="col text-end">
                    <a href="?ct=main&mt=index" class="btn btn-secondary px-4"><i class="fa-solid fa-chevron-left me-2"></i>Voltar</a>
                </div>
            </div>

            <hr>

            <div class="row mb-3">
                <div class="col-sm-6 col-12 p-1">
                    <div class="card p-3">
                        <h4><i class="fa-solid fa-users me-2"></i>Clientes dos agentes</h4>
                        <!-- IF (PHP)-------------------------------------------------------------------- -->
                        <?php if (empty($clientsPerAgents)): ?>
                            <p class="my-5 text-center opacity-75">Não existem clientes registados.</p>
                            <!-- ELSE (PHP)-------------------------------------------------------------------- -->
                        <?php else: ?>
                            <table class="table table-striped table-bordered" id="clients_per_agents">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Agente</th>
                                        <th class="text-center">Clientes registrados</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($clientsPerAgents as $data): ?>
                                        <tr>
                                            <td><?= $data->name ?></td>
                                            <td class="text-center"><?= $data->total ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                            <!-- ENDIF (PHP)-------------------------------------------------------------------- -->
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-sm-6 col-12 p-1">
                    <div class="card p-3">
                        <h4><i class="fa-solid fa-users me-2"></i>Gráfico</h4>
                        [gráfico]
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col p-1">

                <div class="card p-3">
                    <h4><i class="fa-solid fa-list-ul me-2"></i>Dados estatísticos globais</h4>
                    [dados estatísticos]
                </div>

            </div>
        </div>

        <div class="row mb-3">
            <div class="col text-center">
                <a href="?ct=main&mt=index" class="btn btn-secondary px-4"><i class="fa-solid fa-chevron-left me-2"></i>Voltar</a>
            </div>
        </div>

    </div>
</div>
</div>
</div>