<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('officials', function (Blueprint $table) {
            $table->foreignId('resident_id')->nullable()->after('id')->constrained('residents')->nullOnDelete();
            $table->string('title_prefix', 50)->nullable()->after('name');
            $table->string('title_suffix', 50)->nullable()->after('title_prefix');
            $table->char('nik', 16)->nullable()->after('title_suffix')->index();
            $table->string('village_employee_number', 25)->nullable()->after('nik')->index();
            $table->string('nip', 30)->nullable()->after('village_employee_number')->index();
            $table->string('id_card_tag', 50)->nullable()->after('nip')->unique();
            $table->string('birth_place', 100)->nullable()->after('id_card_tag');
            $table->date('birth_date')->nullable()->after('birth_place');
            $table->char('sex', 1)->nullable()->after('birth_date')->index();
            $table->string('education', 100)->nullable()->after('sex');
            $table->string('religion', 30)->nullable()->after('education');
            $table->string('rank_grade', 50)->nullable()->after('religion');
            $table->string('appointment_decree', 100)->nullable()->after('position');
            $table->date('appointment_date')->nullable()->after('appointment_decree');
            $table->string('dismissal_decree', 100)->nullable()->after('appointment_date');
            $table->date('dismissal_date')->nullable()->after('dismissal_decree');
            $table->string('term', 150)->nullable()->after('dismissal_date');
            $table->boolean('is_acting')->default(false)->after('term');
            $table->foreignId('superior_id')->nullable()->after('is_acting')->constrained('officials')->nullOnDelete();
            $table->unsignedSmallInteger('organization_level')->nullable()->after('superior_id');
            $table->smallInteger('organization_offset')->default(0)->after('organization_level');
            $table->string('organization_layout', 30)->nullable()->after('organization_offset');
            $table->string('organization_color', 7)->nullable()->after('organization_layout');
            $table->boolean('can_sign_on_behalf')->default(false)->after('is_active');
            $table->boolean('can_sign_for')->default(false)->after('can_sign_on_behalf');
            $table->string('phone', 25)->nullable()->after('can_sign_for');
            $table->string('email', 150)->nullable()->after('phone');
            $table->json('social_media')->nullable()->after('email');
            $table->date('registered_at')->nullable()->after('social_media');
        });
    }

    public function down(): void
    {
        Schema::table('officials', function (Blueprint $table) {
            $table->dropForeign(['resident_id']);
            $table->dropForeign(['superior_id']);
            $table->dropUnique(['id_card_tag']);
            $table->dropIndex(['nik']);
            $table->dropIndex(['village_employee_number']);
            $table->dropIndex(['nip']);
            $table->dropIndex(['sex']);
            $table->dropColumn([
                'resident_id',
                'title_prefix',
                'title_suffix',
                'nik',
                'village_employee_number',
                'nip',
                'id_card_tag',
                'birth_place',
                'birth_date',
                'sex',
                'education',
                'religion',
                'rank_grade',
                'appointment_decree',
                'appointment_date',
                'dismissal_decree',
                'dismissal_date',
                'term',
                'is_acting',
                'superior_id',
                'organization_level',
                'organization_offset',
                'organization_layout',
                'organization_color',
                'can_sign_on_behalf',
                'can_sign_for',
                'phone',
                'email',
                'social_media',
                'registered_at',
            ]);
        });
    }
};
