<div class="container-fluid mt-5">
    <div class="row justify-content-center">

        <div class="d-flex flex-row flex-wrap justify-content-center">

            <!-- Lógica para carregar o conteúdo dos agentes -->
            <?php if ($user->profile == 'agent'): ?>
                <!-- os meus clientes -->
                <a href="?ct=agent&mt=my_clients" class="unlink m-2">
                    <div class="home-option p-5 text-center">
                        <h3 class="mb-3"><i class="fa-solid fa-users"></i></h3>
                        <h5>Os meus clientes</h5>
                    </div>
                </a>

                <!-- adicionar clientes -->
                <a href="?ct=agent&mt=new_client_frm" class="unlink m-2">
                    <div class="home-option p-5 text-center">
                        <h3 class="mb-3"><i class="fa-solid fa-user-plus"></i></h3>
                        <h5>Adicionar clientes</h5>
                    </div>
                </a>

                <!-- carregar ficheiro de clientes -->
                <a href="?ct=agent&mt=show_upload_form" class="unlink m-2">
                    <div class="home-option p-5 text-center">
                        <h3 class="mb-3"><i class="fa-solid fa-upload"></i></h3>
                        <h5>Carregar ficheiro</h5>
                    </div>
                </a>
            <?php endif; ?> <!-- FIM da Lógica para carregar o conteúdo dos agentes -->

            <!-- Lógica para carregar o conteúdo do ADMIN -->
            <!-- clientes -->
            <?php if($user->profile == 'admin'): ?>
            <a href="?ct=admincontroller&mt=show_all_clients" class="unlink m-2">
                <div class="home-option p-5 text-center">
                    <h3 class="mb-3"><i class="fa-solid fa-users"></i></h3>
                    <h5>Clientes</h5>
                </div>
            </a>

            <!-- estatística -->
            <a href="?ct=admincontroller&mt=show_statistics" class="unlink m-2">
                <div class="home-option p-5 text-center">
                    <h3 class="mb-3"><i class="fa-solid fa-chart-column"></i></h3>
                    <h5>Estatística</h5>
                </div>
            </a>

            <!-- gestão de utilizadores -->
            <a href="#" class="unlink m-2">
                <div class="home-option p-5 text-center">
                    <h3 class="mb-3"><i class="fa-solid fa-user-gear"></i></h3>
                    <h5>Gestão de utilizadores</h5>
                </div>
            </a>
            <?php endif;?> <!-- FIM Lógica para carregar o conteúdo do ADMIN -->

        </div>

    </div>
</div>