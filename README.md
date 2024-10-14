MAAP-OS 4.47.0

Integração com WppConnect



.env

```sh
WHATSAPP_ENDPOINT=http://localhost:9090/api     # ex: url do seu wpp connect
WHATSAPP_SECRET_KEY=123456                      # secretKey do seu wpp connect

```

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
Views
```sh
https://seusite.com/index.php/whatsapp

```
