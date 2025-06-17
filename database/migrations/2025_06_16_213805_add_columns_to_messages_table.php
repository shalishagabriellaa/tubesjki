<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToMessagesTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('messages')) {
            Schema::table('messages', function (Blueprint $table) {
                $table->boolean('is_read')->default(false)->after('content');
                $table->timestamp('read_at')->nullable()->after('is_read');
                $table->timestamp('sent_at')->nullable()->after('read_at');
                $table->boolean('deleted_by_sender')->default(false)->after('sent_at');
                $table->boolean('deleted_by_receiver')->default(false)->after('deleted_by_sender');
                $table->string('message_type', 50)->default('text')->after('deleted_by_receiver');
                $table->json('metadata')->nullable()->after('message_type');

                // Named indexes for clarity
                $table->index(['sender_id', 'receiver_id', 'created_at'], 'messages_sender_receiver_created_idx');
                $table->index(['receiver_id', 'is_read'], 'messages_receiver_read_idx');
                $table->index('created_at', 'messages_created_at_idx');
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('messages')) {
            Schema::table('messages', function (Blueprint $table) {
                $table->dropIndex('messages_sender_receiver_created_idx');
                $table->dropIndex('messages_receiver_read_idx');
                $table->dropIndex('messages_created_at_idx');

                $table->dropColumn([
                    'is_read',
                    'read_at',
                    'sent_at',
                    'deleted_by_sender',
                    'deleted_by_receiver',
                    'message_type',
                    'metadata',
                ]);
            });
        }
    }
}
