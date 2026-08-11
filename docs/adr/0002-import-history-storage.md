# ADR 0002: persistência do histórico de importações

- Status: aceito
- Data: 2026-08-11

## Contexto

Somente armazenar leituras não permite responder qual arquivo originou uma carga, quando ela ocorreu ou qual período foi coberto.

## Decisão

Uma tabela `import_records` armazena `user_id`, nome do arquivo, total de linhas, primeira e última leitura e status. O registro é criado após a importação atômica bem-sucedida e consultado por `/api/imports` com escopo do usuário.

## Consequências

- O dashboard ganha uma trilha auditável sem guardar o arquivo original.
- Nome de arquivo deve ser tratado como dado não confiável na apresentação.
- Falhas anteriores à conclusão não são persistidas nesta versão; auditoria completa de tentativas é evolução futura.
