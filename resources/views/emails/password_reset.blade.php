 <!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <style type="text/css">
        /* CLIENT-SPECIFIC STYLES */
        body, table, td, a { -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
        table, td { mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
        img { -ms-interpolation-mode: bicubic; }
        /* RESET STYLES */
        img { border: 0; height: auto; line-height: 100%; outline: none; text-decoration: none; }
        table { border-collapse: collapse !important; }
        body { height: 100% !important; margin: 0 !important; padding: 0 !important; width: 100% !important; }
        .blanquito { background-color: #ffffff !important; }
        /* iOS BLUE LINKS */
        a[x-apple-data-detectors] {
            color: inherit !important;
            text-decoration: none !important;
            font-size: inherit !important;
            font-family: inherit !important;
            font-weight: inherit !important;
            line-height: inherit !important;
        }
        /* MEDIA QUERIES */
        @media screen and (max-width: 480px) {
            .mobile-hide { display: none !important; }
            .mobile-center { text-align: center !important; }
        }
        /* ANDROID CENTER FIX */
        div[style*="margin: 16px 0;"] { margin: 0 !important; }
        .circleIcons{
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 2%;
            background: #f2f4f8;
            margin-right: 4%;
        }
        .circleIcons img{
            width: 70%;
        }
        .btn_link{
            box-sizing: border-box;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol';
            border-radius: 4px;
            color: #fff;
            display: inline-block;
            overflow: hidden;
            text-decoration: none;
            background-color: #2d3748;
            border-bottom: 8px solid #2d3748;
            border-left: 18px solid #2d3748;
            border-right: 18px solid #2d3748;
            border-top: 8px solid #2d3748;
        }
        .textEmail{
            box-sizing: border-box;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol';
            font-size: 16px;
            line-height: 1.5em;
            margin-top: 0;
            text-align: left;
            color:#142f8e;
            /*margin:16px 0px 16px 0px;color:#142f8e;font-size:16px;line-height:32px;font-weight: 500;*/
        }
        .titleSalu{
            box-sizing: border-box;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol';
            color: #3d4852;
            font-size: 18px;
            font-weight: bold;
            margin-top: 0;
            text-align: left;
        }
    </style>
</head>
<body style="margin: 0 !important; padding: 0 !important; background-color: #eeeeee;" bgcolor="#eeeeee">

<table border="0" cellpadding="0" cellspacing="0" width="100%">
    <tr>
        <td align="center" style="background-color: #edf2f7;height: 100vh;" bgcolor="#eeeeee">
            <table align="center" border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width:600px;height: 100vh">
                <tr>
                    <td align="center" style="background-color: white;border-radius: 10px;" bgcolor="#ffffff">
                        <table align="center" border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width:600px;">
                            <tr>
                                <td align="center" style="font-family: Open Sans, Helvetica, Arial, sans-serif; font-size: 16px; font-weight: 400; line-height: 24px;">
                                   <div style="display: flex;align-items: center;justify-content: end;padding: 15px 20px 15px 20px;background: #1648ff;border-top-right-radius: 10px;border-top-left-radius: 10px">
                                       <img src="{{asset('logoColors.png')}}" style="width: 210px;height: 45px;margin-left: auto" />
                                   </div>
                                    <div style="display: flex;align-items: center!important;justify-content: center;">
                                        <img src="{{asset('Forgot password-bro.png')}}" style="width: 55%;margin-top: 5%" alt="">
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td align="left" style="font-family: Open Sans, Helvetica, Arial, sans-serif; font-size: 16px; font-weight: 400; line-height: 24px;padding: 10px 20px 0px 20px">
                                    <p class="titleSalu">
                                        <strong>
                                            ¡Hola {{ $user->name }}!
                                        </strong>
                                        <br>
                                    </p>
                                    <p class="textEmail">
                                        Recibes este correo electrónico porque recibimos una solicitud de restablecimiento de contraseña para tu cuenta.
                                        <br>
                                    </p>

                                    <div style="display: flex;justify-content:center;align-items: center;margin-top: 35px;margin-bottom: 35px ">
                                        <a href="{{ $url }}" class="btn_link" style="margin: auto!important;color: white!important;">Restablecer contraseña</a>
                                    </div>
                                    <p class="textEmail">
                                        Este enlace de restablecimiento de contraseña caducará en 60 minutos.
                                        <br>
                                    </p>
                                    <p class="textEmail">
                                        Si no solicitaste un restablecimiento de contraseña, no es necesario realizar ninguna otra acción.
                                        <br>
                                    </p>
                                    <p class="textEmail">
                                        Saludos, <br>
                                        Venturis
                                    </p>

                                    <p style="border-top:1px solid #718096;padding-top: 20px;padding-bottom: 20px;color: #718096;box-sizing:border-box;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif,'Apple Color Emoji','Segoe UI Emoji','Segoe UI Symbol';line-height:1.5em;margin-top:30px;text-align:left;font-size:14px">
                                        Si tiene problemas para hacer clic en el botón "Restablecer contraseña", copie y pegue la siguiente URL en su navegador web:
                                        <span style="box-sizing:border-box;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif,'Apple Color Emoji','Segoe UI Emoji','Segoe UI Symbol';word-break:break-all">
                                            <a href="{{ $url }}" style="box-sizing:border-box;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif,'Apple Color Emoji','Segoe UI Emoji','Segoe UI Symbol';color:#3869d4" target="_blank" data-saferedirecturl="https://www.google.com/url?q=http://ventageneral.local/reset-password/36b3b0b7f347ff1bbf00d8b556fc8b6c5c63dff799487e17dfa48d78b2b4d019?email%3Dreynaalfredo421%2540gmail.com&amp;source=gmail&amp;ust=1726578331211000&amp;usg=AOvVaw297SR1N2niGwTa_k_dLt8t">
                                                {{ $url }}
                                            </a>
                                        </span>
                                    </p>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <!-- Cuerpo del mensaje -->

                <!-- Publicidad a Bufeo -->
                <tr>
                    <td align="center" style="padding: 35px;background: linear-gradient(to bottom, #1648ff, #2d3748);" bgcolor="#139a43">
                        <table align="center" border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width:600px;">
{{--                            <tr>--}}
{{--                                <td align="center">--}}
{{--                                    <img src="https://bufeotec.com/archivos_clientes/logo_bufeo_nuevo.png" width="100" style="display: block; border: 0px;"/>--}}
{{--                                </td>--}}
{{--                            </tr>--}}
                            <tr>
                                <td align="center" style="font-family: Open Sans, Helvetica, Arial, sans-serif; font-size: 14px; font-weight: 400; line-height: 24px; padding: 5px 0 10px 0;">
                                    <p style="font-size: 14px; font-weight: 800; line-height: 18px; color: white;">
                                        Desarrollado por Eder Alfredo
                                    </p>
                                </td>
                            </tr>
                            <tr>
                                <td align="center" style="font-family: Open Sans, Helvetica, Arial, sans-serif; font-size: 14px; font-weight: 400; line-height: 24px;">
                                    <p style="font-size: 14px; font-weight: 400; line-height: 20px; color: white;">
                                        <a href="http://apaguesoft.com/" target="_blank" style="color: white;">¿Interesado? Visita nuestro sitio web</a>.
                                    </p>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <!-- Publicidad a Bufeo -->
            </table>
        </td>
    </tr>
</table>
</body>
</html>
