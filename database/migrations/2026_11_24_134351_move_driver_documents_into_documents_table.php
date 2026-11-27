<?php

use App\Models\Driver;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('drivers', function (Blueprint $table) {
            $table->softDeletes();
        });

        if (! Schema::hasColumn('drivers', 'document_path')) {
            return;
        }

        DB::table('drivers')
            ->whereNotNull('document_path')
            ->orderBy('id')
            ->chunkById(100, function ($drivers): void {
                foreach ($drivers as $driver) {
                    DB::table('documents')->insert([
                        'documentable_type' => Driver::class,
                        'documentable_id' => $driver->id,
                        'original_name' => basename($driver->document_path),
                        'path' => $driver->document_path,
                        'mime_type' => null,
                        'size' => 0,
                        'is_current' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            });

        Schema::table('drivers', function (Blueprint $table) {
            $table->dropColumn('document_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('drivers', function (Blueprint $table) {
            $table->string('document_path')->nullable()->after('hst_number');
        });

        DB::table('documents')
            ->where('documentable_type', Driver::class)
            ->where('is_current', true)
            ->orderBy('id')
            ->chunkById(100, function ($documents): void {
                foreach ($documents as $document) {
                    DB::table('drivers')
                        ->where('id', $document->documentable_id)
                        ->update(['document_path' => $document->path]);
                }
            });

        Schema::table('drivers', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
