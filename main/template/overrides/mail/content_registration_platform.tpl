{% if 'mutua.apacampus.com' in mailWebPath %}
<p>{{ complete_name }},</p>
<p>Mutualiko Campusean erregistratuta zaude, honako parametro hauekin:</p>
<p>Erabiltzailea: {{ login_name }}<br>
    Pasahitza: {{ original_password }}</p>
<p>Eskerrik asko gure campus birtualean izena emateagatik</p>
<p style="color: #ff0000;">Sartu ezin baduzu, mutualia onartu arte itxaron beharko duzu</p>
<p>Mutualiko Campusean: {{ mailWebPath }}</p>
<p>Arazorik baduzu, zalantzarik gabe jarri gurekin harremanetan</p>
<p>{{ 'SignatureFormula'|get_lang }}</p>
<p>{{ _admin.name }}, {{ _admin.surname }}<br>
    Kudeatzailea Mutualiko Campus Birtuala<br>
    {{ _admin.telephone ? 'T. ' ~ _admin.telephone }}<br>
    {{ _admin.email ? 'Email'|get_lang ~ ': ' ~ _admin.email }}</p>
{% else %}
<p>{{ 'Dear'|get_lang }} {{ complete_name }},</p>
<p>{{ 'YouAreReg'|get_lang }} {{ _s.site_name }} {{ 'WithTheFollowingSettings'|get_lang }}</p>
<p>{{ 'Username'|get_lang }} : {{ login_name }}<br>
    {{ 'Pass'|get_lang }} : {{ original_password }}</p>
<p>{{ 'ThanksForRegisteringToSite'|get_lang|format(_s.site_name) }}</p>
<p>{{ 'Address'|get_lang }} {{ _s.site_name }} {{ 'Is'|get_lang }} : {{ mailWebPath }}</p>
<p>{{ 'Problem'|get_lang }}</p>
<p>{{ 'SignatureFormula'|get_lang }}</p>
<p>{{ _admin.name }}, {{ _admin.surname }}<br>
    {{ 'Manager'|get_lang }} {{ _s.site_name }}<br>
    {{ _admin.telephone ? 'T. ' ~ _admin.telephone }}<br>
    {{ _admin.email ? 'Email'|get_lang ~ ': ' ~ _admin.email }}</p>
{% endif %}
