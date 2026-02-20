<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-12 p-5 bg-white">

            <div class="row">
                <div class="col">
                    <h4>Gestão de agentes</h4>
                </div>
                <div class="col text-end">
                    <a href="?ct=admincontroller&mt=show_new_agent_form" class="btn btn-secondary"><i class="fa-solid fa-user-plus me-2"></i>Novo agente</a>
                </div>
            </div>

            <hr>
            <?php if (count($agentsData) == 0): ?>
                <p class="my-5 text-center opacity-75">Não existem agentes registados.</p>

                <div class="mb-5 text-center">
                    <a href="?ct=main&mt=index" class="btn btn-secondary px-4"><i class="fa-solid fa-chevron-left me-2"></i>Voltar</a>
                </div>
            <?php else: ?>
                <table class="table table-striped table-bordered" id="global_users">
                    <thead class="table-dark">
                        <tr>
                            <!-- | Nome | Perfil | Registado | Estado | Último login | Clientes | Ações | -->
                            <th class="text-center">Nome</th>
                            <th class="text-center">Perfil</th>
                            <th class="text-center">Registrado</th>
                            <th class="text-center">Estado</th>
                            <th class="text-center">Último login</th>
                            <th class="text-center">Clientes</th>
                            <th class="text-center">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Dados dos agentes -->
                        <?php foreach ($agentsData as $currentAgent): ?>
                            <tr>
                                <!-- username -->
                                <td><?= $currentAgent->name ?></td>
                                <!-- Perfil -->
                                <td class="text-center"><?= $currentAgent->profile ?></td>
                                <!-- Senha registrada -->
                                <?php if (!empty($currentAgent->passwrd)) : ?>
                                    <td class="text-center">
                                        <span class="text-success"
                                            data-bs-toggle="tooltip"
                                            data-bs-placement="top"
                                            title="Cadastro concluído">
                                            ✔
                                        </span>
                                    </td>
                                <?php else : ?>
                                    <td class="text-center">
                                        <span
                                            class="text-danger"
                                            data-bs-toggle="tooltip"
                                            data-bs-placement="top"
                                            title="O agente não definiu a senha.">
                                            ✖
                                        </span>
                                    </td>
                                <?php endif; ?>
                                <!-- Estado (Deletado ou Ativo) -->
                                <?php if (empty($currentAgent->deleted_at)) : ?>
                                    <td class="text-center">
                                        <span
                                            class="badge bg-success"
                                            data-bs-toggle="tooltip"
                                            data-bs-placement="top"
                                            title="O agente está ativo no sistema.">
                                            Ativo
                                        </span>
                                    </td>
                                <?php else : ?>
                                    <td class="text-center">
                                        <span class="badge bg-secondary"
                                            data-bs-toggle="tooltip"
                                            data-bs-placement="top"
                                            title="O agente foi deletado.">
                                            Inativo
                                        </span>
                                    </td>
                                <?php endif; ?>
                                <!-- Ultimo Login -->
                                <td class="text-center"><?= $currentAgent->last_login ?></td>
                                <!-- Qtd de clientes -->
                                <td class="text-center"><?= $currentAgent->total ?></td>

                                <!-- Ação -->
                                <td class="text-center">
                                    <?php if ($currentAgent->profile == 'admin'): ?>
                                        <!-- Se Admin = Somente leitura -->
                                        <span
                                            class="badge bg-secondary"
                                            data-bs-toggle="tooltip"
                                            data-bs-placement="top"
                                            title="Você não tem permissão para alterar esse agente.">
                                            <i class="fa-solid fa-lock me-2"></i>Somente leitura
                                        </span>
                                    <?php elseif (empty($currentAgent->deleted_at)): ?>
                                        <!-- Não é admin && Não está deletado = Editar - Deleter -->
                                        <a href="<?= '?ct=admincontroller&mt=show_user_edit_form&id=' . urlencode($currentAgent->id) ?>">
                                            <i class="fa-regular fa-pen-to-square me-2"></i>Editar
                                        </a>
                                        <span class="mx-2 opacity-50">|</span>
                                        <a href="<?= '?ct=admincontroller&mt=show_user_delete_confirmation&id=' . urlencode($currentAgent->id) ?>"><i class="fa-solid fa-trash-can me-2"></i>Eliminar</a>
                                    <?php else: ?>
                                        <!-- Não é admin && Está deletado = Recuperar -->
                                        <a href="<?= '?ct=admincontroller&mt=show_user_recover_confirmation&id=' . urlencode($currentAgent->id) ?>">
                                            <i class="fa-solid fa-rotate-left me-2"></i>Recuperar
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <div class="row mt-4">
                    <div class="col">
                        <p class="mb-5">Total: <strong><?= count($agentsData) ?></strong></p>
                    </div>
                    <div class="col text-end">
                        <a href="?ct=admincontroller&mt=export_global_agents_xlsx" class="btn btn-secondary px-4"><i class="fa-regular fa-file-excel me-2"></i>Exportar para XLSX</a>
                        <a href="?ct=main&mt=index" class="btn btn-secondary px-4"><i class="fa-solid fa-chevron-left me-2"></i>Voltar</a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<script>
    $(document).ready(function() {

        // Inicializa DataTable
        const table = $('#global_users').DataTable({
            pageLength: 10,
            pagingType: "full_numbers",
            language: {
                decimal: "",
                emptyTable: "Sem dados disponíveis na tabela.",
                info: "Mostrando _START_ até _END_ de _TOTAL_ registos",
                infoEmpty: "Mostrando 0 até 0 de 0 registos",
                infoFiltered: "(Filtrando _MAX_ total de registos)",
                infoPostFix: "",
                thousands: ",",
                lengthMenu: "Mostrando _MENU_ registos por página.",
                loadingRecords: "Carregando...",
                processing: "Processando...",
                search: "Filtrar:",
                zeroRecords: "Nenhum registro encontrado.",
                paginate: {
                    first: "Primeira",
                    last: "Última",
                    next: "Seguinte",
                    previous: "Anterior"
                },
                aria: {
                    sortAscending: ": ative para classificar a coluna em ordem crescente.",
                    sortDescending: ": ative para classificar a coluna em ordem decrescente."
                }
            }
        });

        // Função para ativar tooltips
        function initTooltips() {
            const tooltipTriggerList = [].slice.call(
                document.querySelectorAll('[data-bs-toggle="tooltip"]')
            );

            tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        }

        // Inicializa na primeira carga
        initTooltips();

        // Reinicializa toda vez que o DataTable redesenhar
        table.on('draw', function() {
            initTooltips();
        });

    });
</script>