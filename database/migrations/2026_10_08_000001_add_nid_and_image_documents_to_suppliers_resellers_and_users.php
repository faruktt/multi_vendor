<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Suppliers table
        Schema::table('suppliers', function (Blueprint $table) {
            if (!Schema::hasColumn('suppliers', 'image')) {
                $table->string('image')->nullable()->after('address');
            }
            if (!Schema::hasColumn('suppliers', 'nid_front')) {
                $table->string('nid_front')->nullable()->after('image');
            }
            if (!Schema::hasColumn('suppliers', 'nid_back')) {
                $table->string('nid_back')->nullable()->after('nid_front');
            }
            if (!Schema::hasColumn('suppliers', 'guardian_nid_front')) {
                $table->string('guardian_nid_front')->nullable()->after('nid_back');
            }
            if (!Schema::hasColumn('suppliers', 'guardian_nid_back')) {
                $table->string('guardian_nid_back')->nullable()->after('guardian_nid_front');
            }
        });

        // 2. Resellers table
        Schema::table('resellers', function (Blueprint $table) {
            if (!Schema::hasColumn('resellers', 'image')) {
                $table->string('image')->nullable()->after('address');
            }
            if (!Schema::hasColumn('resellers', 'nid_front')) {
                $table->string('nid_front')->nullable()->after('image');
            }
            if (!Schema::hasColumn('resellers', 'nid_back')) {
                $table->string('nid_back')->nullable()->after('nid_front');
            }
            if (!Schema::hasColumn('resellers', 'guardian_nid_front')) {
                $table->string('guardian_nid_front')->nullable()->after('nid_back');
            }
            if (!Schema::hasColumn('resellers', 'guardian_nid_back')) {
                $table->string('guardian_nid_back')->nullable()->after('guardian_nid_front');
            }
        });

        // 3. Users (Branch staff / managers) table
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'phone')) {
                $table->string('phone')->nullable()->after('email');
            }
            if (!Schema::hasColumn('users', 'nid_front')) {
                $table->string('nid_front')->nullable()->after('image');
            }
            if (!Schema::hasColumn('users', 'nid_back')) {
                $table->string('nid_back')->nullable()->after('nid_front');
            }
            if (!Schema::hasColumn('users', 'guardian_nid_front')) {
                $table->string('guardian_nid_front')->nullable()->after('nid_back');
            }
            if (!Schema::hasColumn('users', 'guardian_nid_back')) {
                $table->string('guardian_nid_back')->nullable()->after('guardian_nid_front');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $cols = ['image', 'nid_front', 'nid_back', 'guardian_nid_front', 'guardian_nid_back'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('suppliers', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        Schema::table('resellers', function (Blueprint $table) {
            $cols = ['nid_front', 'nid_back', 'guardian_nid_front', 'guardian_nid_back'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('resellers', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        Schema::table('users', function (Blueprint $table) {
            $cols = ['phone', 'nid_front', 'nid_back', 'guardian_nid_front', 'guardian_nid_back'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('users', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
