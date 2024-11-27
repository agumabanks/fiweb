<?php

// database/migrations/xxxx_xx_xx_create_balance_sheets_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBalanceSheetsTable extends Migration
{
    public function up()
    {
        Schema::create('balance_sheets', function (Blueprint $table) {
            $table->id();
            $table->date('date')->unique();
            $table->decimal('opening_balance', 15, 2)->default(0);
            $table->decimal('closing_balance', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('balance_sheets');
    }
}
