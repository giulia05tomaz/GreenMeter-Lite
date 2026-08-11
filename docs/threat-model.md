# Modelo de ameaças

## Escopo e ativos

Este documento cobre a SPA, a API Laravel, o banco MySQL e a importação de CSV. Os ativos protegidos são credenciais, tokens, dados de consumo, histórico de importações e disponibilidade da API.

## Fronteiras de confiança

1. Navegador para API: toda entrada é não confiável.
2. API para banco: consultas precisam preservar o escopo do usuário autenticado.
3. Arquivo CSV para importador: nome, tipo, tamanho e conteúdo são não confiáveis.
4. Plataforma de deploy para aplicação: segredos devem chegar apenas por variáveis de ambiente.

## Ameaças e controles

| Ameaça | Controle atual | Risco residual |
| --- | --- | --- |
| Força bruta em autenticação | Rate limit em cadastro, login e demo | Ataques distribuídos exigem proteção da plataforma |
| Elevação de privilégio no cadastro | Campos permitidos explícitos e `is_demo=false` no servidor | Ainda não há papéis administrativos |
| Escrita pela conta demo | Middleware no backend e interface desabilitada | Novas rotas de mutação precisam incluir a proteção |
| Acesso a dados de outra conta | Consultas por `user_id` autenticado | Revisão obrigatória em cada nova consulta |
| CSV malformado ou excessivo | Limite de 2 MB/10.000 linhas, validação e transação | Processamento síncrono não escala para arquivos grandes |
| Token roubado por XSS | Token apenas em memória, sem `localStorage` | CSP e revisão contínua de dependências são recomendadas |
| Vazamento em logs | Não registrar senhas, tokens nem conteúdo completo do CSV | Infra deve restringir acesso e retenção dos logs |
| Segredos no repositório | `.env` ignorado e exemplos sem valores reais | Exige varredura no CI e rotação em caso de incidente |
| Indisponibilidade do banco | Readiness separado de liveness | Backups e alertas dependem da plataforma |

## Privacidade e retenção

Os dados são demonstrativos nesta versão. Um uso real precisa definir base legal, prazo de retenção, exportação, exclusão de conta e eliminação dos dados associados.

## Checklist para novas rotas

- Validar e normalizar entrada no servidor.
- Exigir Sanctum quando houver dados de usuário.
- Aplicar `demo.readonly` a toda mutação autenticada.
- Filtrar consultas pelo usuário autenticado.
- Não expor segredos ou dados pessoais em logs e erros.
- Adicionar teste de autorização e isolamento.
