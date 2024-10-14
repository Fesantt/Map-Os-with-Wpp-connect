MAAP-OS 4.47.0

Integração com WppConnect

como utilizar

```sh
enviar mensagem 'texto'
$this->load->library('WhatsappConect');
$this->load->model('Whatsapp_model');

$this->whatsappconect->sendMessageText($identifier, $token, $phone, $text);

Verificar se numero tem whatsapp

$this->whatsappconect->checkNumber($identifier, $token, $phone)
```
