<div class="container-fluid">
    <div class="row justify-content-center">

        <div class="col-12 p-5 bg-white">

            <h4 class="mb-3">Clientes registados</h4>

            <hr>

            <!-- Exibição quando não existe clientes ------------------------- -->
            <?php if(count($clients) == 0):?>
            <p class="my-4 text-center opacity-75">Não existem clientes registados.</p>

            <div class="text-center mb-5">
                <a href="?ct=main&mt=index" class="btn btn-secondary px-4"><i class="fa-solid fa-chevron-left me-2"></i>Voltar</a>
            </div>

            <?php else:?>
            <!-- Tabela com os dados dos clientes ------------------------ -->
            <table class="table table-striped table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>Nome</th>
                        <th class="text-center">Sexo</th>
                        <th class="text-center">Data nascimento</th>
                        <th>Email</th>
                        <th class="text-center">Telefone</th>
                        <th>Interesses</th>
                        <th>Agente</th>
                        <th>Data de registo</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($clients as $currentClient):?>
                    <tr>
                        <td><?= $currentClient->name ?></td>
                        <td class="text-center"><?= $currentClient->gender ?></td>
                        <td class="text-center"><?= $currentClient->birthdate ?></td>
                        <td><?= $currentClient->email ?></td>
                        <td class="text-center"><?= $currentClient->phone ?></td>
                        <td><?= $currentClient->interests ?></td>
                        <td><?= $currentClient->agent ?></td>
                        <td><?= $currentClient->created_at ?></td>
                    </tr>
                    <?php endforeach;?>
                </tbody>
            </table>

            <div class="row">
                <div class="col">
                    <p class="mb-5">Total: <strong><?= count($clients) ?></strong></p>
                </div>
                <div class="col text-end">
                    <a href="#" class="btn btn-secondary px-4"><i class="fa-regular fa-file-excel me-2"></i>Exportar para XLSX</a>
                    <a href="?ct=main&mt=index" class="btn btn-secondary px-4"><i class="fa-solid fa-chevron-left me-2"></i>Voltar</a>
                </div>
            </div>

            <?php endif;?>
        </div>
    </div>
</div>