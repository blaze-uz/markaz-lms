<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('students', function (Blueprint $table) {
            // Check if indexes exist before creating them
            if (!$this->hasIndex('students', 'students_name_surname_phone_index')) {
                $table->index(['name', 'surname', 'phone']);
            }
            if (Schema::hasColumn('students', 'created_by_id') && !$this->hasIndex('students', 'students_created_by_id_index')) {
                $table->index('created_by_id');
            }
            if (!$this->hasIndex('students', 'students_deleted_at_index')) {
                $table->index('deleted_at');
            }
        });

        Schema::table('groups', function (Blueprint $table) {
            if (!$this->hasIndex('groups', 'groups_teacher_id_deleted_at_index')) {
                $table->index(['teacher_id', 'deleted_at']);
            }
            if (!$this->hasIndex('groups', 'groups_name_index')) {
                $table->index('name');
            }
        });

        Schema::table('student_group_payments', function (Blueprint $table) {
            if (!$this->hasIndex('student_group_payments', 'student_group_payments_student_id_left_amount_index')) {
                $table->index(['student_id', 'left_amount']);
            }
        });
    }

    public function down()
    {
        Schema::table('students', function (Blueprint $table) {
            if ($this->hasIndex('students', 'students_name_surname_phone_index')) {
                $table->dropIndex(['name', 'surname', 'phone']);
            }
            if (Schema::hasColumn('students', 'created_by_id') && $this->hasIndex('students', 'students_created_by_id_index')) {
                $table->dropIndex(['created_by_id']);
            }
            if ($this->hasIndex('students', 'students_deleted_at_index')) {
                $table->dropIndex(['deleted_at']);
            }
        });

        Schema::table('groups', function (Blueprint $table) {
            if ($this->hasIndex('groups', 'groups_teacher_id_deleted_at_index')) {
                $table->dropIndex(['teacher_id', 'deleted_at']);
            }
            if ($this->hasIndex('groups', 'groups_name_index')) {
                $table->dropIndex(['name']);
            }
        });

        Schema::table('student_group_payments', function (Blueprint $table) {
            if ($this->hasIndex('student_group_payments', 'student_group_payments_student_id_left_amount_index')) {
                $table->dropIndex(['student_id', 'left_amount']);
            }
        });
    }

    private function hasIndex($table, $index)
    {
        return DB::select("SELECT to_regclass('public.{$index}') as index")[0]->index !== null;
    }
};
