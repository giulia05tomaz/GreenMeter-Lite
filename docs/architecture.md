# Arquitetura e decisões

## Componentes

O frontend é uma SPA React. Ele recebe um token Sanctum após o login e o mantém somente em memória. A API Laravel valida autenticação, filtros e arquivos antes de acessar o MySQL. Leituras pertencem a um usuário, enquanto fatores de emissão são dados de referência compartilhados.

## Fluxo de importação

1. A API rejeita arquivos fora do tipo ou acima de 2 MB.
2. O importador confere o cabeçalho e valida cada linha.
3. Nenhuma linha é gravada enquanto o arquivo completo não for válido.
4. As leituras são inseridas em lotes dentro de uma transação.
5. Toda leitura recebe o usuário autenticado e a origem `csv`.

## Decisões de segurança

- **Token em memória:** reduz a exposição decorrente de persistência no navegador, com o trade-off de exigir novo login após recarregar a página.
- **Sem cadastro público:** reduz a superfície do MVP; provisionamento e recuperação de conta ficam fora do escopo atual.
- **Importação atômica:** evita dashboards parcialmente atualizados quando uma linha do CSV é inválida.
- **Escopo por usuário:** impede que uma conta consulte leituras de outra.
- **Credenciais via ambiente:** nenhuma senha demo é versionada.

## Cálculos

`CO₂e estimado = energia em kWh × fator de emissão em tCO₂e/kWh`.

Um pico ocorre quando o total de um dia é maior que 130% da média diária da sua semana ISO, considerando o período consultado. Trata-se de uma heurística explicável e deliberadamente simples.

## Limites de produção

Antes de uso real, o projeto exige fonte e vigência do fator de emissão, política de retenção, trilha de auditoria, recuperação de senha, observabilidade, backups do banco, HTTPS e revisão do modelo de autenticação conforme a topologia de deploy.
