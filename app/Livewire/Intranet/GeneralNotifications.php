<?php

namespace App\Livewire\Intranet;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Attributes\On;
class GeneralNotifications extends Component
{
    public $info_notifications;
    #[On('refreshComponenteNotificaciones')]
    public function render()
    {
        $notifications = Auth::user()->unreadNotifications;
        return view('livewire.intranet.general-notifications',compact('notifications'));
    }
    #[On('echo:dashboard,Notify')]
    public function dump()
    {
        $this->render();
    }
    public function markAsRead($notificationId)
    {
        $user = Auth::user();
        $notification = $user->unreadNotifications->where('id', $notificationId)->first();
        if ($notification) {
            $notification->markAsRead();
            $this->dispatch('readNoti');
        }
    }
    public function notification_information($notificationId)
    {
        $user = Auth::user();
        $notification = $user->unreadNotifications->where('id', $notificationId)->first();
        if ($notification) {
            $notification->markAsRead();
            $this->dispatch('readNoti');
            $this->info_notifications = $notification;
        }
    }

}
