# Plano de Implementação — Módulo de Despesas e Comparativo Financeiro (Entradas vs. Saídas)

**Projeto:** GM FITNESS  
**Stack:** Laravel 13 + Filament v5 / PHP 8.4 + SQLite/MySQL  
**Objetivo:** Permitir o lançamento de todas as despesas e contas da academia (luz, água, aluguel, folha de funcionários, empréstimos, manutenção, etc.) por mês de competência e fornecer um comparativo financeiro em tempo real (DRE / Fluxo de Caixa / Lucro Líquido) confrontando o faturamento das mensalidades com os custos operacionais.

---

## 1. Arquitetura e Modelagem do Banco de Dados

### 1.1. Tabela `expense_categories` (Categorias de Despesas)
Permite categorizar e agrupar os custos da academia com cores e ícones para relatórios visuais.

| Campo | Tipo | Descrição |
|---|---|---|
| `id` | `bigint PK` | Identificador único |
| `name` | `string(100)` | Nome da categoria (ex: "Utilidades (Água/Luz)", "Folha Salarial", "Empréstimos & Financiamentos", "Aluguel", "Equipamentos & Manutenção", "Marketing") |
| `slug` | `string(100) unique` | Identificador textual amigável |
| `color` | `string(30) nullable` | Cor visual para badges e gráficos (ex: 'danger', 'warning', 'info', '#f59e0b') |
| `icon` | `string(50) nullable` | Ícone Heroicon representativo (ex: 'heroicon-o-bolt', 'heroicon-o-users', 'heroicon-o-banknotes') |
| `is_recurring` | `boolean` | Indica se é uma categoria de custo fixo mensal recorrente (default: `true`) |
| `active` | `boolean` | Categoria ativa no sistema (default: `true`) |
| `timestamps` | | `created_at`, `updated_at` |

---

### 1.2. Tabela `expenses` (Contas a Pagar / Despesas Mensais)
Armazena cada lançamento de conta ou custo por mês de competência e vencimento.

| Campo | Tipo | Descrição |
|---|---|---|
| `id` | `bigint PK` | Identificador único |
| `expense_category_id` | `FK → expense_categories` | Categoria vinculada à despesa |
| `title` | `string(150)` | Descrição da despesa (ex: "Conta de Energia Elétrica - CEMIG", "Salário Professor Carlos", "Parcela 12/36 Empréstimo Caixa") |
| `reference_month` | `date` | Mês de competência do gasto (ex: `2026-08-01`) |
| `amount` | `decimal(10,2)` | Valor previsto/cobrado da despesa (R$) |
| `paid_amount` | `decimal(10,2) nullable` | Valor efetivamente pago (R$) |
| `due_date` | `date` | Data de vencimento da conta |
| `paid_at` | `date nullable` | Data em que o pagamento foi realizado |
| `payment_method` | `string(50) nullable` | Forma de pagamento (`pix`, `dinheiro`, `ted_transferencia`, `cartao_credito`, `cartao_debito`, `boleto`) |
| `status` | `enum` | Status do pagamento: `'pendente'`, `'pago'`, `'atrasado'`, `'cancelado'` (default: `'pendente'`) |
| `is_recurring` | `boolean` | Se deve ser gerada automaticamente nos próximos meses (default: `false`) |
| `receipt_path` | `string(255) nullable` | Upload de comprovante de pagamento / boleto (PDF ou imagem) |
| `beneficiary` | `string(150) nullable` | Favorecido / Fornecedor / Funcionário (ex: "Companhia Elétrica", "Personal Trainer Silva") |
| `notes` | `text nullable` | Observações adicionais |
| `timestamps` | | `created_at`, `updated_at` |
| `deleted_at` | `timestamp nullable` | Soft delete para auditoria e segurança |

---

## 2. Models Eloquent e Relacionamentos

### `ExpenseCategory`
- `expenses()`: `HasMany(Expense::class)`
- Métodos auxiliares de agregação de despesas por competência.

### `Expense`
- `category()`: `BelongsTo(ExpenseCategory::class, 'expense_category_id')`
- `casts`:
  - `reference_month` => `date`
  - `due_date` => `date`
  - `paid_at` => `date`
  - `amount` => `decimal:2`
  - `paid_amount` => `decimal:2`
  - `is_recurring` => `boolean`
- `Scopes`:
  - `scopePending()`, `scopePaid()`, `scopeOverdue()`
  - `scopeForMonth($date)`: filtra pela competência ou vencimento

---

## 3. Painel Administrativo Filament

### 3.1. `ExpenseCategoryResource` (Gestão de Categorias)
- **Formulário**: Nome, Cor, Ícone, Custo Fixo Recorrente, Status Ativo.
- **Tabela**: Badge colorida, Ícone, Total de Despesas Vinculadas, Ações Rápidas.

### 3.2. `ExpenseResource` (Contas a Pagar / Despesas)
- **Navegação**: Grupo `Financeiro` (Ícone `heroicon-o-receipt-percent`).
- **Abas Superiores com Contadores (Tabs)**:
  - `Todas` (Total)
  - `Pendentes` (com badge de quantidade e total em R$)
  - `Pagas` (com badge verde)
  - `Vencidas` (com badge vermelho de alerta)
- **Tabela de Despesas**:
  - Título / Descrição
  - Categoria (badge personalizada com cor)
  - Competência (`m/Y`)
  - Vencimento (`d/m/Y`)
  - Valor Previsto vs Valor Pago
  - Status (`pago`, `pendente`, `atrasado`, `cancelado`)
  - Comprovante (visualização/download direto)
- **Ação Rápida de Dar Baixa / Registrar Pagamento (`markAsPaid`)**:
  - Modal intuitivo com: Tipo de Pagamento, Valor Pago, Data do Pagamento e Upload opcional do Comprovante.

---

## 4. Comparativo Financeiro e Relatórios (Entradas vs. Saídas)

### 4.1. Página de Relatório Financeiro e DRE (`FinancialReportPage`)
Página dedicada no grupo `Financeiro` contendo seletor de mês de competência ou período anual:

1. **Cards de Resumo (Stats Overview)**:
   - 🟢 **Total de Entradas (Mensalidades Recebidas)**: Soma de `payments.paid_amount` (ou total faturado).
   - 🔴 **Total de Saídas (Despesas Pagas & A Pagar)**: Soma de `expenses.paid_amount` / `amount`.
   - 💰 **Resultado Líquido / Lucro Operacional**: `Total Entradas - Total Saídas` (com indicador verde/positivo ou vermelho/negativo).
   - 📊 **Margem Operacional (%)**: Percentual de lucro sobre o faturamento.
   - ⚠️ **Inadimplência de Alunos vs. Contas Atrasadas**.

2. **Gráficos Visuais Comparativos**:
   - 📈 **Gráfico Histórico Mensal (Bar / Line Chart)**: Barras comparando Entradas vs. Saídas mês a mês nos últimos 6 a 12 meses.
   - 🍩 **Gráfico de Distribuição de Despesas (Doughnut/Pie Chart)**: Percentual de gastos por categoria (ex: 40% Folha, 25% Aluguel, 15% Energia/Água, 10% Empréstimos, 10% Manutenção).

3. **Tabela Comparativa Detalhada**:
   - Tabela consolidada com exportação ou visualização analítica item a item.

---

## 5. Rotinas Automáticas e Automações Financeiras

### Atualização do `PaymentService` / `FinancialService`:
1. **Geração de Despesas Recorrentes**:
   - Na virada do mês (ou botão manual na tela de Rotinas), replica as despesas fixas marcadas como recorrentes (ex: Aluguel, Sistema, Internet, Salários base) para a nova competência.
2. **Atualização Diária de Despesas Vencidas**:
   - Atualiza o status de despesas pendentes com vencimento anterior a hoje para `'atrasado'`.
3. **Alertas no Dashboard**:
   - Alerta a administração sobre contas a vencer hoje ou vencidas.

---

## 6. Ordem de Execução Sugerida

1. **Fase 1 (Banco de Dados)**:
   - Migration `create_expense_categories_table`
   - Migration `create_expenses_table`
   - Seeders iniciais com categorias comuns de academias (Energia, Água, Folha, Aluguel, Empréstimos, Equipamentos).
2. **Fase 2 (Models & Regras de Negócio)**:
   - Models `ExpenseCategory` e `Expense` com relacionamentos, casts e scopes.
   - `FinancialService` para cálculos de fluxo de caixa e DRE.
3. **Fase 3 (Telas Filament)**:
   - `ExpenseCategoryResource`
   - `ExpenseResource` com abas, badges de contagem e modal de baixa com upload de comprovante.
4. **Fase 4 (Dashboards & Relatório Comparativo)**:
   - Página `FinancialReport` no menu Financeiro com filtros por mês/ano.
   - Widgets de Gráficos (Entradas vs Saídas últimos 12 meses, Distribuição por Categoria).
5. **Fase 5 (Validação e Testes)**:
   - Testes de lançamentos, cálculo de saldo líquido e integração com pagamentos de alunos.
