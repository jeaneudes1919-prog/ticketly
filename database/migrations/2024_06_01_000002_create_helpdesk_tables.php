<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('departments', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('description')->nullable();
            $t->timestamps();
        });

        Schema::create('department_user', function (Blueprint $t) {
            $t->foreignId('department_id')->constrained()->cascadeOnDelete();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->primary(['department_id', 'user_id']);
        });

        Schema::create('categories', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('color')->default('#3B82F6');
            $t->timestamps();
        });

        Schema::create('tags', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('color')->default('#8B5CF6');
            $t->timestamps();
        });

        Schema::create('tickets', function (Blueprint $t) {
            $t->id();
            $t->string('reference')->nullable()->unique();
            $t->string('subject');
            $t->text('description');
            $t->string('status')->default('open');
            $t->string('priority')->default('medium');
            $t->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('customer_id')->constrained('users')->cascadeOnDelete();
            $t->foreignId('assignee_id')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamp('due_at')->nullable();
            $t->timestamp('first_response_at')->nullable();
            $t->timestamp('resolved_at')->nullable();
            $t->timestamp('closed_at')->nullable();
            $t->timestamp('sla_warned_at')->nullable();
            $t->unsignedTinyInteger('satisfaction')->nullable();
            $t->text('satisfaction_comment')->nullable();
            $t->boolean('sla_breached')->default(false);
            $t->timestamps();
            $t->index(['status', 'priority', 'assignee_id']);
            $t->index(['customer_id', 'created_at']);
        });

        Schema::create('tag_ticket', function (Blueprint $t) {
            $t->foreignId('tag_id')->constrained()->cascadeOnDelete();
            $t->foreignId('ticket_id')->constrained()->cascadeOnDelete();
            $t->primary(['tag_id', 'ticket_id']);
        });

        Schema::create('ticket_replies', function (Blueprint $t) {
            $t->id();
            $t->foreignId('ticket_id')->constrained()->cascadeOnDelete();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->text('body');
            $t->boolean('is_internal')->default(false);
            $t->timestamps();
        });

        Schema::create('attachments', function (Blueprint $t) {
            $t->id();
            $t->foreignId('ticket_id')->constrained()->cascadeOnDelete();
            $t->foreignId('ticket_reply_id')->nullable()->constrained()->cascadeOnDelete();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->string('path');
            $t->string('original_name');
            $t->string('mime');
            $t->unsignedInteger('size');
            $t->timestamps();
        });

        Schema::create('sla_policies', function (Blueprint $t) {
            $t->id();
            $t->string('priority')->unique();
            $t->unsignedInteger('first_response_minutes');
            $t->unsignedInteger('resolution_minutes');
            $t->timestamps();
        });

        Schema::create('canned_responses', function (Blueprint $t) {
            $t->id();
            $t->string('title');
            $t->text('body');
            $t->string('shortcut')->nullable()->unique();
            $t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $t->timestamps();
        });

        Schema::create('activity_logs', function (Blueprint $t) {
            $t->id();
            $t->foreignId('ticket_id')->constrained()->cascadeOnDelete();
            $t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $t->string('action');
            $t->json('meta')->nullable();
            $t->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        foreach (['activity_logs', 'canned_responses', 'sla_policies', 'attachments', 'ticket_replies', 'tag_ticket', 'tickets', 'tags', 'categories', 'department_user', 'departments'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
