namespace LumenModule\[module_name]\Http\Model;

use Illuminate\Database\Eloquent\Model;

class [model_name] extends Model
{
    /**
     * Connection
     */
    protected $connection = "melis";
    /**
     * The table associated with the model.
     *
     * @var $table
     */
    protected $table = '[table_name]';
    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = '[primary_key]';

    public $timestamps = false;
}