# Guia de entrevista — GreenMeter Lite

## Resumo em 30 segundos

GreenMeter Lite é um MVP full stack que importa leituras de energia por CSV e gera KPIs, série diária e alertas de pico. Usei React e TypeScript no frontend e Laravel com Sanctum no backend. A parte mais importante foi tornar a importação segura e atômica, além de isolar os dados por usuário.

## Decisões que devo conseguir explicar

### Por que CSV?

É uma integração simples para um MVP e permite demonstrar validação de entrada, transações e processamento em lote. Em produção, conectores automáticos e filas seriam opções naturais.

### Por que token em memória?

Evita persistir o token em `localStorage`, reduzindo o impacto de certos cenários de XSS. O trade-off é perder a sessão ao atualizar a página. Se frontend e API compartilhassem domínio, cookies HttpOnly com proteção CSRF seriam uma alternativa.

### Como a importação evita dados parciais?

O arquivo inteiro é validado antes da gravação. Depois, os registros são inseridos em chunks dentro de uma transação. Se houver erro, nenhuma leitura fica salva.

### Como uma conta é impedida de ver dados de outra?

A tabela `readings` possui `user_id`, preenchido a partir da conta autenticada. Todas as queries do dashboard e dos alertas incluem esse filtro.

### O cálculo de CO₂e é confiável?

O cálculo é reproduzível, mas o fator no seed é demonstrativo. Para uso real, eu versionaria o fator com fonte, região e período de validade.

## Pontos fortes demonstrados

- API REST e autenticação.
- Modelagem relacional e índices.
- Validação de arquivo e regras de domínio.
- Transação e inserção em lotes.
- Testes de autenticação, isolamento, importação e dashboard.
- CI e ambiente Docker.
- Documentação de limites, não apenas de funcionalidades.

## Próximas evoluções

1. Documentação OpenAPI.
2. Histórico de importações e idempotência.
3. Processamento assíncrono para arquivos grandes.
4. Observabilidade e métricas.
5. Testes end-to-end e deploy com HTTPS.
