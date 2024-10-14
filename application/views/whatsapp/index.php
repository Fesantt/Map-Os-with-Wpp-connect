
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    select {
        width: 70px;
    }
    #qrCodeImage {
        width: 200px;
        height: 200px;
        display: none;
        margin: 0 auto;
    }
    #loadingMessage {
        display: none;
        text-align: center;
        margin-top: 10px;
    }
    #loadingSection {
        text-align: center;
        margin-top: 20px;
    }
</style>

<div class="new122">
    <div class="widget-title" style="margin: -20px 0 0">
        <span class="icon">
            <i class="fas fa-wrench"></i>
        </span>
        <h5>Whatsapp</h5>
    </div>
    <div class="span12" style="margin-left: 0">
            <div class="span3 flexxn" style="display: flex;">
                <?php if (empty($sessions)): ?>
                    <button class="button btn btn-mini btn-success" style="max-width: 160px" data-toggle="modal"
                        data-target="#createConnectionModal">
                        <span class="button__icon"><i class='bx bx-plus-circle'></i></span>
                        <span class="button__text2"> Criar Conexão</span>
                    </button>
                <?php endif; ?>
            </div>
      
    </div>
    <div class="widget-box">
        <h5 style="padding: 3px 0"></h5>
        <div class="widget-content nopadding tab-content">
            <table class="table table-bordered table-striped">
                <thead class="thead-dark">
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>Token</th>
                        <th>Status</th>
                        <th>Conexão</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($sessions)): ?>
                        <?php foreach ($sessions as $session): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($session['id']); ?></td>
                                <td id="name_session"><?php echo htmlspecialchars($session['name']); ?></td>
                                <td><?php echo htmlspecialchars(substr($session['token'], 0, 8) . '********'); ?></td>
                                <td><?php echo htmlspecialchars($session['isActive'] ? 'Ativo' : 'Inativo'); ?></td>
                                <td><?php echo htmlspecialchars($session['isConnected'] ? 'Conectado' : 'Aguardando....'); ?></td>
                                <td>
                                    <?php if ($session['isConnected'] != 1): ?>
                                        <button class="btn btn-info" onclick="openQRCode('<?php echo htmlspecialchars($session['qrCodeBase64']); ?>')">Ver QR Code</button>
                                    <?php endif; ?>
                                    <button class="btn btn-danger">Excluir</button>
                                    <?php if ($session['isConnected'] != 1): ?>
                                        <button class="btn btn-primary" onclick="gerarQRCode('<?php echo htmlspecialchars($session['name']); ?>')">Novo Qr-Code</button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center">Nenhuma sessão encontrada.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Seção para exibir o QR Code -->
    <div id="loadingSection">
        <img id="qrCodeImage" src="" alt="QR Code">
        <div id="loadingMessage">
            <i class="fas fa-spinner fa-spin" style="margin-right: 5px;"></i>
            Aguardando que o QR Code seja escaneado...
        </div>
    </div>
</div>

<!-- Modal Criar Conexão -->
<div id="createConnectionModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Criar Conexão</h5>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="modal-body">
                <form id="createConnectionForm">
                    <div class="form-group">
                        <label for="connectionName">Nome:</label>
                        <input type="text" class="form-control" id="connectionName" name="name" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                <button type="button" class="btn btn-primary" id="submitCreateConnection">Criar</button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.getElementById('submitCreateConnection').addEventListener('click', function () {
            var formData = new FormData(document.getElementById('createConnectionForm'));

            fetch('<?php echo base_url('index.php/whatsapp/getToken'); ?>', {
                method: 'POST',
                body: formData,
            })
            .then(response => response.text())
            .then(data => {
                if (data.includes("Token gerado com sucesso:")) {
                    
                    location.reload();
                } else {
                    location.reload();
                }
            })
            .catch(error => {
                console.error('Erro:', error);
            });
        });
    });

    const MAX_RETRIES = 50;

    window.gerarQRCode = function (sessionName, attempt = 0) {
        var formData = new FormData();
        formData.append('name', sessionName);

        if (attempt === 0) {
            Swal.fire({
                title: 'Gerando QR Code...',
                html: 'Por favor, aguarde...',
                allowOutsideClick: false,
                onBeforeOpen: () => {
                    Swal.showLoading();
                }
            });
        }

        
        fetch('<?php echo base_url('index.php/whatsapp/refreshQRCode'); ?>', {
            method: 'POST',
            body: formData 
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                
                Swal.close();
                
                openQRCode(data.qrcode);
                
                checkConnection(sessionName); 
            } else {
                handleError(sessionName, attempt);
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            handleError(sessionName, attempt);
        });
    };

    function checkConnection(sessionName, attempt = 0) {
        if (attempt < MAX_RETRIES) {
            fetch('<?php echo base_url('index.php/whatsapp/check'); ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ name: sessionName }) 
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === true) {
                    Swal.fire('Sucesso', 'Conectado com sucesso!', 'success');
                    setTimeout(function() {
                        location.reload();
                    }, 2000);
                } else {
                    
                    setTimeout(() => {
                        checkConnection(sessionName, attempt + 1);
                    }, 2000);
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                setTimeout(() => {
                    checkConnection(sessionName, attempt + 1);
                }, 2000); 
            });
        } else {
            Swal.fire('Erro', 'Não foi possível conectar após várias tentativas.', 'error');
        }
    }

    function handleError(sessionName, attempt) {
        if (attempt < MAX_RETRIES) {
            
            Swal.update({
                html: 'Aguarde estamos gerando o QR-CODE... (' + (attempt + 1) + '/' + MAX_RETRIES + ')',
            });
            
            setTimeout(() => {
                gerarQRCode(sessionName, attempt + 1);
            }, 2000);
        } else {
            Swal.fire('Erro', 'Ocorreu um erro ao gerar o QR Code após várias tentativas.', 'error');
        }
    }

    function openQRCode(base64String) {
        var qrCodeImage = document.getElementById("qrCodeImage");
        qrCodeImage.src = base64String; // Atualiza o src com a imagem Base64
        qrCodeImage.style.display = "block"; // Exibe a imagem
    }
</script>

