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

## Como Contribuir
Sinta-se à vontade para abrir issues ou enviar pull requests. Toda contribuição é bem-vinda!

## Licença
Este projeto é licenciado sob a MIT License - veja o arquivo [LICENSE](LICENSE) para detalhes.
