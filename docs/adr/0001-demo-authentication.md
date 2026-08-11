# ADR 0001: autenticação da demonstração no backend

- Status: aceito
- Data: 2026-08-11

## Contexto

O portfólio precisa ser explorável sem publicar uma senha reutilizável. Uma conta demonstrativa também não deve alterar os dados que outras pessoas verão.

## Decisão

`POST /api/auth/demo`, protegido por rate limit, localiza a conta marcada com `is_demo=true` e emite um token Sanctum válido por quatro horas. O seeder cria essa conta com senha aleatória. O middleware `demo.readonly` bloqueia mutações com HTTP 403; o frontend apenas reflete essa restrição.

## Consequências

- Não há senha demo no README, código ou interface.
- A autorização não depende de JavaScript.
- Toda nova rota de mutação precisa adotar o middleware.
- Dados demonstrativos devem ser determinísticos e não sensíveis.
