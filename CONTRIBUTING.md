# Como contribuir

1. Crie uma branch a partir de `main`.
2. Não versione `.env`, tokens, senhas, bancos ou arquivos de usuários.
3. Mantenha autorização e isolamento por `user_id` em novas consultas.
4. Proteja novas mutações com autenticação e bloqueio da conta demo.
5. Atualize testes e documentação quando mudar um contrato da API.

## Verificação local

```bash
cd backend && composer check
cd frontend && npm run check
```

Commits devem ser pequenos e descrever o motivo da mudança. Pull requests devem incluir resumo, como testar, impacto de segurança e capturas quando houver mudança visual.
