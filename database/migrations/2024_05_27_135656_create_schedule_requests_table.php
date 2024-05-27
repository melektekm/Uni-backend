use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateScheduleRequestsTable extends Migration
{
public function up()
{
Schema::create('schedule_requests', function (Blueprint $table) {
$table->id();
$table->string('course_name');
$table->string('course_code');
$table->string('classroom')->nullable();
$table->string('labroom')->nullable();
$table->string('class_days')->nullable();
$table->string('lab_days')->nullable();
$table->string('lab_instructor')->nullable();
$table->string('class_instructor')->nullable();
$table->string('schedule_type');
$table->unsignedBigInteger('requested_by');
$table->timestamps();

$table->foreign('requested_by')->references('id')->on('users')->onDelete('cascade');
});
}

public function down()
{
Schema::dropIfExists('schedule_requests');
}
}