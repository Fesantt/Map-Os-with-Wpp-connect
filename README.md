MAAP-OS 4.47.0

Integração com WppConnect

Banco de dados

```sh
CREATE TABLE whatsapp (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    token VARCHAR(255) NOT NULL,
    quote TEXT,
    isConnected BOOLEAN DEFAULT FALSE,
    isActive BOOLEAN DEFAULT FALSE,
    qrCodeBase64 TEXT
);

```



como utilizar

```sh
enviar mensagem 'texto'
$this->load->library('WhatsappConect');
$this->load->model('Whatsapp_model');

$this->whatsappconect->sendMessageText($identifier, $token, $phone, $text);

Verificar se numero tem whatsapp

$this->whatsappconect->checkNumber($identifier, $token, $phone)
```
