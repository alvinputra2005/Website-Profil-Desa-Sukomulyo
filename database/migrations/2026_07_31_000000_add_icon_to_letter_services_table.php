<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('letter_services', function (Blueprint $table): void {
            $table->string('icon', 50)->default('fa-file')->after('description');
        });

        $icons = [
            'SKU' => 'fa-briefcase',
            'SKD' => 'fa-home',
            'SKTM' => 'fa-heart',
            'SKCK' => 'fa-file',
            'SKBM' => 'fa-user',
            'SKL' => 'fa-birthday-cake',
            'SKM' => 'fa-certificate',
        ];

        foreach ($icons as $code => $icon) {
            DB::table('letter_services')->where('code', $code)->update(['icon' => $icon]);
        }
    }

    public function down(): void
    {
        Schema::table('letter_services', function (Blueprint $table): void {
            $table->dropColumn('icon');
        });
    }
};
