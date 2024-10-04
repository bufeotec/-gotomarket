<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use Carbon\Carbon;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Fortify;
use Illuminate\Notifications\Messages\MailMessage;
class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);

        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())).'|'.$request->ip());

            return Limit::perMinute(5)->by($throttleKey);
        });

        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });

        Fortify::loginView(function () {
            return view('auth.login');
        });
        fortify::requestPasswordResetLinkView(function(){
            return view('auth.forgot_password');
        });
        Fortify::resetPasswordView(function($request){
            return view('auth.reset_password',['request' => $request]);
        });
        Fortify::registerView(function () {
            return view('auth.register');
        });

//        ResetPassword::toMailUsing(function($user, string $token) {
//            return (new MailMessage)
//                ->subject('Restablecimiento de contraseña')
//                ->view('emails.password_reset', [
//                    'user' => $user,
//                    'url' => sprintf('%s/users/password_reset/%s', config('app.url'), $token)
//                ]);
//        });

        ResetPassword::toMailUsing(function($user, string $token) {
            $resetUrl = route('password.reset', ['token' => $token, 'email' => $user->email]);

            return (new MailMessage)
                ->subject('Restablecimiento de contraseña')
                ->view('emails.password_reset', [
                    'user' => $user,
                    'url' => $resetUrl
                ]);
        });

    }
}
