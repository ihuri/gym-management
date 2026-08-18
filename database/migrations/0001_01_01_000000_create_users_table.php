<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Executa a migration para criação das tabelas de autenticação (usuários, redefinição de senha e sessões).
     */
    public function up(): void
    {
        // Tabela de usuários do painel administrativo Filament
        Schema::create('users', function (Blueprint $table) {
            $table->id(); // Identificador único do usuário
            $table->string('name'); // Nome do usuário / colaborador
            $table->string('email')->unique(); // E-mail de acesso (único)
            $table->timestamp('email_verified_at')->nullable(); // Data de verificação do e-mail
            $table->string('password'); // Senha criptografada (hash)
            $table->rememberToken(); // Token para manter conectado ("lembrar de mim")
            $table->timestamps(); // Data de criação e atualização (created_at, updated_at)
        });

        // Tabela de tokens para recuperação/redefinição de senha
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary(); // E-mail do solicitante
            $table->string('token'); // Token único gerado para redefinição
            $table->timestamp('created_at')->nullable(); // Data/hora da solicitação
        });

        // Tabela para armazenamento de sessões no banco de dados
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary(); // ID da sessão
            $table->foreignId('user_id')->nullable()->index(); // ID do usuário logado (opcional)
            $table->string('ip_address', 45)->nullable(); // Endereço IP do cliente
            $table->text('user_agent')->nullable(); // Informações do navegador/dispositivo
            $table->longText('payload'); // Dados serializados da sessão
            $table->integer('last_activity')->index(); // Timestamp da última atividade
        });
    }

    /**
     * Reverte as migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
