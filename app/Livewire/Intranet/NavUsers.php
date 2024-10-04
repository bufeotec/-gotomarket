<?php

namespace App\Livewire\Intranet;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Component;

class NavUsers extends Component
{
    #[On('refresh_profile')]
    public function render()
    {
        if (auth()->check()){
            $userAuth =  DB::table('users')->where([['id_users','=',Auth::id()],['users_status','=',1]])->first();
            return view('livewire.intranet.nav-users',compact('userAuth'));
        }
    }
}
