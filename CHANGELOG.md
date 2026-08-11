# Changelog

Todas as mudanças relevantes deste projeto são documentadas aqui.

## [1.0.0] - 2026-08-11

### Adicionado

- Cadastro público seguro com login automático.
- Demonstração em um clique e somente leitura.
- Filtros por período, tabela acessível e detalhes de picos.
- Validação de CSV no cliente e histórico de importações.
- OpenAPI, readiness, request ID, modelo de ameaças e ADRs.
- Testes de cadastro, demonstração, auditoria e endpoints operacionais.

### Segurança

- Senha da conta demo gerada aleatoriamente pelo seeder.
- Bloqueio de mutações demo aplicado no backend.
- Rate limit nas rotas públicas de autenticação.
