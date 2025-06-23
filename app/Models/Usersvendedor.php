<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Usersvendedor extends Model
{
    use HasFactory;
    protected $table = "users_vendedores";
    protected $primaryKey = "id_user_vendedor";
    private $logs;

    public function __construct(){
        parent::__construct();
        $this->logs = new Logs();
    }
}
