# Regra de Execução de Seeders

- **Permissão Prévia Obrigatória**: NUNCA executar comandos de seed (`php artisan db:seed`, `php artisan migrate:fresh --seed`, etc.) automaticamente sem autorização prévia.
- **Fluxo**:
  1. Apresentar o seeder criado/alterado e explicar quais dados e quantidade serão inseridos no banco.
  2. Solicitar autorização explícita do usuário antes de executar o seed.
  3. Somente rodar o comando após confirmação positiva do usuário.
