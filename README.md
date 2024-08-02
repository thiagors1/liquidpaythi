# LiquidPay

LiquidPay é uma solução de pagamento desenvolvida em Laravel, oferecendo funcionalidades robustas para:
- Gerenciamento de contas de usuário.
- Adição e controle de saldos.
- Realização de transferências entre clientes.
- Consulta de extratos detalhados.

## Tecnologias Utilizadas
- **Laravel**: Framework PHP para construção da aplicação.
- **JWT**: Implementado com `firebase/php-jwt` para garantir a segurança das transações.
- **Integração com LiquidBank**: Conexão com o endpoint `https://www.liquidworks.com.br/liquidbank/authorize` para autorizações financeiras.

## Funcionalidades
- **Autenticação Segura**: Utilização de JWT para autenticação e autorização dos usuários.
- **Gerenciamento de Saldo**: Clientes podem adicionar saldo e transferir para outras contas.
- **Consulta de Extratos**: Histórico completo das transações realizadas.

## Requisitos

- PHP >= 8.2
- Composer
- MySQL ou outro banco de dados compatível

## Instalação

1. **Clone o repositório**:

   ```bash
   git clone https://github.com/thiagors1/liquidpaythi.git
   cd liquidpaythi

- Instale as dependências do Composer:

    ```bash
    composer install

Configure o arquivo .env:

Copie o arquivo .env.example para .env:

    cp .env.example .env

Edite o arquivo .env com suas configurações de banco de dados e outras variáveis necessárias. Por exemplo:

 DB_CONNECTION=mysql
 DB_HOST=127.0.0.1
 DB_PORT=3306
 DB_DATABASE=nome_do_banco
 DB_USERNAME=usuario
 DB_PASSWORD=senha

Gere a chave de aplicação:

    php artisan key:generate

Crie o banco de dados:

    No MySQL, você pode criar um banco de dados com o seguinte comando SQL:

    sql

    CREATE DATABASE nome_do_banco;

Execute as migrações:

    php artisan migrate