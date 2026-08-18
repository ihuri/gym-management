# Plano de Desenvolvimento — Sistema de Gestão de Academia

**Stack:** Laravel 13 + Filament v5

---

## 1. Visão Geral do Escopo

Módulos principais do sistema:

- **Gestão de alunos**: cadastro completo, status, vínculo com plano, dia de vencimento customizado e soft deletes.
- **Grade de horários semanais**: horários por dia da semana com limite de capacidade, separados por blocos visuais.
- **Alocação de horários (pela recepção)**: atendente insere os horários do aluno, com desativação automática de opções lotadas no select.
- **Fechamento de dias/horários específicos**: bloqueios pontuais (feriados, reformas) sem alterar a grade padrão.
- **Controle financeiro mensal**: mensalidades geradas por competência com ajuste inteligente de dias para meses menores (ex: dia 31 em fevereiro).
- **Notificações diárias para administração**: alertas internos no Filament com inadimplentes do dia.
- **Roadmap futuro**: notificações automáticas para alunos via WhatsApp.

> Observação: o controle de turmas é feito diretamente por **horários semanais fixos** disponíveis para matrícula pela recepção.

---

## 2. Modelagem do Banco de Dados

### `users`
Usuários do painel Filament (administração e recepção).

| Campo | Tipo | Descrição |
|---|---|---|
| id | bigint PK | Identificador único |
| name | string | Nome do usuário |
| email | string unique | E-mail de acesso |
| password | string | Senha hash |
| timestamps | | `created_at`, `updated_at` |

---

### `students` (alunos)

| Campo | Tipo | Descrição |
|---|---|---|
| id | bigint PK | Identificador único |
| name | string | Nome completo |
| cpf | string unique | CPF do aluno |
| email | string nullable | E-mail de contato |
| phone | string | Telefone/WhatsApp |
| birth_date | date | Data de nascimento |
| address | string nullable | Endereço residencial |
| photo_path | string nullable | Foto do perfil |
| status | enum (ativo, inativo, suspenso) | Status da matrícula |
| enrollment_date | date | Data de início na academia |
| plan_id | FK → plans | Plano atual contratado |
| payment_day | tinyint (1–31) | Dia de vencimento preferencial |
| timestamps | | `created_at`, `updated_at` |
| deleted_at | timestamp nullable | Soft Delete |

---

### `plans` (planos)

| Campo | Tipo | Descrição |
|---|---|---|
| id | bigint PK | Identificador único |
| name | string | Ex: Mensal 2x/sem, Mensal Livre, Trimestral |
| price | decimal(10,2) | Valor da mensalidade |
| duration_months | tinyint | Duração em meses (1, 3, 6, 12) |
| description | text nullable | Detalhes do plano |
| active | boolean | Se está disponível para novas matrículas |
| timestamps | | `created_at`, `updated_at` |
| deleted_at | timestamp nullable | Soft Delete |

---

### `time_slots` (horários semanais disponíveis)

| Campo | Tipo | Descrição |
|---|---|---|
| id | bigint PK | Identificador único |
| day_of_week | tinyint (0=domingo ... 6=sábado) | Dia da semana |
| start_time | time | Horário de início (ex: 08:00) |
| end_time | time | Horário de término (ex: 09:00) |
| capacity | integer | Capacidade máxima de alunos no horário |
| active | boolean | Se o horário está ativo na grade |
| timestamps | | `created_at`, `updated_at` |

---

### `schedule_closures` (fechamento de dias/horários específicos ou recorrentes)

| Campo | Tipo | Descrição |
|---|---|---|
| id | bigint PK | Identificador único |
| type | enum | `'data_especifica'` (pontual) ou `'recorrente'` (dia da semana fixo) |
| date | date nullable | Data fechada (quando `type = 'data_especifica'`) |
| day_of_week | tinyint nullable | Dia recorrente 0–6 (quando `type = 'recorrente'`, ex: todos os sábados) |
| time_slot_id | FK → time_slots, **nullable** | Se `null` = dia inteiro fechado; se preenchido = apenas o horário |
| reason | string nullable | Motivo (ex: "Sem funcionamento aos sábados", "Feriado") |
| timestamps | | `created_at`, `updated_at` |

---

### `student_time_slot` (vínculo aluno-horário — pivot)

| Campo | Tipo | Descrição |
|---|---|---|
| id | bigint PK | Identificador único |
| student_id | FK → students | Aluno matriculado |
| time_slot_id | FK → time_slots | Horário alocado |
| status | enum (ativo, cancelado) | Status do vínculo |
| enrolled_at | date | Data em que foi adicionado ao horário |
| timestamps | | `created_at`, `updated_at` |

*Constraint:* `UNIQUE(student_id, time_slot_id)` para vínculos ativos.

---

### `payments` (mensalidades)

| Campo | Tipo | Descrição |
|---|---|---|
| id | bigint PK | Identificador único |
| student_id | FK → students | Aluno pagador |
| plan_id | FK → plans | Plano de referência |
| reference_month | date | Competência (ex: 2026-08-01) |
| amount | decimal(10,2) | Valor cobrado |
| paid_amount | decimal(10,2) nullable | Valor efetivamente pago |
| due_date | date | Data de vencimento calculada |
| paid_at | date nullable | Data de pagamento |
| payment_method | enum (dinheiro, pix, cartao_credito, cartao_debito) nullable | Método |
| status | enum (pendente, pago, atrasado, cancelado) | Status financeiro |
| notes | text nullable | Observações da recepção |
| timestamps | | `created_at`, `updated_at` |
| deleted_at | timestamp nullable | Soft Delete |

---

## 3. Relacionamentos (Models)

- **Student** → `belongsTo(Plan)`, `belongsToMany(TimeSlot, 'student_time_slot')`, `hasMany(Payment)`
- **Plan** → `hasMany(Student)`
- **TimeSlot** → `belongsToMany(Student, 'student_time_slot')`, `hasMany(ScheduleClosure)`
- **ScheduleClosure** → `belongsTo(TimeSlot)` (nullable)
- **Payment** → `belongsTo(Student)`, `belongsTo(Plan)`

---

## 4. Regras de Negócio e Lógicas Centrais

1. **Alocação de Horários pela Recepção**:
   - O atendente vincula os horários do aluno diretamente no formulário/RelationManager.
   - O seletor de horários exibe o total de ocupação em tempo real (ex: `Segunda 07:00 - 08:00 [8/10 vagas]`).
   - Horários com `count(alunos ativos) >= capacity` ficam desabilitados no select para evitar superlotação.
   - O atendente tem flexibilidade para definir a quantidade de horários necessários.

2. **Cálculo Inteligente de Vencimento**:
   - Cada aluno possui seu `payment_day` individual (1 a 31).
   - Ao gerar a mensalidade para um mês específico, a data de vencimento é ajustada automaticamente para o último dia válido daquele mês se o mês tiver menos dias:
     `$dueDate = Carbon::create($year, $month, min($student->payment_day, Carbon::create($year, $month)->daysInMonth));`

3. **Rotina Diária e Notificações Administrativas**:
   - Comando agendado diário (`php artisan schedule:run`):
     1. Atualiza cobranças com `due_date < hoje` e `status = pendente` para `atrasado`.
     2. Dispara Notificação no Painel do Filament (Database Notification) para os administradores/recepção listando os alunos inadimplentes do dia.
     3. Gera as cobranças de competência futura para alunos ativos.

4. **Fechamento de Grade**:
   - Bloqueios pontuais em `schedule_closures` sinalizam na visualização diária se o dia todo ou determinado horário está suspenso, sem remover os alunos da grade semanal padrão.

5. **Roadmap de Comunicação**:
   - Estrutura preparada para envio futuro de lembretes e cobranças automáticas via WhatsApp API.

---

## 5. Estrutura no Filament v5

### Resources

- **StudentResource**: cadastro completo com foto, plano, `payment_day`, RelationManager de `Payments` e RelationManager de `TimeSlots` com badge de ocupação.
- **PlanResource**: cadastro de planos e valores com soft delete.
- **TimeSlotResource**: grade semanal dividida em blocos de horários, exibindo contagem de alunos matriculados e indicador visual de lotação.
- **ScheduleClosureResource**: gestão de feriados e manutenções com bloqueio total ou parcial de data.
- **PaymentResource**: visualização financeira, filtros por status/mês, ação rápida "Dar Baixa / Marcar Pago".

### Widgets do Dashboard

- **Inadimplência do Dia/Mês**: total a receber em atraso e lista de alunos inadimplentes.
- **Ocupação Semanal**: gráfico/tabela com ocupação por bloco de horário.
- **Vencimentos dos Próximos 7 Dias**: fluxo de caixa previsto.
- **Fechamentos Próximos**: alertas de feriados e manutenções agendadas.

---

## 6. Fases de Execução

### Fase 1 — Modelagem e Migrations
- [x] Criar migrations com Soft Deletes e constraints (`students`, `plans`, `time_slots`, `schedule_closures`, `student_time_slot`, `payments`).
- [x] Definir Models, Casts e Relacionamentos Eloquent.

### Fase 2 — Painel Filament v5 (CRUDs por Nível de Complexidade)

> **Estrutura Filament v5:** Recursos organizados em pastas modulares contendo `Resource.php`, subpasta `Schemas/` (formulários/infolists), subpasta `Tables/` (tabelas/filtros/ações) e subpasta `Pages/` (Livewire components).

- [x] **2.1 — `PlanResource`** *(Mais Simples)*
  - `app/Filament/Resources/Plans/` (`PlanResource.php`, `Schemas/PlanForm.php`, `Tables/PlansTable.php`, `Pages/`)
  - Gestão de planos, preços (R$), duração e suporte a Soft Deletes (`--soft-deletes`).
- [x] **2.2 — `ScheduleClosureResource`** *(Simples)*
  - `app/Filament/Resources/ScheduleClosures/` (`ScheduleClosureResource.php`, `Schemas/ScheduleClosureForm.php`, `Tables/ScheduleClosuresTable.php`, `Pages/`)
  - Fechamentos de data total ou horário pontual (feriados, reformas) com motivo.
- [x] **2.3 — `TimeSlotResource`** *(Médio)*
  - `app/Filament/Resources/TimeSlots/` (`TimeSlotResource.php`, `Schemas/TimeSlotForm.php`, `Tables/TimeSlotsTable.php`, `Pages/`)
  - Grade semanal com dia da semana, faixa de horário, capacidade e badges de ocupação em tempo real.
- [x] **2.4 — `PaymentResource`** *(Médio-Avançado)*
  - `app/Filament/Resources/Payments/` (`PaymentResource.php`, `Schemas/PaymentForm.php`, `Tables/PaymentsTable.php`, `Pages/`)
  - Gestão financeira, filtros por status/mês, Soft Deletes e Action customizada rápida "Dar Baixa / Registrar Pagamento".
- [x] **2.5 — `StudentResource`** *(Mais Complexo)*
  - `app/Filament/Resources/Students/` (`StudentResource.php`, `Schemas/StudentForm.php`, `Tables/StudentsTable.php`, `Pages/`, `RelationManagers/`)
  - Cadastro de alunos com foto, CPF, dia de vencimento (1-31) e Soft Deletes.
  - Seletor dinâmico de horários com contagem de vagas e bloqueio visual de horários lotados.
  - `RelationManagers`: Histórico de Pagamentos e Horários Matriculados.

### Fase 3 — Lógica Financeira e Automação
- [x] Implementar serviço de geração de mensalidade com cálculo de dias limites.
- [x] Criar comando de rotina diária (`app:check-payments-and-notify`).
- [x] Configurar notificações no Filament para avisar inadimplentes aos administradores.

### Fase 4 — Dashboard e Widgets
- [x] Desenvolver widgets de ocupação de horários e métricas financeiras.

### Fase 5 — Testes Automatizados
- [x] Testes Pest para regras de capacidade, cálculo de vencimento e rotinas de notificação.
