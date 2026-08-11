# Deploy na Railway

O GreenMeter é publicado como três serviços no mesmo projeto Railway:

- `frontend`: raiz `/frontend`, config `/frontend/railway.json`, Dockerfile próprio e domínio público;
- `backend`: raiz `/backend`, config `/backend/railway.json`, FrankenPHP, migrations automáticas e domínio público;
- `MySQL`: template oficial da Railway, acessível pelo backend na rede privada.

## Variáveis do backend

```dotenv
APP_NAME=GreenMeter Lite
APP_ENV=production
APP_DEBUG=false
APP_KEY=<gerada para o ambiente>
APP_URL=https://<dominio-backend>
LOG_CHANNEL=stderr
LOG_LEVEL=info
DB_CONNECTION=mysql
DB_HOST=${{MySQL.MYSQLHOST}}
DB_PORT=${{MySQL.MYSQLPORT}}
DB_DATABASE=${{MySQL.MYSQLDATABASE}}
DB_USERNAME=${{MySQL.MYSQLUSER}}
DB_PASSWORD=${{MySQL.MYSQLPASSWORD}}
CORS_ALLOWED_ORIGINS=https://<dominio-frontend>
DEMO_ADMIN_EMAIL=<definido no ambiente>
DEMO_ADMIN_PASSWORD=<definido no ambiente>
```

`APP_KEY`, credenciais e senhas devem existir somente no gerenciador de variáveis da Railway.

## Variável de build do frontend

```dotenv
VITE_API_BASE_URL=https://<dominio-backend>/api
```

O frontend precisa de novo deploy quando o endereço da API mudar, porque variáveis `VITE_*` são incorporadas no build.

## Ordem de provisionamento

1. Criar um projeto vazio e adicionar MySQL.
2. Criar o backend a partir do repositório, com raiz `/backend` e config `/backend/railway.json`.
3. Gerar o domínio do backend e configurar suas variáveis.
4. Criar o frontend a partir do mesmo repositório, com raiz `/frontend` e config `/frontend/railway.json`.
5. Gerar o domínio do frontend e configurar `VITE_API_BASE_URL`.
6. Atualizar `CORS_ALLOWED_ORIGINS` no backend e redeployar.
7. Validar `/up`, login, importação CSV, dashboard e logout.

O site e a API podem usar Serverless no plano gratuito, aceitando cold start. O MySQL permanece ativo para preservar conexões e dados.
