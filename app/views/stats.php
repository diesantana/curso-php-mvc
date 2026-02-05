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
                        <canvas id="myChart" height="300px"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col p-1">

                <div class="card p-3">
                    <h4><i class="fa-solid fa-list-ul me-2"></i>Dados estatísticos globais</h4>
                    <!-- IF (PHP)-------------------------------------------------------------------- -->
                    <?php if (empty($clientsPerAgents)): ?>
                        <p class="my-5 text-center opacity-75">Não existem clientes registados.</p>
                        <!-- ELSE (PHP)-------------------------------------------------------------------- -->
                    <?php else: ?>
                        <div class="row justify-content-center">
                            <div class="col-5">
                                <table class="table table-striped">
                                    <!-- Total de agentes -->
                                    <tr>
                                        <td class="text-start">Número total de agentes:</td>
                                        <td class="text-start">
                                            <strong><?= $globalStatistics['totalAgents'] ?></strong>
                                        </td>
                                    </tr>
                                    <!-- Total de clientes -->
                                    <tr>
                                        <td class="text-start">Número total de clientes:</td>
                                        <td class="text-start">
                                            <strong><?= $globalStatistics['totalClients'] ?></strong>
                                        </td>
                                    </tr>
                                    <!-- Total de clientes inativos -->
                                    <tr>
                                        <td class="text-start">Número total de clientes inativos:</td>
                                        <td class="text-start">
                                            <strong><?= $globalStatistics['totalClientsInactives'] ?></strong>
                                        </td>
                                    </tr>
                                    <!-- Média de clientes por agente -->
                                    <tr>
                                        <td class="text-start">Número médio de clientes por agente:</td>
                                        <td class="text-start">
                                            <strong><?= $globalStatistics['AverageClientsPerAgent'] ?></strong>
                                        </td>
                                    </tr>
                                    <!-- Cliente mais novo -->
                                    <tr>
                                        <td class="text-start">Idade do cliente mais novo:</td>
                                        <td class="text-start">
                                            <strong><?= $globalStatistics['youngerAge'] ?></strong>
                                        </td>
                                    </tr>
                                    <!-- Cliente mais velho -->
                                    <tr>
                                        <td class="text-start">Idade do cliente mais velho:</td>
                                        <td class="text-start">
                                            <strong><?= $globalStatistics['olderAge'] ?></strong>
                                        </td>
                                    </tr>
                                    <!-- Média de idade dos clientes -->
                                    <tr>
                                        <td class="text-start">Média de idade dos clientes:</td>
                                        <td class="text-start">
                                            <strong><?= $globalStatistics['averageAge'] ?></strong>
                                        </td>
                                    </tr>
                                    <!-- % de clientes homens -->
                                    <tr>
                                        <td class="text-start">Portentagem de clientes homens:</td>
                                        <td class="text-start">
                                            <strong><?= $globalStatistics['percentageMen'] ?>%</strong>
                                        </td>
                                    </tr>
                                    <!-- % de clientes mulheres -->
                                    <tr>
                                        <td class="text-start">Portentagem de clientes mulheres:</td>
                                        <td class="text-start">
                                            <strong><?= $globalStatistics['percentageWomen'] ?>%</strong>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        <!-- ENDIF (PHP)-------------------------------------------------------------------- -->
                    <?php endif; ?>
                    <div class="text-center">
                        <a href="?ct=admincontroller&mt=export_statistics_pdf" class="btn btn-secondary px-4">
                            <i class="fa-solid fa-file-pdf me-2"></i>
                            Criar relatório em PDF
                        </a>
                    </div>
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
<script>
    <?php if (count($clientsPerAgents) != 0): ?>
        const ctx = document.getElementById('myChart');
        new Chart('myChart', {
            type: 'bar',
            data: {
                labels: <?= $chartLabels ?>,
                datasets: [{
                    label: 'Total de clientes por agente',
                    data: <?= $chartTotals ?>,
                    backgroudColor: 'rbg(50,100,200)',
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    <?php endif; ?>
</script>