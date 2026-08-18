# Regra de Migrations e Reset de Banco de Dados

- **Permissão Prévia Obrigatória**: NUNCA executar `php artisan migrate:fresh`, `migrate:reset` ou `migrate:rollback` sem pedir e obter autorização prévia e explícita do usuário.
- **Inclusão Obrigatória de Seeds**: Sempre que o usuário autorizar um reset do banco via `migrate:fresh`, deve-se OBRIGATORIAMENTE executar junto com a flag `--seed` (`php artisan migrate:fresh --seed`), garantindo que o banco seja repovoado imediatamente com usuários, planos, grade de horários e dados essenciais.
