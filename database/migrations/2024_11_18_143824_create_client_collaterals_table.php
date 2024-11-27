use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClientCollateralsTable extends Migration
{
    public function up()
    {
        Schema::create('client_collaterals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->string('title', 255);
            $table->text('description')->nullable();
            $table->string('file_path')->nullable(); // For storing file paths
            $table->string('file_type')->nullable(); // e.g., 'image', 'document'
            $table->string('mime_type')->nullable(); // e.g., 'image/jpeg'
            $table->string('original_filename')->nullable(); // Original file name
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('client_collaterals');
    }
}
