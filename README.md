# Cobrança Fácil

# Índice

1. [Cobrança Fácil](#cobrança-fácil)
   - [Sobre o Projeto](#sobre-o-projeto)
   - [Como Iniciar o Projeto](#como-iniciar-o-projeto)
   - [Comandos Úteis](#comandos-úteis)
   - [Endpoints](#endpoints)
      - [Health Check](#health-check)
      - [Invoice (Listagem)](#invoice-listagem)
      - [Invoice (Criação)](#invoice-criação)
      - [Daily Invoices](#daily-invoices)
      - [Webhook](#webhook)


## Sobre o Projeto
O Cobrança Fácil é um sistema de cobrança desenvolvido como parte de um desafio. Ele permite:

1. Receber uma lista de cobranças de um arquivo CSV via API, contendo informações como nome, CPF, e-mail, valor da dívida, vencimento da dívida e código da dívida
2. Gerar periodicamente boletos para cobrança e envia e-mails de cobrança para a lista fornecida.
3. Receber uma comunicação via webhook em formato JSON do banco, informando que um boleto foi pago e liquidado diretamente na conta bancária da empresa. Essa informação é utilizada para atualizar o status do boleto no sistema e realizar a baixa correspondente.

## Como Iniciar o Projeto
Siga as etapas abaixo para iniciar o projeto:

1. Clone o repositório para o seu ambiente local:
```bash
git clone https://github.com/Lorenalgm/cobranca_facil
```
2. Acesse o diretório do projeto:
```bash
cd cobranca_facil
```
3. Crie o arquivo `.env` a partir do arquivo `.env.example`:
```bash
cp .env.example .env
```
4. Abra o arquivo `.env` e configure as variáveis de ambiente:
```php
DB_CONNECTION=pgsql
DB_HOST=pgsql
DB_PORT=5432
DB_DATABASE=cobranca_facil
DB_USERNAME=sail
DB_PASSWORD=password
```
5. Inicie o projeto com o Docker:
```bash
docker-compose up -d
```
6. Execute as migrações do banco de dados dentro do contêiner do aplicativo:
```bash
docker exec -it cobranca_facil_laravel.test_1 php artisan migrate
```
8. O projeto estará disponível em http://localhost:8000.

## Comandos Úteis

Aqui estão alguns comandos úteis que você pode usar neste projeto:

- `php artisan schedule:list`: Lista todos os agendamentos disponíveis.
- `php artisan schedule:run`: Executa os agendamentos registrados.
- `php artisan test`: Executa os testes automatizados.
- `php artisan migrate`: Executa as migrações do banco de dados.

## Endpoints

Neste projeto existem alguns endpoints disponíveis para uso.

Lembre-se de informar um token de autenticação ("xxxxxx" para testes).

Caso utilize Insomnia, você pode importar este [arquivo .json](./Insomnia_2023-07-03.json) com todos os endpoints disponíveis. 

### Health Check
```
- Método: GET
- URL: http://localhost/api/health_check
```
Este endpoint é usado para verificar o status de saúde do sistema.

### Invoice (Listagem)
```
- Método: GET
- URL: http://localhost/api/v1/invoices
- Autenticação: Bearer Token
```
Este endpoint retorna uma lista de faturas.

### Invoice (Criação)
```
- Método: POST
- URL: http://localhost/api/v1/invoices
- Autenticação: Bearer Token
- Tipo de Conteúdo: multipart/form-data
```
Este endpoint é usado para criar uma nova fatura. É necessário fornecer um arquivo CSV.

### Daily Invoices
```
- Método: GET
- URL: http://localhost/api/v1/daily
- Autenticação: Bearer Token
```
Este endpoint retorna as faturas diárias.

### Webhook
```
- Método: POST
- URL: http://localhost:8000/webhook
- Autenticação: Bearer Token
- Tipo de Conteúdo: application/json
```
Este endpoint é usado para receber notificações de pagamento de boleto via webhook.
