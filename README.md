# GM Fitness — Sistema de Gestão de Academia

Sistema completo para gestão operacional e financeira de academias, construído com foco em produtividade administrativa, controle de lotação de horários e automação de mensalidades.

---

## 🛠️ Stack Tecnológica

- **Framework:** Laravel 13
- **Painel Administrativo:** Filament v5
- **Linguagem:** PHP 8.3+ (Recomendado 8.4)
- **Banco de Dados:** SQLite (padrão local) ou MySQL
- **Frontend / Assets:** Vite + Tailwind CSS (integrado ao Filament)
- **Testes:** Pest PHP v5

---

## 🚀 Como Rodar o Projeto

### Pré-requisitos
- PHP >= 8.3 com extensões (`sqlite3`, `curl`, `mbstring`, `pdo_sqlite`, `intl`)
- Composer
- Node.js >= 20 & NPM

---

### Opção 1: Rodando com Laravel Herd (Recomendado no Mac/Windows)

1. **Vincular repositório:**
   - Abra o **Laravel Herd**.
   - Arraste a pasta `gym-management` para o Herd ou execute no terminal dentro da pasta:
     ```bash
     herd link gym-management
     ```
   - O site responderá automaticamente em: `http://gym-management.test`

2. **Configurar variáveis e banco:**
   ```bash
   cp .env.example .env
   composer install
   php artisan key:generate
   touch database/database.sqlite
   php artisan migrate --seed
   ```

3. **Compilar assets:**
   ```bash
   npm install
   npm run build # ou npm run dev se for customizar estilos
   ```

4. **Acessar painel:**
   - URL: `http://gym-management.test/admin`
   - **Login:** `dev@test.com`
   - **Senha:** `password`

---

### Opção 2: Rodando Manualmente (CLI / Terminal)

1. **Instalar dependências PHP e preparar `.env`:**
   ```bash
   cp .env.example .env
   composer install
   php artisan key:generate
   ```

2. **Configurar base SQLite e rodar migrações com dados de teste:**
   ```bash
   touch database/database.sqlite
   php artisan migrate --seed
   ```

3. **Instalar dependências de frontend:**
   ```bash
   npm install
   npm run build
   ```

4. **Iniciar servidores de desenvolvimento:**

   *Opção A — Script integrado (Servidor + Fila + Logs + Vite simultâneos):*
   ```bash
   composer run dev
   ```

   *Opção B — Servidor embutido simples:*
   ```bash
   php artisan serve
   ```

5. **Acessar painel:**
   - URL: `http://127.0.0.1:8000/admin`
   - **Login:** `dev@test.com`
   - **Senha:** `password`

---

## ⚙️ Comandos e Rotinas do Sistema

- **Rotina diária de checagem financeira e notificações:**
  ```bash
  php artisan app:check-payments-and-notify
  ```
  *(Identifica inadimplentes, atualiza status para `atrasado`, cria alertas internos e gera cobranças futuras).*

- **Executar suíte de testes:**
  ```bash
  php artisan test
  ```

---

## 📋 Checklist de Funcionalidades

### ✅ Concluído (Pronto no Sistema)
- [x] **Modelagem & Banco de Dados:**
  - Migrações completas com soft deletes (`students`, `plans`, `time_slots`, `schedule_closures`, `payments`).
  - Tabela pivot `student_time_slot` com restrições de integridade.
  - Relacionamentos Eloquent, casts e scopes implementados.
- [x] **Gestão de Planos (`PlanResource`):**
  - Cadastro de planos, valores em R$, duração e suporte a soft deletes.
- [x] **Grade Semanal e Vagas (`TimeSlotResource`):**
  - Horários por dia da semana com capacidade máxima.
  - Badges de ocupação em tempo real (vagas preenchidas vs limite).
- [x] **Fechamento de Grade (`ScheduleClosureResource`):**
  - Bloqueios pontuais (feriados/reformas) ou recorrentes por dia da semana sem desmontar a grade padrão.
- [x] **Gestão de Alunos (`StudentResource`):**
  - Cadastro completo com foto, CPF, dia preferencial de pagamento (1–31).
  - Vínculo direto de horários com bloqueio de seleção quando a turma está lotada.
  - RelationManagers para histórico financeiro e horários matriculados.
- [x] **Controle Financeiro de Alunos (`PaymentResource`):**
  - Mensalidades por competência com ajuste de vencimento para meses menores (ex: dia 31 em fevereiro).
  - Filtros rápidos por status (`pendente`, `pago`, `atrasado`, `cancelado`).
  - Ação rápida de registrar pagamento / dar baixa com modal.
- [x] **Automações e Notificações:**
  - Serviço `PaymentService` com lógica centralizada.
  - Comando `app:check-payments-and-notify` agendado.
  - Página administrativa de rotinas financeiras manuais (`FinancialRoutines`).
  - Alertas no painel do Filament para administração sobre inadimplência.
- [x] **Dashboard:**
  - Widgets de métricas da academia, fluxo de caixa, pagamentos recentes e grade do dia.
- [x] **Testes Automatizados:**
  - Testes Pest cobrindo capacidade de turmas, regras de vencimento e comandos.

---

### ⏳ Pendente (Backlog / Próximos Passos)
- [ ] **Módulo de Despesas Operacionais (Contas a Pagar):**
  - Migrações e models de categorias de despesas (`expense_categories`) e despesas (`expenses`).
  - Cadastro de contas fixas e variáveis (aluguel, água, energia, salários, empréstimos).
  - Anexo de comprovantes de pagamento (PDF/imagens).
- [ ] **Comparativo Financeiro & DRE (Entradas vs. Saídas):**
  - Página dedicada com fluxo de caixa consolidado e lucro líquido do mês.
  - Gráficos comparativos (Entradas x Saídas últimos 12 meses).
  - Gráfico de distribuição de custos por categoria (pizza/rosca).
- [ ] **Automação de Despesas Recorrentes:**
  - Replicação automática de contas fixas a cada virada de mês.
- [ ] **Integração WhatsApp:**
  - Envio automático de lembretes de cobrança e avisos de vencimento para alunos.
- [ ] **Controle de Presença / Check-in:**
  - Registro de frequência diária de alunos por horário ou integração com catraca.
