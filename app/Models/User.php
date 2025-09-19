<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    private $logs;
    protected $table = "users";
    protected $primaryKey = "id_users";
    public function __construct()
    {
        parent::__construct();
        $this->logs = new Logs();
    }

    use HasFactory, Notifiable;
    use HasRoles;
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function listar_usuarios_activos($search = '', $pagination = 10, $order = 'asc'){
        try {
            $query = DB::table('users')
                ->where(function($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%')
                        ->orWhere('last_name', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%')
                        ->orWhere('username', 'like', '%' . $search . '%');
                })
                ->orderBy('id_users', $order);

            $result = $query->paginate($pagination);
        } catch (\Exception $e) {
            $this->logs->insertarLog($e);
            $result = [];
            // Si falla la consulta, puedes devolver un paginador vacío:
//            $result = collect([])->paginate($pagination);
        }

        return $result;
    }
    public function validar_correo_usuarios($correo){
        try {

            $result = DB::table('users')->where('email','=',$correo)->first();

        } catch (\Exception $e) {
            $this->logs->insertarLog($e);
            $result = [];
            // Si falla la consulta, puedes devolver un paginador vacío:
//            $result = collect([])->paginate($pagination);
        }

        return $result;
    }
    public function validar_username_usuarios($username){
        try {

            $result = DB::table('users')->where('username','=',$username)->first();

        } catch (\Exception $e) {
            $this->logs->insertarLog($e);
            $result = [];
            // Si falla la consulta, puedes devolver un paginador vacío:
//            $result = collect([])->paginate($pagination);
        }

        return $result;
    }
    public function informacion_x_id($id){
        try {

            $result = DB::table('users')->where('id_users','=',$id)->first();

        } catch (\Exception $e) {
            $this->logs->insertarLog($e);
            $result = [];
        }

        return $result;
    }
    public function listra_usuarios_activos(){
        try {
            $result = DB::table('users')
                ->where('users_status','=',1)
                ->get();
        } catch (\Exception $e) {
            $this->logs->insertarLog($e);
            $result = [];
        }
        return $result;
    }



    // APIS
    public function obtener_usuario_api($email){
        try {
            return DB::table('users')
                ->select('id_users', 'name', 'last_name', 'email', 'username', 'profile_picture', 'users_phone', 'users_status')
                ->where('email', $email)
                ->first();
        } catch (\Exception $e) {
            return null;
        }
    }

}
