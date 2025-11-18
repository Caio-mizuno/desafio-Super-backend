# Como Rodar o Projeto (Docker + Variáveis de Ambiente)

Este guia explica, do zero, como subir o ambiente com Docker, configurar o `.env` e acessar a API e a documentação.

## Pré-requisitos
- Docker Desktop instalado e em execução
- Docker Compose disponível

## Estrutura do Projeto
- Código Laravel está em `Application`
- Orquestração via `docker-compose.yml`
- Imagens e ajustes em `Dockerfile`

## Serviços e Portas
- API (Nginx) exposta em `http://localhost:9923` (`docker-compose.yml:41`)
- MySQL exposto em `localhost:9922` (`docker-compose.yml:63`)
- Redis exposto em `localhost:9924` (`docker-compose.yml:76`)

## Configurar o .env
1. Copie o exemplo:
   - `cp Application/.env.example Application/.env`
2. Ajuste os valores conforme seu ambiente:
   - `APP_NAME`, `APP_ENV`, `APP_DEBUG`, `APP_URL`
   - `APP_KEY` (gerado via comando abaixo)
   - Banco: `DB_CONNECTION=mysql`, `DB_HOST=mysql-api-backend`, `DB_PORT=3306`, `DB_DATABASE=desafio-super-backend`, `DB_USERNAME=root`, `DB_PASSWORD`
   - Segurança: `SECURITY_KEY` (usado como header `security` nas rotas protegidas)
   - Octane: `OCTANE_SERVER=swoole`
3. Nunca versionar segredos. Mantenha apenas o `.env.example` com valores fictícios.

Observação: o arquivo de exemplo já vem compatível com Docker para MySQL (`Application/.env.example:78–93`).

## Subir com Docker
1. Build e subida:
   - `docker compose up -d --build`
2. Gerar chave da aplicação:
   - `docker compose exec api-backend php artisan key:generate`
3. Rodar migrações (o supervisor já tenta executar, mas você pode rodar manualmente):
   - `docker compose exec api-backend php artisan migrate`
4. Se desejar popular o banco com dados de exemplo:
   - `docker compose exec api-backend php artisan db:seed`

O container `api-backend` inicia o Octane (Swoole) e `schedule:work`/`horizon` via supervisor (`docker/supervisor/supervisord.conf:20–47`). O Nginx no container `web` faz proxy para o Octane (`docker/nginx/conf.d/app.conf:2–4`, `:51–60`).

## Acessar a API e a Documentação
- Base da API: `http://localhost:9923`
- Swagger UI: `http://localhost:9923/api/documentation` (`Application/config/l5-swagger.php:11–20`)
  - Se desejar na verão OpenAPI e Swagger UI separados, acesse: `http://localhost:9923/api/docs`

## Conexão ao Banco (local)
- String JDBC (exemplo):
  - `jdbc:mysql://localhost:9922/desafio-super-backend?allowPublicKeyRetrieval=true&useSSL=false` (`docker-compose.yml:68`)

## Comandos úteis
- Ver logs de um serviço: `docker compose logs -f api-backend`
- Executar comandos Artisan: `docker compose exec api-backend php artisan <comando>`
- Rodar testes: `docker compose exec api-backend php artisan test`
- Parar serviços: `docker compose down`
- Limpar volumes (cuidado: remove dados): `docker compose down -v`

## Variáveis de Ambiente Principais
- `APP_NAME`, `APP_ENV`, `APP_DEBUG`, `APP_URL`
- `APP_KEY` (gerado por `php artisan key:generate`)
- Banco: `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`
- Cache/Fila: `CACHE_STORE`, `QUEUE_CONNECTION`
- Redis (opcional): `REDIS_CLIENT`, `REDIS_HOST`, `REDIS_PORT`
- Segurança: `SECURITY_KEY` (header `security` nas rotas: veja o Swagger)
- Octane: `OCTANE_SERVER`

## Notas de Segurança
- Nunca commit de senhas, chaves ou tokens reais
- Use apenas o `.env.example` como referência com valores fictícios

## Bibliotecas Integradas (Composer)
- Plataforma: `php` (^8.2)

### Produção
- `laravel/framework` (^12.0)
- `laravel/octane` (^2.12)
- `laravel/horizon` (^5.40)
- `laravel/sanctum` (^4.0)
- `darkaonline/l5-swagger` (^9.0)
- `laravel/tinker` (^2.10.1)
- `yajra/laravel-oci8` (*)

### Desenvolvimento
- `phpunit/phpunit` (^11.5.3)
- `nunomaduro/collision` (^8.6)
- `mockery/mockery` (^1.6)
- `fakerphp/faker` (^1.23)
- `laravel/pint` (^1.13)
- `laravel/pail` (^1.2.2)
- `laravel/sail` (^1.41)

As versões listadas refletem as restrições definidas em `Application/composer.json`.
