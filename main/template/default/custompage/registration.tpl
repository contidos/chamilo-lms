<div class="custompage">
    <div class="limiter">
        <div class="container-login">
            <div class="wrap-login width-register">
                <div class="logo">
                    <img width="250px" class="img-responsive" title="{{ _s.site_name }}" src="{{ _p.web_css_theme }}images/header-logo.svg">
                </div>
                <h3 class="title">{{ 'UserRegistrationTitle'|get_lang() }}</h3>
                
                <div class="alert alert-danger" style="text-align: center;">
                    <p><strong><span style="color:#0000cc;"><span style="font-size:18px;">Muchas gracias por acceder a la página de registro de nuestro Campus Virtual. </span></span></strong></p>

                    <p style="text-align: left;"><span style="font-size:20px;"><span style="color:#e74c3c;"><strong><em>Si ya ha realizado alguna actividad previa, debe acceder a&nbsp;</em></strong></span><span style="color:#0000cc;"><strong><em>https://campus.solimat.com </em></strong></span><span style="color:#e74c3c;"><strong><em>con su usuario y contraseña para solicitar una nueva actividad. En caso contrario:</em></strong></span></span></p>

                    <p style="text-align: left;"><strong><span style="color:#0000cc;"><span style="font-size:18px;">Al realizar el proceso de inscripción debe elegir cuál quiere que sea su usuario y contraseña para la </span></span><u><em><span style="font-size:18px;"><a href="https://campus.solimat.com/main/auth/courses.php" target="_blank"><span style="color:#0000cc;"><span style="background-color:#dddddd;">actividad</span></span></a></span></em></u><span style="color:#0000cc;"><span style="font-size:18px;"><span style="background-color:#dddddd;"> </span>que desea desarrollar.<em> No olvide indicar sus dos apellidos para que posteriormente se pueda emitir el certificado.</em></span><span style="font-size:22px;"><em>&nbsp;</em></span></span></strong></p>

                    <p style="text-align: left;"><strong><span style="font-size:22px;"><span style="color:#e74c3c;">*&nbsp;<small>Contenido obligatorio</small></span></span></strong></p>

                    <p><strong><span style="color:#0000cc;"><span style="font-size:18px;">Una vez recibido su registro, realizaremos el proceso de validación de sus datos, tras el cuál recibirá un correo informando de que su usuario y contraseña han sido aprobados y podrá iniciar la actividad solicitada.</span></span></strong></p>
                </div>

                <p style="text-align: center;">&nbsp;</p>
                
                {{ form }}
                <div class="software-name">
                    <a href="{{_p.web}}" target="_blank">
                        {{ "PoweredByX" |get_lang | format(_s.software_name) }}
                    </a>&copy; {{ "now"|date("Y") }}
                </div>
            </div>
        </div>
    </div>
</div>
