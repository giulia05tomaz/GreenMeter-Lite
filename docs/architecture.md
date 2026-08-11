# Arquitetura e decisões

## Componentes

O frontend é uma SPA React que chama uma API Laravel. O token Sanctum permanece somente em memória. A API valida autenticação, filtros e arquivos antes de acessar o MySQL. Leituras e registros de importação pertencem a um usuário; fatores de emissão são dados de referência compartilhados.

## Identidades

- **Conta comum:** criada em `/api/auth/register`, pode consultar e importar dados próprios.
- **Conta demo:** criada pelo seeder com `is_demo=true`, recebe token apenas por `/api/auth/demo` e não pode executar mutações.
- **Conta provisionada por ambiente:** opcional para administração local, sem credenciais no código.

O cliente nunca decide se uma conta é demo: essa propriedade vem do banco e o bloqueio efetivo ocorre no backend.

## Fluxo de importação

1. Frontend e API rejeitam arquivo fora de `.csv` ou acima de 2 MB.
2. O importador confere o cabeçalho e valida cada linha.
3. Nenhuma linha é gravada antes de o arquivo completo ser válido.
4. As leituras são inseridas em lotes dentro de uma transação.
5. A API grava um `import_record` com arquivo, quantidade, período e status.
6. Leituras e auditoria recebem o `user_id` autenticado.

## Decisões de segurança

- **Token em memória:** reduz exposição por persistência no navegador; recarregar exige novo login.
- **Cadastro mínimo:** aceita apenas nome, e-mail e senha; campos de privilégio são ignorados.
- **Demo somente leitura no servidor:** esconder botões melhora a UX, mas o middleware é a barreira de segurança.
- **Importação atômica:** impede dashboards parcialmente atualizados.
- **Escopo por usuário:** evita consultas cruzadas entre contas.
- **Credenciais via ambiente:** nenhum segredo é versionado.

## Operação

- `/up`: liveness do framework.
- `/api/health/readiness`: verifica se a aplicação acessa o banco.
- `X-Request-ID`: correlaciona resposta e logs, reutilizando um identificador válido do cliente ou gerando um UUID.
- `/api/docs`: expõe o arquivo OpenAPI versionado.

## Cálculos

`CO₂e estimado = energia em kWh × fator de emissão em tCO₂e/kWh`.

Um pico ocorre quando o total de um dia é maior que 130% da média diária da sua semana ISO dentro do período consultado. A API retorna média e percentual excedente para tornar a regra auditável.

## Limites de produção

Antes de uso real, o produto ainda exige verificação e recuperação de e-mail, política de retenção, backups testados, observabilidade centralizada, HTTPS obrigatório e fonte/versionamento do fator de emissão.
